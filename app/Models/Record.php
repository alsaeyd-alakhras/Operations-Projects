<?php

namespace App\Models;

use App\Observers\RecordObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    use HasFactory;

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
    ];

    protected static function boot()
    {
        parent::boot();

        // تسجيل الـ Observer
        static::observe(RecordObserver::class);
    }
}
