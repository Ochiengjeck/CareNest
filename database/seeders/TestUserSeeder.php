<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        // System Administrator
        $admin = User::firstOrCreate(
            ['email' => 'admin@carenest.test'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('system_admin');

        // Care Home Manager
        $manager = User::firstOrCreate(
            ['email' => 'manager@carenest.test'],
            [
                'name' => 'Care Home Manager',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $manager->assignRole('care_home_manager');

        // Nurse
        $nurse = User::firstOrCreate(
            ['email' => 'nurse@carenest.test'],
            [
                'name' => 'Senior Nurse',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $nurse->assignRole('nurse');

        // Caregiver
        $caregiver = User::firstOrCreate(
            ['email' => 'caregiver@carenest.test'],
            [
                'name' => 'Caregiver',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $caregiver->assignRole('caregiver');

        // Multi-role user (Nurse + Manager)
        $multiRole = User::firstOrCreate(
            ['email' => 'supervisor@carenest.test'],
            [
                'name' => 'Nursing Supervisor',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $multiRole->assignRole(['nurse', 'care_home_manager']);

        // User with no role (for testing empty state)
        User::firstOrCreate(
            ['email' => 'newuser@carenest.test'],
            [
                'name' => 'New User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
    }
}
