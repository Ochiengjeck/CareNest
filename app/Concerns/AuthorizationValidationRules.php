<?php

namespace App\Concerns;

trait AuthorizationValidationRules
{
    protected function authorizationRules(): array
    {
        return [
            'diagnosis'               => ['nullable', 'string', 'max:5000'],
            'recipient_person_agency' => ['nullable', 'string', 'max:500'],
            'recipient_address'       => ['nullable', 'string', 'max:1000'],
            'recipient_phone'         => ['nullable', 'string', 'max:50'],
            'recipient_fax'           => ['nullable', 'string', 'max:50'],
            'recipient_email'         => ['nullable', 'email', 'max:255'],
            'agency_name'             => ['nullable', 'string', 'max:500'],
            'information_released'    => ['nullable', 'array'],
            'information_released.*'  => ['string', 'max:255'],
            'purpose'                 => ['nullable', 'string', 'max:5000'],
            'expiration_type'         => ['nullable', 'string', 'in:one_year,sixty_days,specific_date,other'],
            'expiration_date'         => ['nullable', 'date'],
            'expiration_other'        => ['nullable', 'string', 'max:500'],
            'witness'                 => ['nullable', 'string', 'max:255'],
            'signers'                 => ['nullable', 'array'],
            'signers.*'               => ['integer', 'exists:users,id'],
            'employee_signature_id'   => ['nullable', 'integer', 'exists:signatures,id'],
        ];
    }
}
