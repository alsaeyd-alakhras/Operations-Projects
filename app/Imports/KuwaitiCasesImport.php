<?php

namespace App\Imports;

use App\Models\KuwaitiCase;
use App\Models\Record;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * استيراد لمرة واحدة لردود «استمارة البحث الاجتماعي – مركز الكويتي» المصدَّرة من Microsoft Forms.
 * الصف الأول = عناوين الأعمدة (نص الأسئلة). تُطابَق الأعمدة بكلمات مفتاحية مطبَّعة، لا بالترتيب.
 * إجمالي النقاط يُحسب في الموديل عند الحفظ (السؤالان 15 و16 يُتجاهلان).
 */
class KuwaitiCasesImport implements ToCollection
{
    public int $imported = 0;

    public int $skipped = 0;

    /**
     * field => كلمات مفتاحية (مطبَّعة تلقائيًا). الترتيب مهم: الأكثر تحديدًا أولًا،
     * وكل عمود يُستهلك مرة واحدة فقط (مثلًا «عنوان السكن» قبل «العنوان الحالي»، و«تفاصيل الفحص» قبل «الفحص المطلوب»).
     */
    private const FIELD_KEYWORDS = [
        'displacement_address' => ['عنوان السكن', 'قبل النزوح'],
        'address' => ['العنوان الحالي'],
        'housing_status' => ['حالة النزوح'],
        'exam_need' => ['تفاصيل الفحص'],
        'exam_cost' => ['تكلفة الفحص'],
        'exam' => ['الفحص المطلوب'],
        'income_status' => ['مصادر الدخل'],
        'family_burden' => ['حجم الأسرة'],
        'name' => ['اسم المريض'],
        'gender' => ['الجنس'],
        'patient_ID' => ['رقم الهوية'],
        'birth_date' => ['تاريخ الميلاد'],
        'marital_status' => ['الحالة الاجتماعية'],
        'phone_number' => ['رقم للتواصل', 'رقم التواصل'],
        'data_entry' => ['مدخل البيانات'],
        'eligible' => ['رأي مدير المكتب'],
        'need_percentage' => ['نسبة الاحتياج'],
        // «رأي الباحث الاجتماعي» (سؤال Forms) و«ملاحظات الفرع» يدخلان معًا في حقل ملاحظات الفرع branch_notes
        '_researcher_note' => ['رأي الباحث'],
        'branch_notes' => ['ملاحظات الفرع'],
        'case_description' => ['وصف الحالة'],
        'management_status' => ['موافقة الإدارة', 'موافقة الادارة'],
        'management_notes' => ['ملاحظات الإدارة', 'ملاحظات الادارة'],
        // كلمة تبدأ بـ «=» تعني تطابق العنوان كاملًا (كي لا تلتقط «تمت» جزءًا من نص سؤال طويل)
        'done' => ['=تمت', '=تم الفحص', '=تمت العملية', '=تم التصوير'],
        '_date' => ['completion time', 'وقت الاكتمال'],
    ];

    /** أعمدة لا تُطابَق أبدًا (جدول النقاط السؤال 15 وإجمالي النقاط السؤال 16). */
    private const IGNORED_HEADERS = ['جدول احتساب', 'اجمالي النقاط'];

    public function __construct(private ?int $branchId = null, private ?int $userId = null)
    {
        $this->userId ??= Auth::id();
    }

    public function collection(Collection $rows): void
    {
        $rows = $rows->map(fn ($row) => $row instanceof Collection ? $row->toArray() : (array) $row)->values();
        if ($rows->isEmpty()) {
            return;
        }

        $map = self::mapColumns(array_values($rows->shift()));
        if (! isset($map['name'])) {
            throw new \RuntimeException('لم يتم العثور على عمود «اسم المريض» في الملف.');
        }

        DB::transaction(function () use ($rows, $map) {
            foreach ($rows as $row) {
                $row = array_values($row);
                $attributes = $this->attributes($row, $map);
                if ($attributes === null) {
                    $this->skipped++;
                    continue;
                }
                KuwaitiCase::create($attributes);
                $this->imported++;
            }
        });
    }

