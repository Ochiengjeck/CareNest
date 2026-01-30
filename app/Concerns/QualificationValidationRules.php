<?php

namespace App\Concerns;

trait QualificationValidationRules
{
    protected function qualificationRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:license,certification,training,education'],
            'issuing_body' => ['nullable', 'string', 'max:255'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'status' => ['required', 'string', 'in:active,expired,pending_renewal'],
            'notes' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
