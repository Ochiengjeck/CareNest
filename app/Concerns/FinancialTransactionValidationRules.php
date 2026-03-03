<?php

namespace App\Concerns;

trait FinancialTransactionValidationRules
{
    protected function financialTransactionRules(): array
    {
        return [
            'diagnosis'             => ['nullable', 'string', 'max:5000'],
            'entries'               => ['required', 'array', 'min:1'],
            'entries.*.date'        => ['required', 'date'],
            'entries.*.deposit'     => ['nullable', 'numeric', 'min:0'],
            'entries.*.money_spent' => ['nullable', 'numeric', 'min:0'],
            'entries.*.description' => ['nullable', 'string', 'max:1000'],
            'signers'               => ['nullable', 'array'],
            'signers.*'             => ['integer', 'exists:users,id'],
            'signature_id'          => ['nullable', 'integer', 'exists:signatures,id'],
        ];
    }
}