    /** يحوّل صفًا إلى خصائص الموديل، أو null إذا كان الاسم فارغًا. */
    public function attributes(array $row, array $map): ?array
    {
        $text = fn (string $field) => isset($map[$field]) ? self::text($row[$map[$field]] ?? null) : '';
        $cell = fn (string $field) => isset($map[$field]) ? ($row[$map[$field]] ?? null) : null;

        $name = $text('name');
        if ($name === '') {
            return null;
        }

        $attributes = [
            'date' => (isset($map['_date']) ? self::parseDate($cell('_date')) : null) ?? Carbon::today(),
            'name' => $name,
            'gender' => self::choice($text('gender'), KuwaitiCase::GENDERS) ?? '',
            'patient_ID' => self::digits($cell('patient_ID')),
            'marital_status' => self::choice($text('marital_status'), KuwaitiCase::MARITAL_STATUSES) ?? '',
            'address' => $text('address'),
            'displacement_address' => $text('displacement_address'),
            'phone_number' => self::phone($cell('phone_number')),
            'exam' => $text('exam'),
            'exam_cost' => self::parseNumber($cell('exam_cost')) ?? 0,
            'exam_need' => self::choice($text('exam_need'), KuwaitiCase::EXAM_NEEDS) ?? '',
            'housing_status' => self::choice($text('housing_status'), KuwaitiCase::HOUSING_STATUSES) ?? '',
            'income_status' => self::choice($text('income_status'), KuwaitiCase::INCOME_STATUSES) ?? '',
            'family_burden' => self::choice($text('family_burden'), KuwaitiCase::FAMILY_BURDENS) ?? '',
            'data_entry' => $text('data_entry') !== '' ? $text('data_entry') : (Auth::user()?->name ?? ''),
            'case_description' => $text('case_description'),
            'branch_id' => $this->branchId,
            'user_id' => $this->userId,
        ];

        // حقول اختيارية: تُهمل إن لم تكن في الملف كي لا تُكتب قيم فارغة فوق الافتراضي.
        if (($birth = self::parseDate($cell('birth_date'))) !== null) {
            $attributes['birth_date'] = $birth;
        }
        if (($eligible = self::parseEligible($text('eligible'))) !== null) {
            $attributes['eligible'] = $eligible;
        }
        if (($percentage = self::parsePercentage($cell('need_percentage'))) !== null) {
            $attributes['need_percentage'] = $percentage;
        }
        // رأي الباحث الاجتماعي يُدمج مع ملاحظات الفرع: كل واحد تحت الآخر (ملاحظات الفرع أولًا)
        $branchNotes = array_values(array_filter([$text('branch_notes'), $text('_researcher_note')], fn ($note) => $note !== ''));
        if ($branchNotes) {
            $attributes['branch_notes'] = implode("\n", $branchNotes);
        }
        if (($done = self::parseBool($cell('done'))) !== null) {
            $attributes['done'] = $done;
        }
        if (($status = self::choice($text('management_status'), Record::MANAGEMENT_STATUSES)) !== null) {
            $attributes['management_status'] = $status;
        }
        if (($notes = $text('management_notes')) !== '') {
            $attributes['management_notes'] = $notes;
        }

        return $attributes;
    }

    // ===== مطابقة الأعمدة =====

    /** @return array<string,int> field => index العمود */
    public static function mapColumns(array $headers): array
    {
        $normalized = array_map(fn ($h) => self::normalize((string) $h), $headers);
        $used = [];
        $map = [];

        foreach (self::FIELD_KEYWORDS as $field => $keywords) {
            foreach ($normalized as $index => $header) {
                if ($header === '' || isset($used[$index]) || self::isIgnored($header)) {
                    continue;
                }
                foreach ($keywords as $keyword) {
                    $exact = str_starts_with($keyword, '=');
                    $needle = self::normalize($exact ? substr($keyword, 1) : $keyword);
                    if ($exact ? $header === $needle : str_contains($header, $needle)) {
                        $map[$field] = $index;
                        $used[$index] = true;
                        continue 3;
                    }
                }
            }
        }

        return $map;
    }

    private static function isIgnored(string $header): bool
    {
        foreach (self::IGNORED_HEADERS as $ignored) {
            if (str_contains($header, self::normalize($ignored))) {
                return true;
            }
        }

        return false;
    }

    /** تشكيل/تطويل/أرقام/علامات ترقيم مُزالة، توحيد الهمزات والياء والتاء المربوطة، وأحرف لاتينية صغيرة. */
    public static function normalize(string $value): string
    {
        $value = preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{0640}]/u', '', $value);
        $value = preg_replace('/[0-9\x{0660}-\x{0669}\x{06F0}-\x{06F9}]/u', '', $value);
        $value = strtr($value, ['أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ى' => 'ي', 'ة' => 'ه']);
        $value = preg_replace('/[^\p{L}\s]/u', ' ', $value);
        $value = preg_replace('/\s+/u', ' ', $value);

