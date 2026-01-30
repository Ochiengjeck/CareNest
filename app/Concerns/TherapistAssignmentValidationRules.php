<?php

namespace App\Concerns;

trait TherapistAssignmentValidationRules
{
    protected function therapistAssignmentRules(): array
    {
        return [
            'therapist_id' => ['required', 'exists:users,id'],
            'resident_id' => ['required', 'exists:residents,id'],
            'assigned_date' => ['required', 'date'],
            'status' => ['required', 'in:active,inactive,completed'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
