<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            // User & Role Management
            'manage-users',
            'manage-roles',
            'view-audit-logs',

            // Resident Management
            'manage-residents',
            'view-residents',

            // Staff Management
            'manage-staff',
            'view-staff',

            // Clinical / Medical
            'manage-medications',
            'administer-medications',
            'manage-care-plans',
            'view-care-plans',

            // Therapy
            'manage-therapy',
            'view-therapy',
            'conduct-therapy',

            // Activities & Incidents
            'log-activities',
            'manage-incidents',
            'report-incidents',

            // Reports & Settings
            'view-reports',
            'manage-settings',

            // Mentorship
            'manage-mentorship',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $systemAdmin = Role::firstOrCreate(['name' => 'system_admin']);
        $systemAdmin->syncPermissions($permissions); // Admin gets all permissions

        $careHomeManager = Role::firstOrCreate(['name' => 'care_home_manager']);
        $careHomeManager->syncPermissions([
            'view-audit-logs',
            'manage-residents',
            'view-residents',
            'manage-staff',
            'view-staff',
            'manage-medications',
            'manage-care-plans',
            'view-care-plans',
            'manage-therapy',
            'view-therapy',
            'log-activities',
            'manage-incidents',
            'report-incidents',
            'view-reports',
            'manage-mentorship',
        ]);

        $nurse = Role::firstOrCreate(['name' => 'nurse']);
        $nurse->syncPermissions([
            'view-residents',
            'view-staff',
            'manage-medications',
            'administer-medications',
            'manage-care-plans',
            'view-care-plans',
            'view-therapy',
            'log-activities',
            'manage-incidents',
            'report-incidents',
        ]);

        $caregiver = Role::firstOrCreate(['name' => 'caregiver']);
        $caregiver->syncPermissions([
            'view-residents',
            'view-staff',
            'view-care-plans',
            'log-activities',
            'report-incidents',
        ]);

        $therapist = Role::firstOrCreate(['name' => 'therapist']);
        $therapist->syncPermissions([
            'view-residents',
            'view-care-plans',
            'view-therapy',
            'conduct-therapy',
            'view-reports',
        ]);
    }
}
