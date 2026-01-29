<?php

namespace App\Concerns;

trait CarePlanValidationRules
{
    protected function carePlanRules(?int $residentRequired = null): array
    {
        return [
            'resident_id' => $residentRequired ? ['required', 'exists:residents,id'] : ['nullable'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:general,nutrition,mobility,mental_health,personal_care,medication,social'],
            'status' => ['required', 'string', 'in:active,draft,archived,under_review'],
            'start_date' => ['required', 'date'],
            'review_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string', 'max:10000'],
            'goals' => ['nullable', 'string', 'max:10000'],
            'interventions' => ['nullable', 'string', 'max:10000'],
            'notes' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
