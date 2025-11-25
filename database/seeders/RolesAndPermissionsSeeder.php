<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Create permissions with underscores
        Permission::firstOrCreate(['name' => 'create_projects']);
        Permission::firstOrCreate(['name' => 'edit_projects']);
        Permission::firstOrCreate(['name' => 'view_projects']);
        Permission::firstOrCreate(['name' => 'delete_projects']);
        Permission::firstOrCreate(['name' => 'view_all_projects']);
        Permission::firstOrCreate(['name' => 'suspend_projects']);
        Permission::firstOrCreate(['name' => 'activate_projects']);
        Permission::firstOrCreate(['name' => 'view_transactions']);
        Permission::firstOrCreate(['name' => 'edit_transactions']);
        Permission::firstOrCreate(['name' => 'create_transactions']);
        Permission::firstOrCreate(['name' => 'delete_transactions']);
        Permission::firstOrCreate(['name' => 'manage_feedback']);
        Permission::firstOrCreate(['name' => 'create_feedback']);
        Permission::firstOrCreate(['name' => 'view_feedback']);

        // Create roles
        $superAdmin = Role::firstOrCreate(['name' => 'Super Administrator']);
        $accountant = Role::firstOrCreate(['name' => 'Accountant']);
        $officeManager = Role::firstOrCreate(['name' => 'Office Manager']);
        $clientManager = Role::firstOrCreate(['name' => 'Client Relationship Manager']);

        // Assign permissions according to specification
        
        // Super Administrator: can create and edit projects, view, delete, and add new projects
        $superAdmin->syncPermissions([
            'create_projects',
            'edit_projects',
            'view_projects',
            'view_all_projects',
            'delete_projects',
            'suspend_projects',
            'activate_projects',
            'view_transactions',
            'edit_transactions',
            'create_transactions',
            'delete_transactions',
            'view_feedback',
        ]);

        // Accountant: can suspend or activate projects, view and edit Clients/Transactions table
        $accountant->syncPermissions([
            'view_all_projects',
            'suspend_projects',
            'activate_projects',
            'view_transactions',
            'edit_transactions',
            'create_transactions',
            'delete_transactions',
        ]);

        // Office Manager: can view projects and manage office operations
        $officeManager->syncPermissions([
            'view_all_projects',
            'view_transactions',
        ]);

        // Client Relationship Manager: can record feedback from clients
        $clientManager->syncPermissions([
            'view_all_projects',
            'view_transactions',
            'manage_feedback',
            'create_feedback',
            'view_feedback',
        ]);
    }
}
