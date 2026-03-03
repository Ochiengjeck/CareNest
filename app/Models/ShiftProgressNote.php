<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftProgressNote extends Model
{
    protected $fillable = [
        'resident_id',
        'shift_date',
        'shift_start_time',
        'shift_end_time',
        'appointment',
        'appointment_other',
        'mood',
        'mood_other',
        'speech',
        'speech_other',
        'behaviors',
        'behaviors_other',
        'resident_redirected',
        'outing_in_community',
        'therapy_participation',
        'awol',
        'welfare_checks',
        'medication_administered',
        'meal_preparation',
        'meals',
        'snacks',
        'adls_completed',
        'prompted_medications',
        'prompted_adls',
        'water_temperature_adjusted',
        'clothing_assistance',
        'activities',
        'activities_other',
        'note_summary',
        'signature_id',
        'raw_signature_data',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'shift_date'                 => 'date',
            'appointment'                => 'array',
            'mood'                       => 'array',
            'speech'                     => 'array',
            'behaviors'                  => 'array',
            'meals'                      => 'array',
            'snacks'                     => 'array',
            'activities'                 => 'array',
            'resident_redirected'        => 'boolean',
            'outing_in_community'        => 'boolean',
            'awol'                       => 'boolean',
            'welfare_checks'             => 'boolean',
            'adls_completed'             => 'boolean',
            'prompted_medications'       => 'boolean',
            'prompted_adls'              => 'boolean',
            'water_temperature_adjusted' => 'boolean',
            'clothing_assistance'        => 'boolean',
            'raw_signature_data'         => 'encrypted',
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

    public function getShiftTypeLabelAttribute(): string
    {
        if (! $this->shift_start_time) {
            return 'Shift';
        }

        $hour = (int) substr($this->shift_start_time, 0, 2);

        if ($hour >= 6 && $hour < 14) {
            return 'Day Shift';
        }

        if ($hour >= 14 && $hour < 22) {
            return 'Evening Shift';
        }

        return 'Night Shift';
    }
}
