<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaceSheet extends Model
{
    protected $fillable = [
        'resident_id', 'diagnosis', 'facility_address', 'facility_phone',
        'place_of_birth', 'eye_color', 'race', 'height', 'weight', 'hair_color',
        'identifiable_marks', 'primary_language', 'court_ordered',
        'family_emergency_contact', 'facility_emergency_contact',
        'medication_allergies', 'other_allergies',
        'pcp_name', 'pcp_phone', 'pcp_address',
        'specialist_1_type', 'specialist_1_name', 'specialist_1_phone', 'specialist_1_address',
        'psych_name', 'psych_phone', 'psych_address',
        'specialist_2_type', 'specialist_2_name', 'specialist_2_phone', 'specialist_2_address',
        'preferred_hospital', 'preferred_hospital_phone', 'preferred_hospital_address',
        'health_plan', 'health_plan_id',
        'case_manager_name', 'case_manager_phone', 'case_manager_email',
        'ss_rep_payee', 'ss_rep_phone', 'ss_rep_email',
        'mental_health_diagnoses', 'medical_diagnoses', 'past_surgeries',
        'signers', 'signature_id', 'raw_signature_data', 'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'court_ordered'      => 'boolean',
            'signers'            => 'array',
            'raw_signature_data' => 'encrypted',
        ];
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function signature(): BelongsTo
    {
        return $this->belongsTo(Signature::class);
    }
}
