<?php

namespace App\Models;

use App\Observers\RecordObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    use HasFactory;

    public const DISPLACEMENT_STATUSES = [
        'displaced_tent' => 'نازح يعيش في خيمة أو مركز إيواء',
        'displaced_rented' => 'نازح في سكن مستأجر متراكم عليه الإيجار',
        'resident_damaged' => 'مقيم في منزله متضرر جزئيًا',
        'resident_safe' => 'مقيم في منزل سليم',
    ];

    public const INCOME_STATUSES = [
        'unemployed' => 'عاطل عن العمل',
        'gaza_gov_or_daily' => 'موظف في حكومة غزة أو عامل يومي',
        'mid_income' => 'موظف متوسط الدخل',
        'stable_income' => 'صاحب دخل مستقر وكافٍ',
    ];

    public const FAMILY_BURDENS = [
        'high' => 'عبء اجتماعي مرتفع',
        'medium' => 'عبء اجتماعي متوسط',
    ];

    protected $fillable = [
        'done', //2
        'date',
        'name',
        'financier_number',
        'age',
        'patient_ID',
        'phone_number1',
        'phone_number2',
        'operation',
        'doctor',
        'amount',
        'doctor_share',
        'anesthesia',
        'anesthesiologists_share',
        'bed',
        'private',
        'notes',
        'notes_2',
        'user_name',
        'user_id',
        'address',
        'displacement_status',
        'income_status',
        'family_burden',
        'distributed',
    ];

    protected $casts = [
        'distributed' => 'boolean',
    ];

    public function getDisplacementStatusLabelAttribute(): ?string
    {
        return self::DISPLACEMENT_STATUSES[$this->displacement_status] ?? null;
    }

    public function getIncomeStatusLabelAttribute(): ?string
    {
        return self::INCOME_STATUSES[$this->income_status] ?? null;
    }

    public function getFamilyBurdenLabelAttribute(): ?string
    {
        return self::FAMILY_BURDENS[$this->family_burden] ?? null;
    }

    public function getDistributedLabelAttribute(): ?string
    {
        return is_null($this->distributed) ? null : ($this->distributed ? 'نعم' : 'لا');
    }

    protected static function boot()
    {
        parent::boot();

        // تسجيل الـ Observer
        static::observe(RecordObserver::class);
    }
}
