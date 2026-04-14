<?php

namespace App\Concerns;

trait MedicationValidationRules
{
    protected function medicationRules(): array
    {
        return [
            'resident_id' => ['required', 'exists:residents,id'],
            'name' => ['required', 'string', 'max:255'],
            'dosage' => ['required', 'string', 'max:255'],
            'frequency' => ['required', 'string', 'max:255'],
            'administration_times' => ['nullable', 'array', 'max:4'],
            'administration_times.*' => ['required', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'route' => ['required', 'string', 'in:oral,topical,injection,inhalation,sublingual,rectal,other'],
            'prescribed_by' => ['required', 'string', 'max:255'],
            'prescribed_date' => ['required', 'date'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'string', 'in:active,completed,discontinued,on_hold'],
            'instructions' => ['nullable', 'string', 'max:10000'],
            'notes' => ['nullable', 'string', 'max:10000'],
        ];
    }

    protected function medicationLogRules(): array
    {
        return [
            'medication_id' => ['required', 'exists:medications,id'],
            'administered_at' => ['required', 'date'],
            'status' => ['required', 'string', 'in:given,refused,withheld,missed,hospital,home_pass,on_hold,unavailable,discontinued'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'initials' => ['nullable', 'string', 'max:10'],
            'slot_time' => ['nullable', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'raw_signature_data' => ['nullable', 'string'],
            'signature_id' => ['nullable', 'exists:signatures,id'],
        ];
    }
}
