<?php

namespace Database\Seeders;

use App\Models\Qualification;
use App\Models\User;
use Illuminate\Database\Seeder;

class QualificationSeeder extends Seeder
{
    public function run(): void
    {
        $qualifications = [
            // Nurse qualifications
            [
                'email' => 'nurse@carenest.test',
                'items' => [
                    [
                        'title' => 'Registered Nurse License',
                        'type' => 'license',
                        'issuing_body' => 'Nursing Council of Kenya',
                        'issue_date' => '2020-06-01',
                        'expiry_date' => now()->addMonths(8)->format('Y-m-d'),
                        'status' => 'active',
                    ],
                    [
                        'title' => 'Basic Life Support (BLS)',
                        'type' => 'certification',
                        'issuing_body' => 'Kenya Red Cross',
                        'issue_date' => '2024-03-15',
                        'expiry_date' => now()->addDays(20)->format('Y-m-d'),
                        'status' => 'active',
                        'notes' => 'Renewal scheduled',
                    ],
                    [
                        'title' => 'Bachelor of Science in Nursing',
                        'type' => 'education',
                        'issuing_body' => 'University of Nairobi',
                        'issue_date' => '2019-12-15',
                        'expiry_date' => null,
                        'status' => 'active',
                    ],
                    [
                        'title' => 'Wound Care Management',
                        'type' => 'training',
                        'issuing_body' => 'Kenya Medical Training College',
                        'issue_date' => '2023-08-10',
                        'expiry_date' => now()->subDays(30)->format('Y-m-d'),
                        'status' => 'expired',
                    ],
                ],
            ],
            // Caregiver qualifications
            [
                'email' => 'caregiver@carenest.test',
                'items' => [
                    [
                        'title' => 'Certificate in Caregiving',
                        'type' => 'certification',
                        'issuing_body' => 'National Care Academy',
                        'issue_date' => '2023-09-01',
                        'expiry_date' => now()->addMonths(6)->format('Y-m-d'),
                        'status' => 'active',
                    ],
                    [
                        'title' => 'First Aid Training',
                        'type' => 'training',
                        'issuing_body' => 'St John Ambulance Kenya',
                        'issue_date' => '2024-01-20',
                        'expiry_date' => now()->addDays(15)->format('Y-m-d'),
                        'status' => 'active',
                        'notes' => 'Expiring soon - renewal booked',
                    ],
                    [
                        'title' => 'Dementia Care Awareness',
                        'type' => 'training',
                        'issuing_body' => 'Alzheimer Society of Kenya',
                        'issue_date' => '2024-06-10',
                        'expiry_date' => null,
                        'status' => 'active',
                    ],
                ],
            ],
            // Supervisor qualifications
            [
                'email' => 'supervisor@carenest.test',
                'items' => [
                    [
                        'title' => 'Registered Nurse License',
                        'type' => 'license',
                        'issuing_body' => 'Nursing Council of Kenya',
                        'issue_date' => '2018-03-01',
                        'expiry_date' => now()->addMonths(14)->format('Y-m-d'),
                        'status' => 'active',
                    ],
                    [
                        'title' => 'Advanced Cardiac Life Support',
                        'type' => 'certification',
                        'issuing_body' => 'Kenya Red Cross',
                        'issue_date' => '2023-11-01',
                        'expiry_date' => now()->addMonths(10)->format('Y-m-d'),
                        'status' => 'active',
                    ],
                    [
                        'title' => 'Healthcare Management Certificate',
                        'type' => 'certification',
                        'issuing_body' => 'Kenya Institute of Management',
                        'issue_date' => '2022-05-15',
                        'expiry_date' => now()->subDays(60)->format('Y-m-d'),
                        'status' => 'expired',
                        'notes' => 'Pending renewal application',
                    ],
                    [
                        'title' => 'Master of Science in Nursing',
                        'type' => 'education',
                        'issuing_body' => 'Kenyatta University',
                        'issue_date' => '2021-12-10',
                        'expiry_date' => null,
                        'status' => 'active',
                    ],
                ],
            ],
        ];

        foreach ($qualifications as $group) {
            $user = User::where('email', $group['email'])->first();
            if (! $user) {
                continue;
            }

            foreach ($group['items'] as $item) {
                Qualification::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'title' => $item['title'],
                    ],
                    [
                        'type' => $item['type'],
                        'issuing_body' => $item['issuing_body'] ?? null,
                        'issue_date' => $item['issue_date'] ?? null,
                        'expiry_date' => $item['expiry_date'] ?? null,
                        'status' => $item['status'],
                        'notes' => $item['notes'] ?? null,
                    ]
                );
            }
        }
    }
}
