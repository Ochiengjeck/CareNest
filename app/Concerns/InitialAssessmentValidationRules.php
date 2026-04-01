<?php

namespace App\Concerns;

trait InitialAssessmentValidationRules
{
    protected function initialAssessmentRules(): array
    {
        return [
            // Section 1
            'assessment_date'    => ['nullable', 'date'],
            'assessment_time'    => ['nullable', 'string', 'max:50'],
            'referral_source'    => ['nullable', 'string', 'max:255'],
            'primary_language'   => ['nullable', 'string', 'max:100'],
            'assessor_name'      => ['nullable', 'string', 'max:255'],
            'court_ordered'      => ['nullable', 'boolean'],

            // Section 2
            'marital_status'     => ['nullable', 'string', 'max:100'],
            'employment_status'  => ['nullable', 'string', 'max:100'],
            'education_level'    => ['nullable', 'string', 'max:100'],
            'living_situation'   => ['nullable', 'string', 'max:255'],
            'veteran_status'     => ['nullable', 'boolean'],

            // Section 3
            'chief_complaint'     => ['nullable', 'string', 'max:5000'],
            'presenting_problem'  => ['nullable', 'string', 'max:10000'],
            'duration_of_problem' => ['nullable', 'string', 'max:255'],
            'previous_treatments' => ['nullable', 'string', 'max:5000'],
            'goals_for_treatment' => ['nullable', 'string', 'max:5000'],

            // Section 4
            'mental_status'               => ['nullable', 'array'],
            'mental_status.*.selected'    => ['nullable', 'array'],
            'mental_status.*.selected.*'  => ['nullable', 'string', 'max:100'],
            'mental_status.*.other'       => ['nullable', 'string', 'max:500'],

            // Section 5
            'substance_use'                  => ['nullable', 'array'],
            'substance_use.*.substance'      => ['nullable', 'string', 'max:100'],
            'substance_use.*.primary'        => ['nullable', 'boolean'],
            'substance_use.*.age_first_use'  => ['nullable', 'string', 'max:20'],
            'substance_use.*.current_use'    => ['nullable', 'boolean'],
            'substance_use.*.last_use_date'  => ['nullable', 'string', 'max:50'],
            'substance_use.*.frequency'      => ['nullable', 'string', 'max:100'],
            'substance_use.*.route'          => ['nullable', 'string', 'max:100'],
            'substance_use.*.days_abstinent' => ['nullable', 'string', 'max:20'],

            // Section 6
            'current_medications'  => ['nullable', 'string', 'max:5000'],
            'medical_conditions'   => ['nullable', 'string', 'max:5000'],
            'medication_allergies' => ['nullable', 'string', 'max:2000'],
            'other_allergies'      => ['nullable', 'string', 'max:2000'],
            'hospitalizations'     => ['nullable', 'string', 'max:5000'],

            // Section 7
            'psychiatric_diagnoses'        => ['nullable', 'string', 'max:5000'],
            'psychiatric_hospitalizations' => ['nullable', 'string', 'max:5000'],
            'psychiatric_medications'      => ['nullable', 'string', 'max:5000'],
            'psych_provider_name'          => ['nullable', 'string', 'max:255'],
            'psych_provider_phone'         => ['nullable', 'string', 'max:50'],

            // Section 8
            'legal_status'            => ['nullable', 'string', 'max:255'],
            'legal_history'           => ['nullable', 'string', 'max:5000'],
            'employment_history'      => ['nullable', 'string', 'max:5000'],
            'family_history'          => ['nullable', 'string', 'max:5000'],
            'trauma_history'          => ['nullable', 'string', 'max:5000'],
            'social_support'          => ['nullable', 'string', 'max:5000'],
            'cultural_considerations' => ['nullable', 'string', 'max:5000'],

            // Section 9
            'suicidal_ideation'  => ['nullable', 'string', 'in:none,passive,active'],
            'suicide_plan'       => ['nullable', 'boolean'],
            'suicide_history'    => ['nullable', 'string', 'max:5000'],
            'homicidal_ideation' => ['nullable', 'string', 'in:none,passive,active'],
            'self_harm_history'  => ['nullable', 'string', 'max:5000'],
            'risk_level'         => ['nullable', 'string', 'in:low,moderate,high,imminent'],

            // Section 10
            'clinical_summary'    => ['nullable', 'string', 'max:10000'],
            'primary_diagnosis'   => ['nullable', 'string', 'max:500'],
            'secondary_diagnosis' => ['nullable', 'string', 'max:500'],
            'asam_level'          => ['nullable', 'string', 'max:100'],
            'level_of_care'       => ['nullable', 'string', 'max:255'],
            'treatment_goals'     => ['nullable', 'string', 'max:5000'],
            'recommendations'     => ['nullable', 'string', 'max:5000'],

            // Signature
            'signers'      => ['nullable', 'array'],
            'signers.*'    => ['integer', 'exists:users,id'],
            'signature_id' => ['nullable', 'integer', 'exists:signatures,id'],
        ];
    }
}
