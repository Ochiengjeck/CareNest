<?php

namespace App\Concerns;

trait ContactNoteValidationRules
{
    protected function contactNoteRules(): array
    {
        return [
            'contact_date'       => ['required', 'date'],
            'contact_time'       => ['nullable', 'date_format:H:i'],
            'person_contacted'   => ['nullable', 'array'],
            'person_contacted.*' => ['string', 'max:255'],
            'contact_name'       => ['nullable', 'string', 'max:500'],
            'mode_of_contact'    => ['nullable', 'array'],
            'mode_of_contact.*'  => ['string', 'max:255'],
            'mode_other'         => ['nullable', 'string', 'max:500'],
            'diagnosis'          => ['nullable', 'string', 'max:5000'],
            'contact_summary'    => ['nullable', 'string', 'max:10000'],
            'emergency_issue'    => ['nullable', 'boolean'],
            'signers'            => ['nullable', 'array'],
            'signers.*'          => ['integer', 'exists:users,id'],
            'signature_id'       => ['nullable', 'integer', 'exists:signatures,id'],
        ];
    }
}
