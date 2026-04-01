<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InitialAssessment extends Model
{
    protected $fillable = [
        'resident_id',
        // Section 1
        'assessment_date', 'assessment_time', 'referral_source',
        'primary_language', 'assessor_name', 'court_ordered',
        // Section 2
        'marital_status', 'employment_status', 'education_level',
        'living_situation', 'veteran_status',
        // Section 3
        'chief_complaint', 'presenting_problem', 'duration_of_problem',
        'previous_treatments', 'goals_for_treatment',
        // Section 4
        'mental_status',
        // Section 5
        'substance_use',
        // Section 6
        'current_medications', 'medical_conditions', 'medication_allergies',
        'other_allergies', 'hospitalizations',
        // Section 7
        'psychiatric_diagnoses', 'psychiatric_hospitalizations', 'psychiatric_medications',
        'psych_provider_name', 'psych_provider_phone',
        // Section 8
        'legal_status', 'legal_history', 'employment_history', 'family_history',
        'trauma_history', 'social_support', 'cultural_considerations',
        // Section 9
        'suicidal_ideation', 'suicide_plan', 'suicide_history',
        'homicidal_ideation', 'self_harm_history', 'risk_level',
        // Section 10
        'clinical_summary', 'primary_diagnosis', 'secondary_diagnosis',
        'asam_level', 'level_of_care', 'treatment_goals', 'recommendations',
        // Signature
        'signers', 'signature_id', 'raw_signature_data', 'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'assessment_date'    => 'date',
            'court_ordered'      => 'boolean',
            'veteran_status'     => 'boolean',
            'suicide_plan'       => 'boolean',
            'mental_status'      => 'array',
            'substance_use'      => 'array',
            'signers'            => 'array',
            'raw_signature_data' => 'encrypted',
        ];
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function signature(): BelongsTo
    {
        return $this->belongsTo(Signature::class);
    }

    public static function mentalStatusCategories(): array
    {
        return [
            'appearance' => [
                'key'     => 'appearance',
                'label'   => 'Appearance',
                'options' => ['Well Groomed', 'Casually Groomed', 'Disheveled', 'Bizarre', 'Other'],
            ],
            'behavior' => [
                'key'     => 'behavior',
                'label'   => 'Behavior',
                'options' => ['Calm', 'Cooperative', 'Agitated', 'Hostile', 'Restless', 'Other'],
            ],
            'speech' => [
                'key'     => 'speech',
                'label'   => 'Speech',
                'options' => ['Normal Rate/Tone', 'Rapid', 'Slow', 'Pressured', 'Slurred', 'Mute', 'Other'],
            ],
            'orientation' => [
                'key'     => 'orientation',
                'label'   => 'Orientation (oriented to)',
                'options' => ['Person', 'Place', 'Time', 'Situation'],
            ],
            'affect' => [
                'key'     => 'affect',
                'label'   => 'Affect',
                'options' => ['Appropriate', 'Flat', 'Blunted', 'Labile', 'Euphoric', 'Anxious', 'Other'],
            ],
            'mood' => [
                'key'     => 'mood',
                'label'   => 'Mood',
                'options' => ['Euthymic', 'Depressed', 'Anxious', 'Irritable', 'Euphoric', 'Other'],
            ],
            'thought_process' => [
                'key'     => 'thought_process',
                'label'   => 'Thought Process',
                'options' => ['Logical', 'Tangential', 'Circumstantial', 'Flight of Ideas', 'Loose Associations', 'Other'],
            ],
            'thought_content' => [
                'key'     => 'thought_content',
                'label'   => 'Thought Content',
                'options' => ['Normal', 'Suicidal Ideation', 'Homicidal Ideation', 'Paranoid', 'Obsessions', 'Other'],
            ],
            'perceptions' => [
                'key'     => 'perceptions',
                'label'   => 'Perceptions',
                'options' => ['No Hallucinations', 'Auditory', 'Visual', 'Tactile', 'Olfactory', 'Other'],
            ],
            'memory' => [
                'key'     => 'memory',
                'label'   => 'Memory',
                'options' => ['Intact', 'Impaired Short-term', 'Impaired Long-term', 'Other'],
            ],
            'insight' => [
                'key'     => 'insight',
                'label'   => 'Insight',
                'options' => ['Good', 'Fair', 'Poor', 'Absent'],
            ],
            'judgment' => [
                'key'     => 'judgment',
                'label'   => 'Judgment',
                'options' => ['Good', 'Fair', 'Poor', 'Absent'],
            ],
            'impulse_control' => [
                'key'     => 'impulse_control',
                'label'   => 'Impulse Control',
                'options' => ['Good', 'Fair', 'Poor', 'Absent'],
            ],
        ];
    }

    public static function substanceList(): array
    {
        return [
            'Alcohol',
            'Cannabis',
            'Cocaine/Crack',
            'Heroin',
            'Methamphetamine',
            'Opioids (Rx)',
            'Benzodiazepines',
            'Other',
        ];
    }
}
