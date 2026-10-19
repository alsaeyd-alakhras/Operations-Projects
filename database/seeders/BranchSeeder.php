<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\RoleUser;
use App\Models\User;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    private const BRANCHES = [
        'المغازي' => 'maghazi',
        'البريج' => 'bureij',
        'دير البلح' => 'deir_balah',
        'النصيرات' => 'nuseirat',
        'الزوايدة' => 'zawayda',
        'غزة' => 'gaza',
        'جباليا' => 'jabalia',
        'رفح' => 'rafah',
        'خانيونس' => 'khanyounis',
    ];

    private const ABILITIES = ['records.view', 'records.update', 'records.archive'];

    public function run(): void
    {
        foreach (self::BRANCHES as $name => $slug) {
            $branch = Branch::firstOrCreate(['name' => $name]);

            $user = User::firstOrCreate(
                ['username' => 'branch_' . $slug],
                ['name' => 'مستخدم فرع ' . $name, 'password' => 'Branch@123', 'branch_id' => $branch->id]
            );

            foreach (self::ABILITIES as $ability) {
                RoleUser::firstOrCreate(
                    ['role_name' => $ability, 'user_id' => $user->id],
                    ['ability' => 'allow']
                );
            }
        }
    }
}
