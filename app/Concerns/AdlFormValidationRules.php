<?php

namespace App\Concerns;

trait AdlFormValidationRules
{
    protected function adlFormRules(): array
    {
        $levels = 'no_assistance,some_assistance,complete_assistance,not_applicable,refused';

        return [
            'form_date'          => ['required', 'date'],
            'entries'            => ['nullable', 'array'],
            'entries.*.level'    => ['nullable', 'string', 'in:' . $levels],
            'entries.*.initials' => ['nullable', 'string', 'max:10'],
            'signature_id'       => ['nullable', 'integer', 'exists:signatures,id'],
        ];
    }
}
