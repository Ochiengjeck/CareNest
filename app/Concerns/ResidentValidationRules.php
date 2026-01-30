<?php

namespace App\Concerns;

trait ResidentValidationRules
{
    protected function personalInfoRules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'string', 'in:male,female,other'],
            'photo' => ['nullable', 'image', 'max:1024'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
        ];
    }

    protected function admissionRules(): array
    {
        return [
            'admission_date' => ['required', 'date'],
            'room_number' => ['nullable', 'string', 'max:50'],
            'bed_number' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'string', 'in:active,discharged,deceased,on_leave'],
        ];
    }

    protected function dischargeDateRules(): array
    {
        return [
            'discharge_date' => ['nullable', 'date', 'after_or_equal:admission_date'],
        ];
    }

    protected function medicalRules(): array
    {
        return [
            'blood_type' => ['nullable', 'string', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'allergies' => ['nullable', 'string', 'max:5000'],
            'medical_conditions' => ['nullable', 'string', 'max:5000'],
            'mobility_status' => ['required', 'string', 'in:independent,assisted,wheelchair,bedridden'],
            'dietary_requirements' => ['nullable', 'string', 'max:2000'],
            'fall_risk_level' => ['required', 'string', 'in:low,medium,high'],
            'dnr_status' => ['required', 'boolean'],
        ];
    }

    protected function emergencyContactRules(): array
    {
        return [
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:100'],
        ];
    }

    protected function nextOfKinRules(): array
    {
        return [
            'nok_name' => ['nullable', 'string', 'max:255'],
            'nok_phone' => ['nullable', 'string', 'max:50'],
            'nok_email' => ['nullable', 'email', 'max:255'],
            'nok_relationship' => ['nullable', 'string', 'max:100'],
            'nok_address' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function notesRules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
