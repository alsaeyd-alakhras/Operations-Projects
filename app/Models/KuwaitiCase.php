<?php

namespace App\Models;

use App\Observers\KuwaitiCaseObserver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class KuwaitiCase extends Model
{
    public const GENDERS = [
        'male' => 'ذكر',
        'female' => 'أنثى',
    ];

    public const MARITAL_STATUSES = [
        'single' => 'أعزب/ عزباء',
        'married' => 'متزوج / متزوجة',
        'divorced' => 'مطلق/ مطلقة',
        'widowed' => 'أرمل/ أرملة',
        'orphan' => 'طفل يتيم / يتيمة',
    ];

    public const NEED_PERCENTAGES = [
        50 => '50%',
        33 => '33%',
        25 => '25%',
    ];

    /** تفاصيل الفحص التشخيصي ومدى الحاجة إليه (الوزن: 30) */
    public const EXAM_NEEDS = [
        'urgent' => ['label' => 'فحص ضروري وعاجل ويؤثر تأخيره على التشخيص أو العلاج', 'points' => 30],
        'important' => ['label' => 'فحص مهم ويحتاج إلى إجرائه خلال فترة قريبة', 'points' => 20],
        'medium' => ['label' => 'فحص متوسط الأهمية ويمكن تأجيله لفترة محدودة', 'points' => 10],
        'routine' => ['label' => 'فحص روتيني / مجدول', 'points' => 5],
    ];

    /** حالة النزوح والسكن وتضرر المسكن (الوزن: 30) */
    public const HOUSING_STATUSES = [
        'tent' => ['label' => 'نازح يعيش في خيمة أو مركز إيواء', 'points' => 30],
        'rented_arrears' => ['label' => 'نازح في سكن مستأجر مع وجود متأخرات إيجار', 'points' => 20],
        'damaged' => ['label' => 'مقيم في منزل متضرر جزئيًا', 'points' => 10],
        'safe' => ['label' => 'مقيم في منزل سليم', 'points' => 5],
    ];

    /** مصادر الدخل والوضع المالي (الوزن: 30) */
    public const INCOME_STATUSES = [
        'unemployed' => ['label' => 'عاطل عن العمل / دون دخل ثابت', 'points' => 30],
        'daily' => ['label' => 'عامل يومية أو دخل محدود وغير منتظم', 'points' => 20],
        'employee' => ['label' => 'موظف / دخل متوسط ومحدود', 'points' => 10],
        'stable' => ['label' => 'دخل مستقر وكافٍ', 'points' => 5],
    ];

    /** حجم الأسرة والحالات الاجتماعية الخاصة (الوزن: 10) */
    public const FAMILY_BURDENS = [
        'high' => ['label' => 'عبء اجتماعي مرتفع / عدد كبير من المعالين', 'points' => 10],
        'medium' => ['label' => 'عبء اجتماعي متوسط', 'points' => 7],
        'low' => ['label' => 'عبء اجتماعي منخفض', 'points' => 5],
    ];

    protected $fillable = [
        'date',
        'name',
        'gender',
        'patient_ID',
        'birth_date',
        'marital_status',
        'address',
        'displacement_address',
        'phone_number',
        'exam',
        'exam_cost',
        'exam_need',
        'housing_status',
        'income_status',
        'family_burden',
        'total_score',
        'data_entry',
        'case_description',
        'branch_id',
        'eligible',
        'need_percentage',
        'branch_notes',
        'done',
        'management_status',
        'management_notes',
        'user_id',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'birth_date' => 'date:Y-m-d',
        'eligible' => 'boolean',
        'done' => 'boolean',
        'need_percentage' => 'integer',
        'total_score' => 'integer',
    ];

    protected $appends = ['age'];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /** إجمالي النقاط = مجموع نقاط البنود 11 إلى 14 في الاستمارة. */
    public static function scoreFor(array $attrs): int
    {
        return (self::EXAM_NEEDS[$attrs['exam_need'] ?? '']['points'] ?? 0)
            + (self::HOUSING_STATUSES[$attrs['housing_status'] ?? '']['points'] ?? 0)
            + (self::INCOME_STATUSES[$attrs['income_status'] ?? '']['points'] ?? 0)
            + (self::FAMILY_BURDENS[$attrs['family_burden'] ?? '']['points'] ?? 0);
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birth_date ? $this->birth_date->age : null;
    }

    public function getGenderLabelAttribute(): ?string
    {
        return self::GENDERS[$this->gender] ?? null;
    }

    public function getMaritalStatusLabelAttribute(): ?string
    {
        return self::MARITAL_STATUSES[$this->marital_status] ?? null;
    }

    public function getExamNeedLabelAttribute(): ?string
    {
        return self::EXAM_NEEDS[$this->exam_need]['label'] ?? null;
    }

    public function getHousingStatusLabelAttribute(): ?string
    {
        return self::HOUSING_STATUSES[$this->housing_status]['label'] ?? null;
    }

    public function getIncomeStatusLabelAttribute(): ?string
    {
        return self::INCOME_STATUSES[$this->income_status]['label'] ?? null;
    }

    public function getFamilyBurdenLabelAttribute(): ?string
    {
        return self::FAMILY_BURDENS[$this->family_burden]['label'] ?? null;
    }

    public function getNeedPercentageLabelAttribute(): ?string
    {
        return self::NEED_PERCENTAGES[$this->need_percentage] ?? null;
    }

    public function getEligibleLabelAttribute(): string
    {
        return is_null($this->eligible) ? 'غير محدد' : ($this->eligible ? 'يستحق' : 'لا يستحق');
    }

    public function getDoneLabelAttribute(): string
    {
        return $this->done ? 'نعم' : 'لا';
    }

    public function getManagementStatusLabelAttribute(): string
    {
        return Record::MANAGEMENT_STATUSES[$this->management_status] ?? 'لم يحدد';
    }

    protected static function boot()
    {
        parent::boot();

        // يوزر الفرع يرى حالات فرعه فقط
        static::addGlobalScope('branch', function (Builder $query) {
            $user = Auth::user();
            if ($user && $user->branch_id) {
                $query->where($query->getModel()->getTable() . '.branch_id', $user->branch_id);
            }
        });

        static::saving(function (KuwaitiCase $case) {
            $case->total_score = self::scoreFor($case->getAttributes());

            // تحديد «تمت» ينقل الحالة مباشرة إلى الأرشيف، وإلغاؤه يعيدها إلى القائمة الأساسية
            if ($case->isDirty('done')) {
                if ($case->done) {
                    $case->archived = true;
                } elseif ($case->exists) {
                    $case->archived = false;
                }
            }
        });

        static::observe(KuwaitiCaseObserver::class);
    }
}
