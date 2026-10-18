<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Financier extends Model
{
    use HasFactory;

    protected $fillable = [
        'financier_number',
        'name',
        'stage',
        'maneger_name',
        'amount_ils',
        'number_cases',
        'completion_project',
        'push_project',
        'project_distribution',
        'project_archive',
        'archive',
    ];
}
