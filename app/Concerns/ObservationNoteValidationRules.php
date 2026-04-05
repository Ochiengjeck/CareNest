<?php

namespace App\Concerns;

trait ObservationNoteValidationRules
{
    public function observationNoteRules(): array
    {
        return [
            'observed_at'      => ['required', 'date'],
            'observation_type' => ['required', 'string', 'in:every_15_min,every_30_min,one_to_one,continuous'],
            'behavior'         => ['nullable', 'string'],
            'location'         => ['nullable', 'string', 'max:255'],
            'mood_affect'      => ['nullable', 'string', 'max:255'],
            'safety_status'    => ['required', 'string', 'in:safe,at_risk,unsafe'],
            'notes'            => ['nullable', 'string'],
        ];
    }
}
