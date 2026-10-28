<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'last_activity',
        'super_admin',
        'branch_id',
        'column_profile_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function columnProfile(): BelongsTo
    {
        return $this->belongsTo(ColumnProfile::class);
    }

    /**
     * الأعمدة المسموحة سيرفريًا لجدول السجلات: null = بلا قيود.
     * القيد فقط لملف أعمدة «مقيّد» ولغير المدير العام. العمود name دائمًا مسموح.
     */
    public function allowedRecordColumns(): ?array
    {
        if ($this->super_admin) {
            return null;
        }
        $profile = $this->column_profile_id ? $this->columnProfile : null;
        $isBranch = $this->isBranchUser();
        if (! $isBranch && (! $profile || ! $profile->restricted)) {
            return null;
        }

        // مستخدم الفرع مقيّد دائماً، حتى بدون ملف أعمدة (الافتراضي: الأعمدة الأساسية)، ولا يرى الأعمدة المالية أبداً
        $columns = $profile ? $profile->validColumns() : ColumnProfile::GROUPS['أساسية'];
        if ($isBranch) {
            $columns = array_values(array_diff($columns, ColumnProfile::GROUPS['مالية']));
        }

        return array_merge(['name'], $columns);
    }

    /** الأعمدة المخفية افتراضيًا (لمن ليس لديه اختيار محفوظ) حسب ملف الأعمدة. */
    public function defaultHiddenRecordColumns(): array
    {
        if (! $this->column_profile_id || ! ($profile = $this->columnProfile)) {
            return [];
        }

        return array_values(array_diff(array_keys(ColumnProfile::COLUMNS), $profile->validColumns()));
    }

    public function isBranchUser(): bool
    {
        return ! is_null($this->branch_id);
    }

    public function roles() :HasMany
    {
        return $this->hasMany(RoleUser::class,'user_id','id');
    }
}
