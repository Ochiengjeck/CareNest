<?php

namespace App\Concerns;

trait FaceSheetValidationRules
{
    protected function faceSheetRules(): array
    {
        return [
            'diagnosis'                  => ['nullable', 'string', 'max:5000'],
            'facility_address'           => ['nullable', 'string', 'max:1000'],
            'facility_phone'             => ['nullable', 'string', 'max:50'],
            'place_of_birth'             => ['nullable', 'string', 'max:255'],
            'eye_color'                  => ['nullable', 'string', 'max:100'],
            'race'                       => ['nullable', 'string', 'max:255'],
            'height'                     => ['nullable', 'string', 'max:50'],
            'weight'                     => ['nullable', 'string', 'max:50'],
            'hair_color'                 => ['nullable', 'string', 'max:100'],
            'identifiable_marks'         => ['nullable', 'string', 'max:1000'],
            'primary_language'           => ['nullable', 'string', 'max:100'],
            'court_ordered'              => ['nullable', 'boolean'],
            'family_emergency_contact'   => ['nullable', 'string', 'max:2000'],
            'facility_emergency_contact' => ['nullable', 'string', 'max:500'],
            'medication_allergies'       => ['nullable', 'string', 'max:2000'],
            'other_allergies'            => ['nullable', 'string', 'max:2000'],
            'pcp_name'                   => ['nullable', 'string', 'max:255'],
            'pcp_phone'                  => ['nullable', 'string', 'max:50'],
            'pcp_address'                => ['nullable', 'string', 'max:1000'],
            'specialist_1_type'          => ['nullable', 'string', 'max:255'],
            'specialist_1_name'          => ['nullable', 'string', 'max:255'],
            'specialist_1_phone'         => ['nullable', 'string', 'max:50'],
            'specialist_1_address'       => ['nullable', 'string', 'max:1000'],
            'psych_name'                 => ['nullable', 'string', 'max:255'],
            'psych_phone'                => ['nullable', 'string', 'max:50'],
            'psych_address'              => ['nullable', 'string', 'max:1000'],
            'specialist_2_type'          => ['nullable', 'string', 'max:255'],
            'specialist_2_name'          => ['nullable', 'string', 'max:255'],
            'specialist_2_phone'         => ['nullable', 'string', 'max:50'],
            'specialist_2_address'       => ['nullable', 'string', 'max:1000'],
            'preferred_hospital'         => ['nullable', 'string', 'max:255'],
            'preferred_hospital_phone'   => ['nullable', 'string', 'max:50'],
            'preferred_hospital_address' => ['nullable', 'string', 'max:1000'],
            'health_plan'                => ['nullable', 'string', 'max:255'],
            'health_plan_id'             => ['nullable', 'string', 'max:255'],
            'case_manager_name'          => ['nullable', 'string', 'max:255'],
            'case_manager_phone'         => ['nullable', 'string', 'max:50'],
            'case_manager_email'         => ['nullable', 'email', 'max:255'],
            'ss_rep_payee'               => ['nullable', 'string', 'max:255'],
            'ss_rep_phone'               => ['nullable', 'string', 'max:50'],
            'ss_rep_email'               => ['nullable', 'email', 'max:255'],
            'mental_health_diagnoses'    => ['nullable', 'string', 'max:5000'],
            'medical_diagnoses'          => ['nullable', 'string', 'max:5000'],
            'past_surgeries'             => ['nullable', 'string', 'max:5000'],
            'signers'                    => ['nullable', 'array'],
            'signers.*'                  => ['integer', 'exists:users,id'],
            'signature_id'               => ['nullable', 'integer', 'exists:signatures,id'],
        ];
    }
}
