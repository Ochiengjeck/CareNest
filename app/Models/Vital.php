<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vital extends Model
{
    use HasFactory;

    protected $fillable = [
        'resident_id',
        'recorded_at',
        'blood_pressure_systolic',
        'blood_pressure_diastolic',
        'heart_rate',
        'temperature',
        'respiratory_rate',
        'oxygen_saturation',
        'blood_sugar',
        'weight',
        'pain_level',
        'consciousness_level',
        'notes',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'blood_pressure_systolic' => 'integer',
            'blood_pressure_diastolic' => 'integer',
            'heart_rate' => 'integer',
            'temperature' => 'decimal:1',
            'respiratory_rate' => 'integer',
            'oxygen_saturation' => 'integer',
            'blood_sugar' => 'decimal:1',
            'weight' => 'decimal:1',
            'pain_level' => 'integer',
        ];
    }

    // Relationships

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    // Accessors

    public function getBloodPressureAttribute(): ?string
    {
        if ($this->blood_pressure_systolic && $this->blood_pressure_diastolic) {
            return "{$this->blood_pressure_systolic}/{$this->blood_pressure_diastolic}";
        }

        return null;
    }

    // Methods

    public function hasAbnormalValues(): bool
    {
        if ($this->blood_pressure_systolic && ($this->blood_pressure_systolic > 140 || $this->blood_pressure_systolic < 90)) {
            return true;
        }
        if ($this->blood_pressure_diastolic && ($this->blood_pressure_diastolic > 90 || $this->blood_pressure_diastolic < 60)) {
            return true;
        }
        if ($this->heart_rate && ($this->heart_rate > 100 || $this->heart_rate < 60)) {
            return true;
        }
        if ($this->temperature && ($this->temperature > 37.8 || $this->temperature < 36.0)) {
            return true;
        }
        if ($this->respiratory_rate && ($this->respiratory_rate > 20 || $this->respiratory_rate < 12)) {
            return true;
        }
        if ($this->oxygen_saturation && $this->oxygen_saturation < 95) {
            return true;
        }

        return false;
    }
}
