<?php

namespace App\Concerns;

trait DischargeValidationRules
{
    protected function dischargeStep1Rules(): array
    {
        return [
            // Provider Information
            'agency_name' => ['nullable', 'string', 'max:255'],
            'discharge_staff_id' => ['nullable', 'exists:users,id'],
            'discharge_staff_name' => ['nullable', 'string', 'max:255'],

            // Dates
            'discharge_date' => ['required', 'date'],

            // Aftercare Information
            'next_level_of_care' => ['nullable', 'string', 'max:255'],
            'barriers_to_transition' => ['nullable', 'string', 'max:5000'],
            'strengths_for_discharge' => ['nullable', 'string', 'max:5000'],

            // Clinical Summary
            'reason_for_admission' => ['nullable', 'string', 'max:10000'],
            'course_of_treatment' => ['nullable', 'string', 'max:10000'],
            'discharge_status_recommendations' => ['nullable', 'string', 'max:10000'],
        ];
    }

    protected function dischargeStep2Rules(): array
    {
        return [
            'discharge_condition_reason' => ['nullable', 'string', 'max:10000'],
            'crisis_plan' => ['nullable', 'string', 'max:10000'],
            'future_appointments' => ['nullable', 'array'],
            'future_appointments.*.date' => ['nullable', 'date'],
            'future_appointments.*.time' => ['nullable', 'string', 'max:50'],
            'future_appointments.*.provider' => ['nullable', 'string', 'max:255'],
            'future_appointments.*.provider_id' => ['nullable', 'exists:users,id'],
            'future_appointments.*.location' => ['nullable', 'string', 'max:255'],
            'future_appointments.*.agency_id' => ['nullable', 'exists:agencies,id'],
            'future_appointments.*.phone' => ['nullable', 'string', 'max:50'],
            'future_appointments.*.notes' => ['nullable', 'string', 'max:500'],
            'selected_agencies' => ['nullable', 'array'],
            'selected_agencies.*' => ['exists:agencies,id'],
        ];
    }

    protected function dischargeStep3Rules(): array
    {
        return [
            'special_needs' => ['nullable', 'string', 'max:5000'],
            'medications_at_discharge' => ['nullable', 'array'],
            'medications_at_discharge.*.name' => ['nullable', 'string', 'max:255'],
            'medications_at_discharge.*.dosage' => ['nullable', 'string', 'max:255'],
            'medications_at_discharge.*.quantity' => ['nullable', 'string', 'max:255'],
            'personal_possessions' => ['nullable', 'string', 'max:5000'],
        ];
    }

    protected function allDischargeRules(): array
    {
        return array_merge(
            $this->dischargeStep1Rules(),
            $this->dischargeStep2Rules(),
            $this->dischargeStep3Rules()
        );
    }
}