        return trim(mb_strtolower($value));
    }

    // ===== تحويل القيم =====

    public static function text(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d');
        }
        if (is_float($value) && floor($value) === $value) {
            return (string) (int) $value;
        }

        return trim((string) $value);
    }

    /**
     * يحوّل نص الخيار (بدون/مع «(30 نقطة)») إلى مفتاحه. $options: key => label أو key => ['label' => ...].
     * المطابقة: مفتاح مباشر، ثم تساوٍ، ثم بداية نص (باتجاهين) بشرط أن تكون النتيجة وحيدة.
     */
    public static function choice(string $value, array $options): int|string|null
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        foreach ($options as $key => $_) {
            if ((string) $key === $value) {
                return $key;
            }
        }

        $needle = self::normalize(preg_replace('/\(\s*[0-9\x{0660}-\x{0669}]+\s*نقا?ط[ةه]?\s*\)/u', '', $value));
        if ($needle === '') {
            return null;
        }
        $labels = [];
        foreach ($options as $key => $option) {
            $labels[$key] = self::normalize(is_array($option) ? (string) ($option['label'] ?? '') : (string) $option);
        }

        foreach ($labels as $key => $label) {
            if ($label !== '' && $label === $needle) {
                return $key;
            }
        }
        $matches = array_keys(array_filter($labels, fn ($label) => $label !== '' && (str_starts_with($label, $needle) || str_starts_with($needle, $label))));

        return count($matches) === 1 ? $matches[0] : null;
    }

    /**
     * رأي مدير المكتب: محتاج / يستحق → true، غير محتاج / لا يستحق → false (يفحص «غير» و«لا» أولًا)،
     * وغير محدد / لم يحدد / فارغ → null.
     */
    public static function parseEligible(string $value): ?bool
    {
        $normalized = self::normalize($value);
        if ($normalized === '' || str_contains($normalized, 'محدد') || str_contains($normalized, 'يحدد')) {
            return null;
        }
        if (str_starts_with($normalized, 'غير') || str_starts_with($normalized, 'لا ') || $normalized === 'لا') {
            return false;
        }
        if (str_contains($normalized, 'محتاج') || str_contains($normalized, 'يستحق') || $normalized === 'نعم') {
            return true;
        }

        return null;
    }

    /** نعم / yes / 1 / true / صح → true؛ لا / no / 0 / false → false؛ وإلا null (يقبل منطقي Excel أيضًا). */
    public static function parseBool(mixed $value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if ($value === null || $value === '') {
            return null;
        }
        if (is_int($value) || is_float($value)) {
            return $value == 1 ? true : ($value == 0 ? false : null);
        }
        $normalized = self::normalize((string) $value);
        $raw = strtolower(trim((string) $value));
        if (in_array($raw, ['1', 'true', 'yes', 'y'], true) || in_array($normalized, ['نعم', 'تمت', 'تم', 'صح'], true)) {
            return true;
        }
        if (in_array($raw, ['0', 'false', 'no', 'n'], true) || in_array($normalized, ['لا', 'لم تتم', 'لم يتم', 'خطا'], true)) {
            return false;
        }

        return null;
    }

    /** "50%" / "0.5" / 0.33 / 50 → 50 / 50 / 33 / 50؛ وإلا null (يقبل 50 و33 و25 فقط). */
    public static function parsePercentage(mixed $value): ?int
    {
        $number = self::parseNumber(is_string($value) ? str_replace('%', '', $value) : $value);
        if ($number === null) {
            return null;
        }
        if ($number > 0 && $number <= 1) {
            $number *= 100;
        }
        $number = (int) round($number);

        return in_array($number, array_keys(KuwaitiCase::NEED_PERCENTAGES), true) ? $number : null;
    }

    public static function parseNumber(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }
        $value = strtr((string) $value, ['٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9', '٫' => '.', '،' => '', ',' => '']);
        $value = preg_replace('/[^0-9.\-]/', '', $value);

        return is_numeric($value) ? (float) $value : null;
    }

    /** رقم هوية: أرقام فقط كنص (Excel قد يعطيه رقمًا عشريًا). */
    public static function digits(mixed $value): string
    {
        $text = self::text($value);
        $text = strtr($text, ['٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9']);

        return preg_replace('/\D/', '', $text) ?? '';
    }

    /** هاتف: أرقام فقط، ويُعاد الصفر الذي يحذفه Excel (9 أرقام تبدأ بـ5). */
    public static function phone(mixed $value): string
    {
        $phone = self::digits($value);

        return strlen($phone) === 9 && $phone[0] === '5' ? '0'.$phone : $phone;
    }

    /** رقم تاريخ Excel أو DateTime أو نص M/d/yyyy (وسنة بخانتين) أو Y-m-d؛ وإلا null. */
    public static function parseDate(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }
        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)->startOfDay();
        }
        if (is_int($value) || is_float($value) || (is_string($value) && is_numeric($value))) {
            $serial = (float) $value;
            if ($serial < 1 || $serial > 2958465) {
                return null;
            }

            return Carbon::instance(Date::excelToDateTimeObject($serial))->startOfDay();
        }

        $value = trim((string) $value);
        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{2,4})#', $value, $m)) {
            [$month, $day, $year] = [(int) $m[1], (int) $m[2], (int) $m[3]];
            if ($year < 100) {
                $year += 2000;
            }

            return checkdate($month, $day, $year) ? Carbon::create($year, $month, $day)->startOfDay() : null;
        }
        if (preg_match('#^(\d{4})[-/](\d{1,2})[-/](\d{1,2})#', $value, $m)) {
            return checkdate((int) $m[2], (int) $m[3], (int) $m[1]) ? Carbon::create((int) $m[1], (int) $m[2], (int) $m[3])->startOfDay() : null;
        }

        return null;
    }
}
