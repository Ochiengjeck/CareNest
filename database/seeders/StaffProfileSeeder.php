<?php

namespace Database\Seeders;

use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class StaffProfileSeeder extends Seeder
{
    public function run(): void
    {
        $profiles = [
            [
                'email' => 'admin@carenest.test',
                'employee_id' => 'EMP001',
                'department' => 'Administration',
                'position' => 'System Administrator',
                'hire_date' => '2023-01-15',
                'phone' => '+254 700 100 001',
                'address' => '123 Kenyatta Avenue, Nairobi',
                'employment_status' => 'active',
                'emergency_contact_name' => 'Jane Wanjiku',
                'emergency_contact_phone' => '+254 700 200 001',
                'emergency_contact_relationship' => 'Spouse',
            ],
            [
                'email' => 'manager@carenest.test',
                'employee_id' => 'EMP002',
                'department' => 'Management',
                'position' => 'Care Home Manager',
                'hire_date' => '2023-03-01',
                'phone' => '+254 700 100 002',
                'address' => '456 Moi Avenue, Nairobi',
                'employment_status' => 'active',
                'emergency_contact_name' => 'Peter Kamau',
                'emergency_contact_phone' => '+254 700 200 002',
                'emergency_contact_relationship' => 'Brother',
            ],
            [
                'email' => 'nurse@carenest.test',
                'employee_id' => 'EMP003',
                'department' => 'Nursing',
                'position' => 'Senior Nurse',
                'hire_date' => '2023-06-15',
                'phone' => '+254 700 100 003',
                'address' => '789 Uhuru Highway, Nairobi',
                'employment_status' => 'active',
                'emergency_contact_name' => 'Mary Akinyi',
                'emergency_contact_phone' => '+254 700 200 003',
                'emergency_contact_relationship' => 'Mother',
            ],
            [
                'email' => 'caregiver@carenest.test',
                'employee_id' => 'EMP004',
                'department' => 'Care',
                'position' => 'Caregiver',
                'hire_date' => '2024-01-10',
                'phone' => '+254 700 100 004',
                'address' => '321 Ngong Road, Nairobi',
                'employment_status' => 'active',
                'emergency_contact_name' => 'David Ochieng',
                'emergency_contact_phone' => '+254 700 200 004',
                'emergency_contact_relationship' => 'Father',
            ],
            [
                'email' => 'supervisor@carenest.test',
                'employee_id' => 'EMP005',
                'department' => 'Nursing',
                'position' => 'Nursing Supervisor',
                'hire_date' => '2023-04-20',
                'phone' => '+254 700 100 005',
                'address' => '654 Kimathi Street, Nairobi',
                'employment_status' => 'active',
                'emergency_contact_name' => 'Grace Njeri',
                'emergency_contact_phone' => '+254 700 200 005',
                'emergency_contact_relationship' => 'Spouse',
            ],
        ];

        $admin = User::where('email', 'admin@carenest.test')->first();

        foreach ($profiles as $data) {
            $user = User::where('email', $data['email'])->first();
            if (! $user) {
                continue;
            }

            unset($data['email']);

            StaffProfile::firstOrCreate(
                ['user_id' => $user->id],
                array_merge($data, [
                    'created_by' => $admin?->id,
                ])
            );
        }
    }
}
