<?php

namespace App\Concerns;

trait NursingAssessmentValidationRules
{
    public function nursingAssessmentRules(): array
    {
        return [
            'assessment_date'              => ['required', 'date'],
            'safety_screening'             => ['nullable', 'array'],
            'safety_screening.suicidal_ideation'  => ['nullable', 'string', 'in:none,passive,active'],
            'safety_screening.homicidal_ideation' => ['nullable', 'string', 'in:none,passive,active'],
            'safety_screening.protective_factors' => ['nullable', 'string'],
            'substance_use_check'          => ['nullable', 'array'],
            'physical_condition'           => ['nullable', 'string'],
            'nursing_intake_note'          => ['nullable', 'string'],
            'risk_level'                   => ['required', 'string', 'in:low,moderate,high,imminent'],
            'risk_assessment_notes'        => ['nullable', 'string'],
        ];
    }
}
