<?php

namespace Database\Seeders;

use App\Models\Medication;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Database\Seeder;

class MedicationSeeder extends Seeder
{
    public function run(): void
    {
        $nurse = User::role('nurse')->first();
        $createdBy = $nurse?->id ?? User::role('care_home_manager')->first()?->id;

        $activeResidents = Resident::active()->get();

        $medications = [
            ['name' => 'Metformin', 'dosage' => '500mg', 'frequency' => 'Twice daily', 'route' => 'oral', 'prescribed_by' => 'Dr. Kamau', 'status' => 'active', 'instructions' => 'Take with meals. Monitor blood sugar levels.'],
            ['name' => 'Lisinopril', 'dosage' => '10mg', 'frequency' => 'Once daily', 'route' => 'oral', 'prescribed_by' => 'Dr. Ochieng', 'status' => 'active', 'instructions' => 'Take in the morning. Monitor blood pressure.'],
            ['name' => 'Amlodipine', 'dosage' => '5mg', 'frequency' => 'Once daily', 'route' => 'oral', 'prescribed_by' => 'Dr. Ochieng', 'status' => 'active', 'instructions' => 'Take at bedtime.'],
            ['name' => 'Paracetamol', 'dosage' => '500mg', 'frequency' => 'Every 6 hours as needed', 'route' => 'oral', 'prescribed_by' => 'Dr. Wanjiru', 'status' => 'active', 'instructions' => 'For pain management. Maximum 4 doses per day.'],
            ['name' => 'Omeprazole', 'dosage' => '20mg', 'frequency' => 'Once daily', 'route' => 'oral', 'prescribed_by' => 'Dr. Kamau', 'status' => 'active', 'instructions' => 'Take 30 minutes before breakfast.'],
            ['name' => 'Levodopa/Carbidopa', 'dosage' => '250/25mg', 'frequency' => 'Three times daily', 'route' => 'oral', 'prescribed_by' => 'Dr. Njuguna', 'status' => 'active', 'instructions' => 'Take 30 minutes before meals. Monitor for dyskinesia.'],
            ['name' => 'Furosemide', 'dosage' => '40mg', 'frequency' => 'Once daily', 'route' => 'oral', 'prescribed_by' => 'Dr. Ochieng', 'status' => 'active', 'instructions' => 'Take in the morning. Monitor fluid intake/output and potassium levels.'],
            ['name' => 'Insulin Glargine', 'dosage' => '20 units', 'frequency' => 'Once daily at bedtime', 'route' => 'injection', 'prescribed_by' => 'Dr. Kamau', 'status' => 'active', 'instructions' => 'Inject subcutaneously in abdomen. Rotate injection sites.'],
            ['name' => 'Salbutamol Inhaler', 'dosage' => '100mcg', 'frequency' => 'As needed', 'route' => 'inhalation', 'prescribed_by' => 'Dr. Wanjiru', 'status' => 'active', 'instructions' => '2 puffs when needed for breathlessness. Rinse mouth after use.'],
            ['name' => 'Donepezil', 'dosage' => '10mg', 'frequency' => 'Once daily', 'route' => 'oral', 'prescribed_by' => 'Dr. Njuguna', 'status' => 'active', 'instructions' => 'Take at bedtime. For dementia management.'],
            ['name' => 'Sertraline', 'dosage' => '50mg', 'frequency' => 'Once daily', 'route' => 'oral', 'prescribed_by' => 'Dr. Njuguna', 'status' => 'active', 'instructions' => 'Take in the morning. Monitor mood and appetite.'],
            ['name' => 'Calcium + Vitamin D', 'dosage' => '500mg/400IU', 'frequency' => 'Twice daily', 'route' => 'oral', 'prescribed_by' => 'Dr. Wanjiru', 'status' => 'active', 'instructions' => 'Take with meals for osteoporosis prevention.'],
            ['name' => 'Diclofenac Gel', 'dosage' => '1%', 'frequency' => 'Three times daily', 'route' => 'topical', 'prescribed_by' => 'Dr. Wanjiru', 'status' => 'active', 'instructions' => 'Apply to affected joints. Avoid broken skin.'],
            ['name' => 'GTN Sublingual', 'dosage' => '0.5mg', 'frequency' => 'As needed for chest pain', 'route' => 'sublingual', 'prescribed_by' => 'Dr. Ochieng', 'status' => 'on_hold', 'instructions' => 'Place under tongue for angina. Sit down before use. Call emergency if no relief after 3 doses.'],
            ['name' => 'Amoxicillin', 'dosage' => '500mg', 'frequency' => 'Three times daily', 'route' => 'oral', 'prescribed_by' => 'Dr. Kamau', 'status' => 'completed', 'instructions' => 'Complete full 7-day course.'],
            ['name' => 'Tramadol', 'dosage' => '50mg', 'frequency' => 'Every 8 hours', 'route' => 'oral', 'prescribed_by' => 'Dr. Wanjiru', 'status' => 'discontinued', 'instructions' => 'Discontinued due to side effects. Replaced with alternative pain management.'],
            ['name' => 'Warfarin', 'dosage' => '3mg', 'frequency' => 'Once daily', 'route' => 'oral', 'prescribed_by' => 'Dr. Ochieng', 'status' => 'active', 'instructions' => 'Take at the same time each day. Regular INR monitoring required.'],
            ['name' => 'Lactulose', 'dosage' => '15ml', 'frequency' => 'Twice daily', 'route' => 'oral', 'prescribed_by' => 'Dr. Kamau', 'status' => 'active', 'instructions' => 'For constipation management. Adjust dose based on response.'],
        ];

        foreach ($activeResidents as $index => $resident) {
            $count = ($index % 3 === 0) ? 3 : 2;
            $offset = ($index * 2) % count($medications);

            for ($i = 0; $i < $count; $i++) {
                $medIndex = ($offset + $i) % count($medications);
                $med = $medications[$medIndex];

                $startDate = $resident->admission_date->copy()->addDays(rand(1, 14));

                Medication::firstOrCreate(
                    [
                        'resident_id' => $resident->id,
                        'name' => $med['name'],
                    ],
                    [
                        'dosage' => $med['dosage'],
                        'frequency' => $med['frequency'],
                        'route' => $med['route'],
                        'prescribed_by' => $med['prescribed_by'],
                        'prescribed_date' => $startDate->copy()->subDays(rand(1, 7)),
                        'start_date' => $startDate,
                        'end_date' => $med['status'] === 'completed' ? $startDate->copy()->addDays(7) : null,
                        'status' => $med['status'],
                        'instructions' => $med['instructions'],
                        'created_by' => $createdBy,
                    ]
                );
            }
        }
    }
}
