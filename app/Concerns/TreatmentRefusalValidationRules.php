<?php

namespace App\Concerns;

trait TreatmentRefusalValidationRules
{
    protected function treatmentRefusalRules(): array
    {
        return [
            'refusal_date'        => ['required', 'date'],
            'illness_description' => ['nullable', 'string', 'max:10000'],
            'signers'             => ['nullable', 'array'],
            'signers.*'           => ['integer', 'exists:users,id'],
            'signature_id'        => ['nullable', 'integer', 'exists:signatures,id'],
        ];
    }
}
