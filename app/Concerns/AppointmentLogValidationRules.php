<?php

namespace App\Concerns;

trait AppointmentLogValidationRules
{
    protected function appointmentLogRules(): array
    {
        return [
            'contact_number'   => ['nullable', 'string', 'max:50'],
            'appointment_date' => ['required', 'date'],
            'time_slot'        => ['nullable', 'string', 'max:50'],
            'address'          => ['nullable', 'string', 'max:1000'],
            'reason'           => ['nullable', 'string', 'max:500'],
        ];
    }
}
