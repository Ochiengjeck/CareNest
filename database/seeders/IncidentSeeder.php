<?php

namespace Database\Seeders;

use App\Models\Incident;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Database\Seeder;

class IncidentSeeder extends Seeder
{
    public function run(): void
    {
        $reporters = User::role(['nurse', 'caregiver'])->get();
        $reviewers = User::role(['nurse', 'care_home_manager'])->get();

        if ($reporters->isEmpty()) {
            return;
        }

        $activeResidents = Resident::active()->get();

        $incidents = [
            [
                'title' => 'Fall in bathroom - no injury',
                'type' => 'fall',
                'severity' => 'minor',
                'location' => 'Room 102 - Bathroom',
                'description' => 'Resident found on bathroom floor after attempting to stand from toilet unassisted. Resident was alert and oriented. No visible injuries. Resident denied pain.',
                'immediate_actions' => 'Assisted resident back to wheelchair. Completed full body check - no injuries found. Vital signs checked and within normal limits. Resident reassured and reminded to use call bell for assistance.',
                'witnesses' => 'Caregiver Mary Akinyi',
                'outcome' => 'No injury sustained. Care plan updated to include additional bathroom supervision.',
                'status' => 'resolved',
                'resident_index' => 1,
            ],
            [
                'title' => 'Medication administered at wrong time',
                'type' => 'medication_error',
                'severity' => 'moderate',
                'location' => 'Room 101',
                'description' => 'Morning medication round completed 2 hours late due to staffing issues. Resident received 10am medications at 12pm. Affected medications include Metformin and Lisinopril.',
                'immediate_actions' => 'Administered medications immediately upon discovery. Monitored resident for any adverse effects. Blood sugar and blood pressure checked - both within acceptable range. Doctor informed.',
                'witnesses' => 'Nurse on duty',
                'outcome' => 'No adverse effects observed. Staffing review initiated to prevent recurrence.',
                'follow_up_actions' => 'Review medication round scheduling. Ensure adequate staffing for all medication rounds.',
                'status' => 'closed',
                'resident_index' => 0,
            ],
            [
                'title' => 'Aggressive behavior during personal care',
                'type' => 'behavioral',
                'severity' => 'moderate',
                'location' => 'Room 106',
                'description' => 'Resident became agitated and physically resistant during morning personal care routine. Resident was shouting and attempted to push caregiver away. This behavior is new and not previously documented.',
                'immediate_actions' => 'Staff stepped back and gave resident space. Spoke calmly and reassuringly. Returned 20 minutes later and resident was calmer. Personal care completed with two staff members present.',
                'witnesses' => 'Caregiver on duty, Room neighbor heard shouting',
                'follow_up_actions' => 'GP referral for behavioral assessment. Review personal care approach. Consider if change in routine or environment may be contributing factor.',
                'status' => 'under_investigation',
                'resident_index' => 5,
            ],
            [
                'title' => 'Wheelchair brake failure',
                'type' => 'equipment_failure',
                'severity' => 'major',
                'location' => 'Main corridor',
                'description' => 'Wheelchair brakes failed to engage when resident attempted to stand using walking frame. Wheelchair rolled backward causing resident to lose balance. Staff caught resident before fall.',
                'immediate_actions' => 'Resident assessed - no injury. Wheelchair immediately taken out of service and labeled as faulty. Replacement wheelchair provided. Maintenance notified.',
                'witnesses' => 'Two caregivers present in corridor',
                'outcome' => 'No injury to resident. Wheelchair sent for repair.',
                'follow_up_actions' => 'All wheelchairs to be inspected within 48 hours. Implement monthly wheelchair safety checks.',
                'status' => 'resolved',
                'resident_index' => 3,
            ],
            [
                'title' => 'Skin tear on forearm',
                'type' => 'injury',
                'severity' => 'minor',
                'location' => 'Dining room',
                'description' => 'Resident caught forearm on edge of dining table while reaching for water glass. Small skin tear approximately 2cm on right forearm. Minimal bleeding.',
                'immediate_actions' => 'Wound cleaned with saline. Steri-strips applied. Non-adherent dressing applied. Wound documented in care notes with photograph.',
                'witnesses' => 'Dining room staff',
                'outcome' => 'Minor skin tear treated. Healing well at 3-day review.',
                'status' => 'closed',
                'resident_index' => 6,
            ],
            [
                'title' => 'Fall from bed during night',
                'type' => 'fall',
                'severity' => 'moderate',
                'location' => 'Room 104',
                'description' => 'Night staff found resident on floor beside bed during 2am check. Bed rails were in place but resident appears to have climbed over. Resident confused and disoriented. Bruise noted on left hip.',
                'immediate_actions' => 'Neurological observations completed. Ice applied to bruised area. Resident assisted back to bed with lower bed height setting. Doctor called - advised X-ray in morning if pain persists. 15-minute observations initiated.',
                'witnesses' => 'Night nurse discovered resident',
                'follow_up_actions' => 'X-ray ordered for left hip. Falls risk assessment to be updated. Consider sensor mat or low-profile bed. Review night-time care plan.',
                'status' => 'open',
                'resident_index' => 3,
            ],
            [
                'title' => 'Near miss - wrong medication prepared',
                'type' => 'medication_error',
                'severity' => 'major',
                'location' => 'Medication room',
                'description' => 'During evening medication round, nurse noticed that medications prepared for Room 108 resident were actually prescribed for Room 110 resident. Error caught before administration. Both residents have similar surnames.',
                'immediate_actions' => 'Medications discarded and correctly prepared. Double-check system implemented for remainder of shift. Both residents received correct medications. Incident reported to manager on call.',
                'witnesses' => 'Senior nurse on duty',
                'outcome' => 'No harm to any resident. Error caught before administration.',
                'follow_up_actions' => 'Implement two-person verification for medication preparation. Review medication labeling system. Staff refresher training on medication safety.',
                'status' => 'under_investigation',
                'resident_index' => null,
            ],
            [
                'title' => 'Resident found wandering outside at night',
                'type' => 'behavioral',
                'severity' => 'critical',
                'location' => 'Garden area',
                'description' => 'Resident with moderate dementia found in the garden area at 3:15am during routine security check. Resident was in nightwear and appeared confused about location. Temperature outside was 12°C. Exit door to garden was not properly secured.',
                'immediate_actions' => 'Resident immediately brought inside and warmed. Vital signs checked - temperature slightly low at 35.8°C. Warm drinks provided. Doctor contacted for advice. Extra blankets applied. Continuous observation for remainder of night.',
                'witnesses' => 'Security guard, Night nurse',
                'outcome' => 'Resident recovered with no lasting effects. Temperature returned to normal within 2 hours.',
                'follow_up_actions' => "Urgent review of all exit door security. Install door alarms on all garden exits. Review night-time staffing levels. Update resident's risk assessment and care plan. Family informed.",
                'status' => 'open',
                'resident_index' => 5,
            ],
        ];

        foreach ($incidents as $data) {
            $residentId = null;
            if ($data['resident_index'] !== null && isset($activeResidents[$data['resident_index']])) {
                $residentId = $activeResidents[$data['resident_index']]->id;
            }

            $reporter = $reporters->random();
            $reviewer = $reviewers->random();
            $occurredAt = now()->subDays(rand(1, 14))->subHours(rand(0, 12));

            $isReviewed = in_array($data['status'], ['resolved', 'closed']);

            Incident::firstOrCreate(
                ['title' => $data['title']],
                [
                    'resident_id' => $residentId,
                    'type' => $data['type'],
                    'severity' => $data['severity'],
                    'occurred_at' => $occurredAt,
                    'location' => $data['location'],
                    'description' => $data['description'],
                    'immediate_actions' => $data['immediate_actions'],
                    'witnesses' => $data['witnesses'] ?? null,
                    'outcome' => $data['outcome'] ?? null,
                    'follow_up_actions' => $data['follow_up_actions'] ?? null,
                    'status' => $data['status'],
                    'reported_by' => $reporter->id,
                    'reviewed_by' => $isReviewed ? $reviewer->id : null,
                    'reviewed_at' => $isReviewed ? $occurredAt->copy()->addDays(rand(1, 3)) : null,
                    'notes' => null,
                ]
            );
        }
    }
}
