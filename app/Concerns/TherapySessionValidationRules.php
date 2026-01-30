<?php

namespace App\Concerns;

trait TherapySessionValidationRules
{
    protected function therapySessionRules(): array
    {
        return [
            'therapist_id' => ['required', 'exists:users,id'],
            'resident_id' => ['required', 'exists:residents,id'],
            'session_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'service_type' => ['required', 'in:individual,group,intake_assessment,crisis,collateral,case_management,treatment_planning,discharge,other'],
            'challenge_index' => ['nullable', 'in:substance_use,mental_health,physical_health,employment_education,financial_housing,legal,psychosocial_family,spirituality'],
            'session_topic' => ['required', 'string', 'max:255'],
            'interventions' => ['nullable', 'string', 'max:5000'],
            'progress_notes' => ['nullable', 'string', 'max:5000'],
            'client_plan' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'in:scheduled,completed,cancelled,no_show'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    protected function therapySessionDocumentRules(): array
    {
        return [
            'interventions' => ['required', 'string', 'max:5000'],
            'progress_notes' => ['required', 'string', 'max:5000'],
            'client_plan' => ['required', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
