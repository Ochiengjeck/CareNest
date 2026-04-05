<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtMeeting extends Model
{
    protected $fillable = [
        'resident_id',
        'meeting_date',
        'meeting_type',
        'attendees',
        'discussion_notes',
        'treatment_plan_revisions',
        'next_meeting_date',
        'recorded_by',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'meeting_date'      => 'date',
            'next_meeting_date' => 'date',
            'attendees'         => 'array',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getMeetingTypeLabelAttribute(): string
    {
        return match($this->meeting_type) {
            'scheduled'         => 'Scheduled',
            'emergency'         => 'Emergency',
            'discharge_planning' => 'Discharge Planning',
            default             => ucfirst($this->meeting_type),
        };
    }

    public function getMeetingTypeColorAttribute(): string
    {
        return match($this->meeting_type) {
            'scheduled'         => 'blue',
            'emergency'         => 'red',
            'discharge_planning' => 'amber',
            default             => 'zinc',
        };
    }
}
