<?php

namespace App\Concerns;

trait IncidentValidationRules
{
    protected function incidentRules(): array
    {
        return [
            'resident_id' => ['nullable', 'exists:residents,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:fall,medication_error,injury,behavioral,equipment_failure,other'],
            'severity' => ['required', 'string', 'in:minor,moderate,major,critical'],
            'occurred_at' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
            'immediate_actions' => ['nullable', 'string', 'max:10000'],
            'witnesses' => ['nullable', 'string', 'max:10000'],
            'outcome' => ['nullable', 'string', 'max:10000'],
            'follow_up_actions' => ['nullable', 'string', 'max:10000'],
            'status' => ['required', 'string', 'in:open,under_investigation,resolved,closed'],
            'notes' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
