<?php

namespace Database\Seeders;

use App\Models\Medication;
use App\Models\MedicationLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class MedicationLogSeeder extends Seeder
{
    public function run(): void
    {
        $nurses = User::role('nurse')->get();
        $caregivers = User::role('caregiver')->get();
        $staff = $nurses->merge($caregivers);

        if ($staff->isEmpty()) {
            return;
        }

        $activeMedications = Medication::active()->get();

        $statuses = ['given', 'given', 'given', 'given', 'refused', 'withheld'];
        $refusalNotes = [
            'Resident refused medication, stated feeling nauseous.',
            'Resident was asleep, will retry later.',
            'Resident refused - complained of stomach upset.',
        ];
        $withheldNotes = [
            'Withheld - blood pressure too low (85/55).',
            'Withheld pending doctor review.',
            'Withheld - resident vomiting.',
        ];

        foreach ($activeMedications as $medication) {
            $logCount = rand(3, 5);

            for ($i = 0; $i < $logCount; $i++) {
                $administeredAt = now()->subDays(rand(0, 4))->subHours(rand(0, 12));
                $status = $statuses[array_rand($statuses)];
                $administeredBy = $staff->random();

                $notes = null;
                if ($status === 'refused') {
                    $notes = $refusalNotes[array_rand($refusalNotes)];
                } elseif ($status === 'withheld') {
                    $notes = $withheldNotes[array_rand($withheldNotes)];
                }

                MedicationLog::create([
                    'medication_id' => $medication->id,
                    'resident_id' => $medication->resident_id,
                    'administered_at' => $administeredAt,
                    'status' => $status,
                    'notes' => $notes,
                    'administered_by' => $administeredBy->id,
                ]);
            }
        }
    }
}
