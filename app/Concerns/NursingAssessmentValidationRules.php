<?php

namespace App\Concerns;

trait NursingAssessmentValidationRules
{
    public function nursingAssessmentRules(): array
    {
        return [
            'assessment_date'       => ['required', 'date'],
            'suicidal_ideation'     => ['nullable', 'string', 'in:none,passive,active'],
            'homicidal_ideation'    => ['nullable', 'string', 'in:none,passive,active'],
            'protective_factors'    => ['nullable', 'string'],
            'substances_used'       => ['nullable', 'array'],
            'substances_used.*'     => ['string'],
            'substance_frequency'   => ['nullable', 'string'],
            'substance_last_use'    => ['nullable', 'string'],
            'physical_condition'    => ['nullable', 'string'],
            'nursing_intake_note'   => ['nullable', 'string'],
            'risk_level'            => ['required', 'string', 'in:low,moderate,high,imminent'],
            'risk_assessment_notes' => ['nullable', 'string'],
        ];
    }
}
