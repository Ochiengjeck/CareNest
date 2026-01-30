<?php

namespace Database\Seeders;

use App\Models\Resident;
use App\Models\TherapistAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;

class TherapistAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $therapists = User::role('therapist')->get();
        $residents = Resident::active()->get();
        $assignedBy = User::role('care_home_manager')->first()?->id
            ?? User::role('system_admin')->first()?->id;

        if ($therapists->isEmpty() || $residents->isEmpty()) {
            return;
        }

        // Distribute residents among therapists
        foreach ($residents as $index => $resident) {
            $therapist = $therapists[$index % $therapists->count()];

            TherapistAssignment::firstOrCreate(
                [
                    'therapist_id' => $therapist->id,
                    'resident_id' => $resident->id,
                ],
                [
                    'assigned_date' => $resident->admission_date ?? now()->subDays(rand(7, 60)),
                    'status' => 'active',
                    'notes' => null,
                    'assigned_by' => $assignedBy,
                ]
            );
        }
    }
}
