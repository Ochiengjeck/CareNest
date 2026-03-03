<?php

namespace App\Concerns;

trait MentalStatusExamValidationRules
{
    protected function mentalStatusExamRules(): array
    {
        return [
            'exam_date'          => ['required', 'date'],
            'before_appointment' => ['nullable', 'array'],
            'after_appointment'  => ['nullable', 'array'],
            'signers'            => ['nullable', 'array'],
            'signers.*'          => ['integer', 'exists:users,id'],
            'signature_id'       => ['nullable', 'integer', 'exists:signatures,id'],
        ];
    }
}
