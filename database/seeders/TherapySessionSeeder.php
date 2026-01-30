<?php

namespace Database\Seeders;

use App\Models\TherapistAssignment;
use App\Models\TherapySession;
use Illuminate\Database\Seeder;

class TherapySessionSeeder extends Seeder
{
    public function run(): void
    {
        $assignments = TherapistAssignment::active()->with(['therapist', 'resident'])->get();

        if ($assignments->isEmpty()) {
            return;
        }

        $serviceTypes = ['individual', 'group', 'intake_assessment', 'treatment_planning'];
        $challenges = ['substance_use', 'mental_health', 'physical_health', 'psychosocial_family'];

        $sessionTemplates = [
            [
                'topic' => 'DBT-Informed - Sitting With Discomfort Safely',
                'interventions' => 'Staff facilitated a DBT-informed session on learning to tolerate emotional discomfort without impulsively reacting or avoiding the experience. Client was guided through grounding exercises and discussed ways to remain safe and self-aware during distress.',
                'progress_notes' => 'Client acknowledged that their instinct is to "escape" uncomfortable emotions, but noted they are beginning to recognize that distress doesn\'t always require immediate relief. They practiced a self-soothing technique during the session and reported feeling calmer. Staff observed use of grounding techniques and praised the client for staying present despite visible anxiety.',
                'client_plan' => 'Staff will continue reinforcing client\'s capacity to sit with distress safely, providing reminders to use DBT distress tolerance skills during moments of emotional difficulty.',
            ],
            [
                'topic' => 'Cognitive Behavioral Therapy - Identifying Thought Patterns',
                'interventions' => 'Facilitated CBT session focused on identifying automatic negative thoughts and cognitive distortions. Used thought records to track thinking patterns and introduced cognitive restructuring techniques.',
                'progress_notes' => 'Client actively engaged in identifying negative thought patterns. Successfully completed a thought record exercise and identified "all-or-nothing thinking" as a common pattern. Expressed interest in continuing to challenge unhelpful thoughts.',
                'client_plan' => 'Continue CBT work with focus on challenging cognitive distortions. Assign daily thought record homework to practice between sessions.',
            ],
            [
                'topic' => 'Mindfulness and Stress Reduction',
                'interventions' => 'Guided mindfulness meditation session focusing on breath awareness and body scan techniques. Discussed the role of mindfulness in managing anxiety and stress responses.',
                'progress_notes' => 'Client participated in 15-minute guided meditation. Initially reported difficulty focusing but showed improvement by end of session. Expressed that the body scan helped them notice tension they were unaware of.',
                'client_plan' => 'Introduce short daily mindfulness practice. Provide audio resources for home practice. Review progress and adjust techniques as needed in next session.',
            ],
            [
                'topic' => 'Coping Skills Development',
                'interventions' => 'Worked on building healthy coping skills repertoire. Reviewed current coping mechanisms and identified which are helpful vs. harmful. Introduced new adaptive strategies including journaling and physical activity.',
                'progress_notes' => 'Client openly discussed current coping strategies, acknowledging some are maladaptive. Showed enthusiasm for trying journaling as a new outlet. Committed to walking daily as a healthy coping mechanism.',
                'client_plan' => 'Support implementation of new coping strategies. Check in on journaling practice and physical activity. Explore additional coping skills based on client preferences.',
            ],
        ];

        foreach ($assignments as $assignment) {
            // Create 2-5 sessions per assignment
            $sessionCount = rand(2, 5);

            for ($i = 0; $i < $sessionCount; $i++) {
                $template = $sessionTemplates[$i % count($sessionTemplates)];
                $sessionDate = now()->subDays(rand(1, 30));
                $startHour = rand(8, 15);
                $isCompleted = rand(0, 10) > 2; // 80% completed

                TherapySession::firstOrCreate(
                    [
                        'therapist_id' => $assignment->therapist_id,
                        'resident_id' => $assignment->resident_id,
                        'session_date' => $sessionDate->format('Y-m-d'),
                        'session_topic' => $template['topic'],
                    ],
                    [
                        'start_time' => sprintf('%02d:00', $startHour),
                        'end_time' => sprintf('%02d:00', $startHour + 1),
                        'service_type' => $serviceTypes[array_rand($serviceTypes)],
                        'challenge_index' => $challenges[array_rand($challenges)],
                        'interventions' => $isCompleted ? $template['interventions'] : null,
                        'progress_notes' => $isCompleted ? $template['progress_notes'] : null,
                        'client_plan' => $isCompleted ? $template['client_plan'] : null,
                        'status' => $isCompleted ? 'completed' : 'scheduled',
                        'notes' => null,
                        'created_by' => $assignment->therapist_id,
                    ]
                );
            }

            // Add one upcoming scheduled session
            TherapySession::firstOrCreate(
                [
                    'therapist_id' => $assignment->therapist_id,
                    'resident_id' => $assignment->resident_id,
                    'session_date' => now()->addDays(rand(1, 7))->format('Y-m-d'),
                ],
                [
                    'start_time' => sprintf('%02d:00', rand(9, 14)),
                    'end_time' => sprintf('%02d:00', rand(10, 15)),
                    'service_type' => 'individual',
                    'challenge_index' => $challenges[array_rand($challenges)],
                    'session_topic' => 'Follow-up Session - Progress Review',
                    'status' => 'scheduled',
                    'created_by' => $assignment->therapist_id,
                ]
            );
        }
    }
}
