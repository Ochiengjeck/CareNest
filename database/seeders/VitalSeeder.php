<?php

namespace Database\Seeders;

use App\Models\Resident;
use App\Models\User;
use App\Models\Vital;
use Illuminate\Database\Seeder;

class VitalSeeder extends Seeder
{
    public function run(): void
    {
        $nurses = User::role('nurse')->get();
        $caregivers = User::role('caregiver')->get();
        $staff = $nurses->merge($caregivers);

        if ($staff->isEmpty()) {
            return;
        }

        $activeResidents = Resident::active()->get();

        foreach ($activeResidents as $resident) {
            $recordCount = rand(3, 5);

            for ($i = 0; $i < $recordCount; $i++) {
                $recordedAt = now()->subDays(rand(0, 6))->subHours(rand(0, 12));
                $recordedBy = $staff->random();

                // Generate realistic vital signs with some variation
                $systolic = rand(100, 160);
                $diastolic = rand(55, 95);
                $heartRate = rand(55, 105);
                $temperature = round(rand(360, 385) / 10, 1);
                $respiratoryRate = rand(12, 22);
                $oxygenSaturation = rand(92, 100);
                $painLevel = rand(0, 6);

                Vital::create([
                    'resident_id' => $resident->id,
                    'recorded_at' => $recordedAt,
                    'blood_pressure_systolic' => $systolic,
                    'blood_pressure_diastolic' => $diastolic,
                    'heart_rate' => $heartRate,
                    'temperature' => $temperature,
                    'respiratory_rate' => $respiratoryRate,
                    'oxygen_saturation' => $oxygenSaturation,
                    'blood_sugar' => rand(0, 1) ? round(rand(35, 120) / 10, 1) : null,
                    'weight' => rand(0, 1) ? round(rand(450, 900) / 10, 1) : null,
                    'pain_level' => $painLevel,
                    'consciousness_level' => 'alert',
                    'notes' => $i === 0 ? 'Routine observations. Resident comfortable and settled.' : null,
                    'recorded_by' => $recordedBy->id,
                ]);
            }
        }
    }
}
