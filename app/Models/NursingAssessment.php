<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NursingAssessment extends Model
{
    protected $fillable = [
        'resident_id',
        'assessment_date',
        'safety_screening',
        'substance_use_check',
        'physical_condition',
        'nursing_intake_note',
        'risk_level',
        'risk_assessment_notes',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'assessment_date'   => 'datetime',
            'safety_screening'  => 'array',
            'substance_use_check' => 'array',
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

    public function getRiskLevelColorAttribute(): string
    {
        return match($this->risk_level) {
            'low'      => 'green',
            'moderate' => 'amber',
            'high'     => 'red',
            'imminent' => 'red',
            default    => 'zinc',
        };
    }

    public function getRiskLevelLabelAttribute(): string
    {
        return match($this->risk_level) {
            'low'      => 'Low',
            'moderate' => 'Moderate',
            'high'     => 'High',
            'imminent' => 'Imminent',
            default    => ucfirst($this->risk_level),
        };
    }
}
