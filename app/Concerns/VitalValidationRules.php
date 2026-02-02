<?php

namespace App\Concerns;

trait VitalValidationRules
{
    protected function vitalRules(): array
    {
        return [
            'resident_id' => ['required', 'exists:residents,id'],
            'recorded_at' => ['required', 'date'],
            'blood_pressure_systolic' => ['nullable', 'integer', 'min:40', 'max:300'],
            'blood_pressure_diastolic' => ['nullable', 'integer', 'min:20', 'max:200'],
            'heart_rate' => ['nullable', 'integer', 'min:20', 'max:250'],
            'temperature' => ['nullable', 'numeric', 'min:86', 'max:113'],
            'respiratory_rate' => ['nullable', 'integer', 'min:5', 'max:60'],
            'oxygen_saturation' => ['nullable', 'integer', 'min:0', 'max:100'],
            'blood_sugar' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'pain_level' => ['nullable', 'integer', 'min:0', 'max:10'],
            'consciousness_level' => ['nullable', 'string', 'in:alert,verbal,pain,unresponsive'],
            'notes' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
