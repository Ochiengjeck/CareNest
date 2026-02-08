<?php

namespace Database\Seeders;

use App\Models\MentorshipTopic;
use App\Models\User;
use Illuminate\Database\Seeder;

class MentorshipSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::whereHas('roles', fn ($q) => $q->where('name', 'system_admin'))->first();

        if (! $admin) {
            return;
        }

        // February 2026 Counseling Topics from reference image
        $topics = [
            // Feb 2, Sun
            ['date' => '2026-02-02', 'day' => 'Sun', 'time' => '10:00', 'title' => 'DBT-INFORMED– Mindfulness of Thoughts', 'category' => 'Mental Health'],
            ['date' => '2026-02-02', 'day' => 'Sun', 'time' => '14:00', 'title' => 'Coping With Emotional Triggers', 'category' => 'Mental Health'],
            ['date' => '2026-02-02', 'day' => 'Sun', 'time' => '18:00', 'title' => 'Setting Personal Intentions', 'category' => 'Spirituality'],

            // Feb 3, Mon
            ['date' => '2026-02-03', 'day' => 'Mon', 'time' => '10:00', 'title' => 'DBT-INFORMED– Wise Mind vs. Emotion Mind', 'category' => 'Mental Health'],
            ['date' => '2026-02-03', 'day' => 'Mon', 'time' => '14:00', 'title' => 'Managing Stress Reactions', 'category' => 'Mental Health'],
            ['date' => '2026-02-03', 'day' => 'Mon', 'time' => '18:00', 'title' => 'Building Daily Structure', 'category' => 'Employment/Education'],

            // Feb 4, Tue
            ['date' => '2026-02-04', 'day' => 'Tue', 'time' => '10:00', 'title' => 'DBT-INFORMED– Observing Emotions Without Judgment', 'category' => 'Mental Health'],
            ['date' => '2026-02-04', 'day' => 'Tue', 'time' => '14:00', 'title' => 'Coping With Anxiety', 'category' => 'Mental Health'],
            ['date' => '2026-02-04', 'day' => 'Tue', 'time' => '18:00', 'title' => 'Time Management Basics', 'category' => 'Employment/Education'],

            // Feb 5, Wed
            ['date' => '2026-02-05', 'day' => 'Wed', 'time' => '10:00', 'title' => 'DBT-INFORMED– Distress Tolerance During Cravings', 'category' => 'Substance Use Disorder'],
            ['date' => '2026-02-05', 'day' => 'Wed', 'time' => '14:00', 'title' => 'Urge Surfing Techniques', 'category' => 'Substance Use Disorder'],
            ['date' => '2026-02-05', 'day' => 'Wed', 'time' => '18:00', 'title' => 'Responsibility & Follow-Through', 'category' => 'Employment/Education'],

            // Feb 6, Thu
            ['date' => '2026-02-06', 'day' => 'Thu', 'time' => '10:00', 'title' => 'DBT-INFORMED– Radical Acceptance in Daily Life', 'category' => 'Mental Health'],
            ['date' => '2026-02-06', 'day' => 'Thu', 'time' => '14:00', 'title' => 'Coping With Frustration', 'category' => 'Mental Health'],
            ['date' => '2026-02-06', 'day' => 'Thu', 'time' => '18:00', 'title' => 'Healthy Routine Building', 'category' => 'Physical Health'],

            // Feb 7, Fri
            ['date' => '2026-02-07', 'day' => 'Fri', 'time' => '10:00', 'title' => 'DBT-INFORMED– Emotional Regulation Basics', 'category' => 'Mental Health'],
            ['date' => '2026-02-07', 'day' => 'Fri', 'time' => '14:00', 'title' => 'Managing Negative Self-Talk', 'category' => 'Mental Health'],
            ['date' => '2026-02-07', 'day' => 'Fri', 'time' => '18:00', 'title' => 'Budgeting Awareness', 'category' => 'Financial/Housing'],

            // Feb 8, Sat
            ['date' => '2026-02-08', 'day' => 'Sat', 'time' => '10:00', 'title' => 'DBT-INFORMED– Validation of Self & Others', 'category' => 'Psycho-Social/Family'],
            ['date' => '2026-02-08', 'day' => 'Sat', 'time' => '14:00', 'title' => 'Coping With Anger Safely', 'category' => 'Mental Health'],
            ['date' => '2026-02-08', 'day' => 'Sat', 'time' => '18:00', 'title' => 'Communication Skills', 'category' => 'Psycho-Social/Family'],

            // Feb 9, Sun
            ['date' => '2026-02-09', 'day' => 'Sun', 'time' => '10:00', 'title' => 'DBT-INFORMED– Mindful Awareness of the Body', 'category' => 'Physical Health'],
            ['date' => '2026-02-09', 'day' => 'Sun', 'time' => '14:00', 'title' => 'Grounding Skills Practice', 'category' => 'Mental Health'],
            ['date' => '2026-02-09', 'day' => 'Sun', 'time' => '18:00', 'title' => 'Personal Hygiene & Self-Care', 'category' => 'Physical Health'],

            // Feb 10, Mon
            ['date' => '2026-02-10', 'day' => 'Mon', 'time' => '10:00', 'title' => 'DBT-INFORMED– Opposite Action for Low Motivation', 'category' => 'Mental Health'],
            ['date' => '2026-02-10', 'day' => 'Mon', 'time' => '14:00', 'title' => 'Coping With Depression', 'category' => 'Mental Health'],
            ['date' => '2026-02-10', 'day' => 'Mon', 'time' => '18:00', 'title' => 'Daily Goal Setting', 'category' => 'Employment/Education'],

            // Feb 11, Tue
            ['date' => '2026-02-11', 'day' => 'Tue', 'time' => '10:00', 'title' => 'DBT-INFORMED– Understanding Emotional Health', 'category' => 'Mental Health'],
            ['date' => '2026-02-11', 'day' => 'Tue', 'time' => '14:00', 'title' => 'Stress-Reduction Techniques', 'category' => 'Mental Health'],
            ['date' => '2026-02-11', 'day' => 'Tue', 'time' => '18:00', 'title' => 'Healthy Use of Free Time', 'category' => 'Psycho-Social/Family'],
        ];

        foreach ($topics as $topicData) {
            MentorshipTopic::firstOrCreate(
                [
                    'topic_date' => $topicData['date'],
                    'time_slot' => $topicData['time'],
                ],
                [
                    'day_of_week' => $topicData['day'],
                    'title' => $topicData['title'],
                    'category' => $topicData['category'],
                    'is_published' => true,
                    'created_by' => $admin->id,
                ]
            );
        }
    }
}
