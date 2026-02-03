<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discharge extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'resident_id',
        'agency_name',
        'discharge_staff_name',
        'discharge_staff_id',
        'discharge_date',
        'next_level_of_care',
        'barriers_to_transition',
        'strengths_for_discharge',
        'reason_for_admission',
        'course_of_treatment',
        'discharge_status_recommendations',
        'discharge_condition_reason',
        'crisis_plan',
        'future_appointments',
        'selected_agencies',
        'special_needs',
        'medications_at_discharge',
        'personal_possessions',
        'created_by',
        'updated_by',
        'admin_override_by',
        'admin_override_at',
        'ai_generated_at',
    ];

    protected function casts(): array
    {
        return [
            'discharge_date' => 'date',
            'future_appointments' => 'array',
            'selected_agencies' => 'array',
            'medications_at_discharge' => 'array',
            'admin_override_at' => 'datetime',
            'ai_generated_at' => 'datetime',
        ];
    }

    // Relationships

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function dischargeStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'discharge_staff_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function adminOverrider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_override_by');
    }
}
