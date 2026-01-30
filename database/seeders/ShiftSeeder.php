<?php

namespace Database\Seeders;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $staffEmails = [
            'nurse@carenest.test',
            'caregiver@carenest.test',
            'supervisor@carenest.test',
            'manager@carenest.test',
        ];

        $staffUsers = User::whereIn('email', $staffEmails)->get()->keyBy('email');

        if ($staffUsers->isEmpty()) {
            return;
        }

        $manager = User::where('email', 'manager@carenest.test')->first();

        $shiftTemplates = [
            'morning' => ['start' => '06:00', 'end' => '14:00'],
            'afternoon' => ['start' => '14:00', 'end' => '22:00'],
            'night' => ['start' => '22:00', 'end' => '06:00'],
        ];

        $shifts = [
            // Past shifts (completed / no_show)
            ['email' => 'nurse@carenest.test', 'days_ago' => 5, 'type' => 'morning', 'status' => 'completed'],
            ['email' => 'caregiver@carenest.test', 'days_ago' => 5, 'type' => 'afternoon', 'status' => 'completed'],
            ['email' => 'supervisor@carenest.test', 'days_ago' => 4, 'type' => 'morning', 'status' => 'completed'],
            ['email' => 'nurse@carenest.test', 'days_ago' => 3, 'type' => 'night', 'status' => 'completed'],
            ['email' => 'caregiver@carenest.test', 'days_ago' => 3, 'type' => 'morning', 'status' => 'no_show', 'notes' => 'Called in sick - replacement arranged'],
            ['email' => 'supervisor@carenest.test', 'days_ago' => 2, 'type' => 'afternoon', 'status' => 'completed'],
            ['email' => 'nurse@carenest.test', 'days_ago' => 1, 'type' => 'morning', 'status' => 'completed'],

            // Today's shifts
            ['email' => 'nurse@carenest.test', 'days_ago' => 0, 'type' => 'afternoon', 'status' => 'scheduled'],
            ['email' => 'caregiver@carenest.test', 'days_ago' => 0, 'type' => 'morning', 'status' => 'in_progress'],
            ['email' => 'supervisor@carenest.test', 'days_ago' => 0, 'type' => 'morning', 'status' => 'in_progress'],

            // Future shifts (scheduled)
            ['email' => 'nurse@carenest.test', 'days_ahead' => 1, 'type' => 'morning', 'status' => 'scheduled'],
            ['email' => 'caregiver@carenest.test', 'days_ahead' => 1, 'type' => 'afternoon', 'status' => 'scheduled'],
            ['email' => 'supervisor@carenest.test', 'days_ahead' => 2, 'type' => 'morning', 'status' => 'scheduled'],
            ['email' => 'nurse@carenest.test', 'days_ahead' => 3, 'type' => 'night', 'status' => 'scheduled'],
            ['email' => 'caregiver@carenest.test', 'days_ahead' => 3, 'type' => 'morning', 'status' => 'scheduled'],
        ];

        foreach ($shifts as $data) {
            $user = $staffUsers[$data['email']] ?? null;
            if (! $user) {
                continue;
            }

            if (isset($data['days_ago'])) {
                $date = today()->subDays($data['days_ago']);
            } else {
                $date = today()->addDays($data['days_ahead']);
            }

            $times = $shiftTemplates[$data['type']];

            Shift::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'shift_date' => $date->format('Y-m-d'),
                    'type' => $data['type'],
                ],
                [
                    'start_time' => $times['start'],
                    'end_time' => $times['end'],
                    'status' => $data['status'],
                    'notes' => $data['notes'] ?? null,
                    'created_by' => $manager?->id,
                ]
            );
        }
    }
}
