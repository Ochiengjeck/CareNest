<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            // TestUserSeeder::class,
            // SystemSettingsSeeder::class,
            // ResidentSeeder::class,
            // CarePlanSeeder::class,
            // MedicationSeeder::class,
            // MedicationLogSeeder::class,
            // VitalSeeder::class,
            // IncidentSeeder::class,
            // StaffProfileSeeder::class,
            // QualificationSeeder::class,
            // ShiftSeeder::class,
            // TherapistAssignmentSeeder::class,
            // TherapySessionSeeder::class,
        ]);
    }
}
