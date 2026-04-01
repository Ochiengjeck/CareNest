<?php

namespace App\Concerns;

trait CarePlanValidationRules
{
    protected function carePlanRules(?int $residentRequired = null): array
    {
        return [
            'resident_id' => $residentRequired ? ['required', 'exists:residents,id'] : ['nullable'],
            'title'       => ['required', 'string', 'max:255'],
            'type'        => ['required', 'string', 'in:general,nutrition,mobility,mental_health,personal_care,medication,social'],
            'status'      => ['required', 'string', 'in:active,draft,archived,under_review'],
            'start_date'  => ['required', 'date'],
            'review_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string', 'max:10000'],
            'notes'       => ['nullable', 'string', 'max:10000'],

            // Structured goals
            'planGoals'                           => ['nullable', 'array'],
            'planGoals.*.problem_description'     => ['required', 'string', 'max:5000'],
            'planGoals.*.case_manager_actions'    => ['nullable', 'string', 'max:5000'],
            'planGoals.*.client_actions'          => ['nullable', 'string', 'max:5000'],
            'planGoals.*.progress_status'         => ['required', 'string', 'in:not_started,making_progress,achieved,not_achieved'],
            'planGoals.*.target_date'             => ['nullable', 'date'],

            // Recovery team
            'recoveryTeam'                        => ['nullable', 'array'],
            'recoveryTeam.*.name'                 => ['nullable', 'string', 'max:255'],
            'recoveryTeam.*.title'                => ['nullable', 'string', 'max:255'],
            'recoveryTeam.*.date'                 => ['nullable', 'date'],
        ];
    }
}
