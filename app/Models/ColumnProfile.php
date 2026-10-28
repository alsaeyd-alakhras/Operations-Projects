<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ملف أعمدة: مجموعة جاهزة من أعمدة جدول السجلات (شبيه بالدور).
 * - غير مقيّد: الأعمدة هي العرض الافتراضي فقط والمستخدم يغيّرها بحرية.
 * - مقيّد: السيرفر لا يرسل ولا يصدّر ولا يسمح بالتصفية إلا بهذه الأعمدة.
 */
class ColumnProfile extends Model
{
    /** المصدر الوحيد لأعمدة الجدول القابلة للتحكم (اسم العمود => التسمية). اسم المريض دائمًا ظاهر. */
    public const COLUMNS = [
        'done' => 'تمت',
        'date' => 'التاريخ',
        'financier' => 'اسم الممول',
        'financier_number' => 'رقم الممول',
        'age' => 'العمر',
        'patient_ID' => 'رقم الهوية',
        'phone_number1' => 'جوال 1',
        'phone_number2' => 'جوال 2',
        'operation' => 'العملية',
        'doctor' => 'الطبيب',
        'eligible_label' => 'يستحق',
        'branch_name' => 'الفرع',
        'branch_notes' => 'ملاحظات الفرع',
        'management_status' => 'موافقة الإدارة',
        'amount' => 'التكلفة',
        'doctor_share' => 'حصة الطبيب',
        'anesthesia' => 'طبيب التخدير',
        'anesthesiologists_share' => 'حصة التخدير',
        'bed' => 'المبيت',
        'private' => 'خاص',
        'address' => 'العنوان',
        'displacement_status' => 'حالة النزوح والسكن وتضرر البيت',
        'income_status' => 'مصادر الدخل والوضع المالي',
        'family_burden' => 'حجم الأسرة والحالات الخاصة',
        'distributed' => 'تم التوزيع',
        'notes' => 'ملاحظات',
        'notes_2' => 'ملاحظات ثانوية',
        'user_name' => 'المستخدم',
    ];

    /** مجموعات العرض في نموذج الإنشاء. */
    public const GROUPS = [
        'أساسية' => ['done', 'date', 'financier', 'financier_number', 'age', 'patient_ID', 'phone_number1', 'phone_number2', 'operation', 'doctor', 'eligible_label', 'branch_name', 'branch_notes', 'management_status'],
        'مالية' => ['amount', 'doctor_share', 'anesthesia', 'anesthesiologists_share', 'bed', 'private'],
        'توزيع واجتماعية' => ['address', 'displacement_status', 'income_status', 'family_burden', 'distributed'],
        'ملاحظات' => ['notes', 'notes_2', 'user_name'],
    ];

    /** مفاتيح بيانات DataTables/التصدير التي يجب تفريغها عند منع العمود. */
    public const DATA_KEYS = [
        'financier' => ['financier', 'financier_name'],
        'eligible_label' => ['eligible', 'eligible_label'],
        'branch_name' => ['branch_id', 'branch_name', 'branch_name_sql'],
        'management_status' => ['management_status', 'management_status_label', 'management_notes'],
        'displacement_status' => ['displacement_status', 'displacement_status_label'],
        'income_status' => ['income_status', 'income_status_label'],
        'family_burden' => ['family_burden', 'family_burden_label'],
        'distributed' => ['distributed', 'distributed_label'],
    ];

    /** مفتاح فلتر الواجهة => اسم العمود (حيث يختلفان). */
    public const FILTER_ALIASES = [
        'eligible' => 'eligible_label',
        'management_notes' => 'management_status',
    ];

    protected $fillable = ['name', 'columns', 'restricted'];

    protected function casts(): array
    {
        return ['columns' => 'array', 'restricted' => 'boolean'];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** الأعمدة المحفوظة بعد تنقيتها من أي اسم غير معروف. */
    public function validColumns(): array
    {
        return array_values(array_intersect(array_keys(self::COLUMNS), (array) $this->columns));
    }

    /** اسم العمود المقابل لمفتاح فلتر. */
    public static function columnForFilter(string $key): string
    {
        return self::FILTER_ALIASES[$key] ?? $key;
    }

    /** مفاتيح البيانات التي تخص عمودًا واحدًا. */
    public static function dataKeys(string $column): array
    {
        return self::DATA_KEYS[$column] ?? [$column];
    }

    /** تفريغ قيم الأعمدة غير المسموحة من صف (مصفوفة) مع إبقاء المفاتيح حتى لا تفشل القوالب. */
    public static function stripRow(array $row, ?array $allowed): array
    {
        if ($allowed === null) {
            return $row;
        }
        foreach (array_diff(array_keys(self::COLUMNS), $allowed) as $column) {
            foreach (self::dataKeys($column) as $key) {
                if (array_key_exists($key, $row)) {
                    $row[$key] = null;
                }
            }
        }

        return $row;
    }

    /** ملفات افتراضية؛ لا تعدّل ما أنشأه المستخدم (firstOrCreate بالاسم). */
    public static function seedDefaults(): void
    {
        $all = array_keys(self::COLUMNS);
        $basic = self::GROUPS['أساسية'];
        $defaults = [
            ['إدارة', $all, false],
            ['مالية', array_merge($basic, self::GROUPS['مالية']), false],
            ['عمليات', $basic, false],
            ['فرع', $basic, true],
            ['كامل', $all, false],
        ];
        foreach ($defaults as [$name, $columns, $restricted]) {
            self::firstOrCreate(['name' => $name], ['columns' => $columns, 'restricted' => $restricted]);
        }
    }
}
