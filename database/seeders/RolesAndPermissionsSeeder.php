<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Сначала создаём разрешения
        Permission::firstOrCreate(['name' => 'view all projects']);
        Permission::firstOrCreate(['name' => 'edit projects']);
        Permission::firstOrCreate(['name' => 'suspend projects']);
        Permission::firstOrCreate(['name' => 'view transactions']);
        Permission::firstOrCreate(['name' => 'edit transactions']);
        Permission::firstOrCreate(['name' => 'manage feedback']);

        // Создаём роли
        $superadmin = Role::firstOrCreate(['name' => 'Superadmin']);
        $accountant = Role::firstOrCreate(['name' => 'Accountant']);
        $officeManager = Role::firstOrCreate(['name' => 'Office Manager']);
        $accountManager = Role::firstOrCreate(['name' => 'Account Manager']);

        // Присваиваем разрешения ролям
        $superadmin->syncPermissions(Permission::all()); // всё может
        $accountant->syncPermissions(['view all projects', 'view transactions']);
        $officeManager->syncPermissions(['view all projects', 'edit projects', 'view transactions']);
        $accountManager->syncPermissions(['view all projects', 'view transactions', 'manage feedback']);
    }
}
