<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObservationNote extends Model
{
    protected $fillable = [
        'resident_id',
        'observed_at',
        'observation_type',
        'behavior',
        'location',
        'mood_affect',
        'safety_status',
        'notes',
        'observed_by',
    ];

    protected function casts(): array
    {
        return [
            'observed_at' => 'datetime',
        ];
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function observer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'observed_by');
    }

    public function getObservationTypeLabelAttribute(): string
    {
        return match($this->observation_type) {
            'every_15_min' => 'Every 15 Minutes',
            'every_30_min' => 'Every 30 Minutes',
            'one_to_one'   => '1:1 Observation',
            'continuous'   => 'Continuous',
            default        => ucfirst($this->observation_type),
        };
    }

    public function getSafetyStatusColorAttribute(): string
    {
        return match($this->safety_status) {
            'safe'    => 'green',
            'at_risk' => 'amber',
            'unsafe'  => 'red',
            default   => 'zinc',
        };
    }

    public function getSafetyStatusLabelAttribute(): string
    {
        return match($this->safety_status) {
            'safe'    => 'Safe',
            'at_risk' => 'At Risk',
            'unsafe'  => 'Unsafe',
            default   => ucfirst($this->safety_status),
        };
    }
}
