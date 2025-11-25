<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersSeeder extends Seeder
{
    public function run()
    {
        // Пользователь Superadmin
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            ['name' => 'Super Admin', 'password' => bcrypt('password')]
        );
        $superadmin->assignRole('Superadmin');

        // Пользователь Accountant
        $accountant = User::firstOrCreate(
            ['email' => 'accountant@example.com'],
            ['name' => 'Accountant', 'password' => bcrypt('password')]
        );
        $accountant->assignRole('Accountant');

        // Пользователь Account Manager
        $accountManager = User::firstOrCreate(
            ['email' => 'accountmanager@example.com'],
            ['name' => 'Account Manager', 'password' => bcrypt('password')]
        );
        $accountManager->assignRole('Account Manager');

        // Пользователь Office Manager
        $officeManager = User::firstOrCreate(
            ['email' => 'officemanager@example.com'],
            ['name' => 'Office Manager', 'password' => bcrypt('password')]
        );
        $officeManager->assignRole('Office Manager');
    }
}
