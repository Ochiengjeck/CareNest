<?php

namespace Database\Seeders;

use App\Models\CarePlan;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Database\Seeder;

class CarePlanSeeder extends Seeder
{
    public function run(): void
    {
        $createdBy = User::role('nurse')->first()?->id
            ?? User::role('care_home_manager')->first()?->id;

        $activeResidents = Resident::active()->get();

        $carePlanTemplates = [
            [
                'title' => 'General Daily Care Plan',
                'type' => 'general',
                'status' => 'active',
                'description' => 'Comprehensive daily care plan covering personal hygiene, grooming, and general well-being monitoring.',
                'goals' => "- Maintain personal hygiene and dignity\n- Monitor daily mood and well-being\n- Ensure comfortable and safe environment\n- Promote social interaction",
                'interventions' => "- Assist with morning and evening hygiene routine\n- Regular comfort checks every 2 hours\n- Encourage participation in group activities\n- Document daily observations",
            ],
            [
                'title' => 'Nutrition & Hydration Plan',
                'type' => 'nutrition',
                'status' => 'active',
                'description' => 'Personalized nutrition plan ensuring adequate dietary intake and proper hydration throughout the day.',
                'goals' => "- Maintain healthy weight within target range\n- Ensure minimum 1.5L fluid intake daily\n- Meet nutritional requirements per dietitian recommendations\n- Monitor and manage any swallowing difficulties",
                'interventions' => "- Provide meals according to dietary plan\n- Offer fluids every 2 hours\n- Record food and fluid intake at each meal\n- Weekly weight monitoring\n- Refer to dietitian if intake drops below 75%",
            ],
            [
                'title' => 'Mobility & Physical Activity Plan',
                'type' => 'mobility',
                'status' => 'active',
                'description' => 'Plan to maintain and improve physical mobility, prevent falls, and promote safe movement.',
                'goals' => "- Maintain current level of mobility\n- Prevent falls and injuries\n- Promote physical activity appropriate to ability\n- Maintain joint flexibility",
                'interventions' => "- Assist with transfers using correct manual handling techniques\n- Supervised walking sessions twice daily\n- Chair-based exercises 3 times per week\n- Ensure walking aids are within reach\n- Regular physiotherapy review",
            ],
            [
                'title' => 'Mental Health & Well-being Plan',
                'type' => 'mental_health',
                'status' => 'draft',
                'description' => 'Support plan addressing emotional well-being, cognitive stimulation, and social engagement.',
                'goals' => "- Reduce feelings of loneliness and anxiety\n- Maintain cognitive function through stimulating activities\n- Promote positive social interactions\n- Monitor for signs of depression",
                'interventions' => "- Daily one-to-one conversation time\n- Cognitive stimulation activities 3x weekly\n- Encourage participation in group activities\n- Regular mood assessments using validated tools\n- Referral to mental health team if concerns arise",
            ],
            [
                'title' => 'Personal Care & Hygiene Plan',
                'type' => 'personal_care',
                'status' => 'active',
                'description' => 'Detailed personal care plan respecting dignity, preferences, and cultural needs.',
                'goals' => "- Maintain high standard of personal hygiene\n- Respect individual preferences and routines\n- Promote independence where possible\n- Ensure skin integrity",
                'interventions' => "- Morning wash/shower according to preference\n- Oral care after meals\n- Nail care weekly\n- Skin inspection during personal care\n- Clean clothing changes as needed\n- Hair care per resident preference",
            ],
            [
                'title' => 'Medication Management Plan',
                'type' => 'medication',
                'status' => 'under_review',
                'description' => 'Plan for safe and effective medication administration, monitoring side effects, and ensuring compliance.',
                'goals' => "- Ensure timely administration of all prescribed medications\n- Monitor for adverse effects\n- Review medication efficacy\n- Maintain accurate medication records",
                'interventions' => "- Administer medications at prescribed times\n- Monitor vital signs as required for specific medications\n- Report any side effects to prescribing physician\n- Monthly medication review with GP\n- PRN medication usage documentation",
            ],
            [
                'title' => 'Social Engagement Plan',
                'type' => 'social',
                'status' => 'draft',
                'description' => 'Plan to promote meaningful social connections, family involvement, and community participation.',
                'goals' => "- Maintain regular contact with family and friends\n- Participate in at least 3 group activities per week\n- Develop new social connections within the home\n- Engage in meaningful leisure activities",
                'interventions' => "- Facilitate family visits and video calls\n- Encourage attendance at group activities\n- Organize one-to-one companionship\n- Support participation in hobbies and interests\n- Monthly review of social engagement",
            ],
        ];

        foreach ($activeResidents as $index => $resident) {
            // Each resident gets 2-3 care plans from templates
            $offset = $index % count($carePlanTemplates);
            $count = ($index % 3 === 0) ? 3 : 2;

            for ($i = 0; $i < $count; $i++) {
                $templateIndex = ($offset + $i) % count($carePlanTemplates);
                $template = $carePlanTemplates[$templateIndex];

                $startDate = $resident->admission_date->copy()->addDays(rand(1, 14));
                $reviewDate = $startDate->copy()->addMonths(3);

                CarePlan::firstOrCreate(
                    [
                        'resident_id' => $resident->id,
                        'title' => $template['title'],
                    ],
                    [
                        'type' => $template['type'],
                        'status' => $template['status'],
                        'start_date' => $startDate,
                        'review_date' => $reviewDate,
                        'description' => $template['description'],
                        'goals' => $template['goals'],
                        'interventions' => $template['interventions'],
                        'notes' => null,
                        'created_by' => $createdBy,
                    ]
                );
            }
        }
    }
}
