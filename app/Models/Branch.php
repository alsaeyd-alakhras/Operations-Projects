<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $fillable = ['name'];

    public function records(): HasMany
    {
        return $this->hasMany(Record::class)->withoutGlobalScopes();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
