<?php

namespace App\Concerns;

trait SafetyPlanValidationRules
{
    protected function safetyPlanRules(): array
    {
        return [
            'diagnosis'                          => ['nullable', 'string', 'max:5000'],
            'warning_signs'                      => ['nullable', 'array'],
            'warning_signs.*'                    => ['nullable', 'string', 'max:500'],
            'coping_strategies'                  => ['nullable', 'array'],
            'coping_strategies.*'                => ['nullable', 'string', 'max:500'],
            'distraction_people'                 => ['nullable', 'array'],
            'distraction_people.*.name'          => ['nullable', 'string', 'max:255'],
            'distraction_people.*.phone'         => ['nullable', 'string', 'max:50'],
            'distraction_people.*.relationship'  => ['nullable', 'string', 'max:255'],
            'distraction_places'                 => ['nullable', 'array'],
            'distraction_places.*'               => ['nullable', 'string', 'max:500'],
            'help_people'                        => ['nullable', 'array'],
            'help_people.*.name'                 => ['nullable', 'string', 'max:255'],
            'help_people.*.phone'                => ['nullable', 'string', 'max:50'],
            'help_people.*.relationship'         => ['nullable', 'string', 'max:255'],
            'crisis_professionals'               => ['nullable', 'array'],
            'crisis_professionals.*.facility_name'  => ['nullable', 'string', 'max:255'],
            'crisis_professionals.*.phone'          => ['nullable', 'string', 'max:50'],
            'crisis_professionals.*.clinician_name' => ['nullable', 'string', 'max:255'],
            'crisis_professionals.*.relationship'   => ['nullable', 'string', 'max:255'],
            'environment_safety'                 => ['nullable', 'string', 'max:10000'],
            'signers'                            => ['nullable', 'array'],
            'signers.*'                          => ['integer', 'exists:users,id'],
            'signature_id'                       => ['nullable', 'integer', 'exists:signatures,id'],
        ];
    }
}
