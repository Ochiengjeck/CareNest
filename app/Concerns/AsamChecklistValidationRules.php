<?php

namespace App\Concerns;

trait AsamChecklistValidationRules
{
    protected function asamChecklistRules(): array
    {
        return [
            'diagnosis'      => ['nullable', 'string', 'max:5000'],
            'discharge_date' => ['nullable', 'date'],
            'dimension_1'    => ['nullable', 'array'],
            'dimension_1.*'  => ['nullable', 'string', 'max:5000'],
            'dimension_2'    => ['nullable', 'array'],
            'dimension_2.*'  => ['nullable', 'string', 'max:5000'],
            'dimension_3'    => ['nullable', 'array'],
            'dimension_3.*'  => ['nullable', 'string', 'max:5000'],
            'dimension_4'    => ['nullable', 'array'],
            'dimension_4.*'  => ['nullable', 'string', 'max:5000'],
            'dimension_5'    => ['nullable', 'array'],
            'dimension_5.*'  => ['nullable', 'string', 'max:5000'],
            'dimension_6'    => ['nullable', 'array'],
            'dimension_6.*'  => ['nullable', 'string', 'max:5000'],
            'asam_score'     => ['nullable', 'string', 'max:5000'],
            'level_of_care'  => ['nullable', 'string', 'max:5000'],
            'residential'    => ['nullable', 'string', 'in:3.1,3.3,3.5'],
            'comment'        => ['nullable', 'string', 'max:10000'],
            'signers'        => ['nullable', 'array'],
            'signers.*'      => ['integer', 'exists:users,id'],
            'signature_id'   => ['nullable', 'integer', 'exists:signatures,id'],
        ];
    }
}
