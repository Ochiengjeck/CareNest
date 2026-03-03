<?php

namespace App\Concerns;

trait BhpProgressNoteValidationRules
{
    protected function bhpProgressNoteRules(): array
    {
        return [
            'diagnosis'                => ['nullable', 'string', 'max:5000'],
            'discharge_date'           => ['nullable', 'date'],
            'progress_note'            => ['nullable', 'string', 'max:10000'],
            'treatment_goals_progress' => ['nullable', 'string', 'max:10000'],
            'sobriety_physical_health' => ['nullable', 'string', 'max:10000'],
            'cognitive_emotional'      => ['nullable', 'string', 'max:10000'],
            'therapeutic_support'      => ['nullable', 'string', 'max:10000'],
            'progress_towards_goals'   => ['nullable', 'string', 'max:10000'],
            'barriers'                 => ['nullable', 'string', 'max:10000'],
            'summary_continued_stay'   => ['nullable', 'string', 'max:10000'],
            'bhp_name_credential'      => ['nullable', 'string', 'max:500'],
            'signers'                  => ['nullable', 'array'],
            'signers.*'                => ['integer', 'exists:users,id'],
            'signature_id'             => ['nullable', 'integer', 'exists:signatures,id'],
        ];
    }
}
