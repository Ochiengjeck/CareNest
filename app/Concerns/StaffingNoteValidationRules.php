<?php

namespace App\Concerns;

trait StaffingNoteValidationRules
{
    protected function staffingNoteRules(): array
    {
        return [
            'note_date'                => ['required', 'date'],
            'begin_time'               => ['nullable', 'date_format:H:i'],
            'end_time'                 => ['nullable', 'date_format:H:i'],
            'participant'              => ['nullable', 'string', 'max:500'],
            'diagnosis'                => ['nullable', 'string', 'max:5000'],
            'presenting_issues'        => ['nullable', 'string', 'max:10000'],
            'conducted_within_30_days' => ['nullable', 'boolean'],
            'treatment_plan_requested' => ['nullable', 'boolean'],
            'step_down_discussed'      => ['nullable', 'boolean'],
            'goals_addressed'          => ['nullable', 'string', 'max:10000'],
            'note_summary'             => ['nullable', 'string', 'max:10000'],
            'barriers'                 => ['nullable', 'string', 'max:10000'],
            'not_conducted_reason'     => ['nullable', 'string', 'max:10000'],
            'recommendations'          => ['nullable', 'string', 'max:10000'],
            'signers'                  => ['nullable', 'array'],
            'signers.*'                => ['integer', 'exists:users,id'],
            'signature_id'             => ['nullable', 'integer', 'exists:signatures,id'],
        ];
    }
}
