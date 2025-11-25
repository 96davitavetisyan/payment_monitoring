<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersSeeder extends Seeder
{
    public function run()
    {
        // Super Administrator
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            ['name' => 'Super Admin', 'password' => bcrypt('password')]
        );
        $superadmin->assignRole('Super Administrator');

        // Accountant
        $accountant = User::firstOrCreate(
            ['email' => 'accountant@example.com'],
            ['name' => 'Accountant', 'password' => bcrypt('password')]
        );
        $accountant->assignRole('Accountant');

        // Office Manager
        $officeManager = User::firstOrCreate(
            ['email' => 'officemanager@example.com'],
            ['name' => 'Office Manager', 'password' => bcrypt('password')]
        );
        $officeManager->assignRole('Office Manager');

        // Client Relationship Manager
        $clientManager = User::firstOrCreate(
            ['email' => 'clientmanager@example.com'],
            ['name' => 'Client Relationship Manager', 'password' => bcrypt('password')]
        );
        $clientManager->assignRole('Client Relationship Manager');
    }
}
