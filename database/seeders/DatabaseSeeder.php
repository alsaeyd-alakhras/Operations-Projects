<?php

namespace Database\Seeders;

use App\Models\Record;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        // Record::factory(100)->create();

        User::create([
            'name' => 'Administrator',
            'username' => 'saeyd_jamal',
            'password' => Hash::make('20051118Jamal'),
            'email' => 'alsaeydjalkhras@gmail.com',
            'super_admin' => 1
        ]);
    }
}
