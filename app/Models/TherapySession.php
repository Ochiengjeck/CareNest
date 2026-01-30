<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TherapySession extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'therapist_id',
        'resident_id',
        'session_date',
        'start_time',
        'end_time',
        'service_type',
        'challenge_index',
        'session_topic',
        'interventions',
        'progress_notes',
        'client_plan',
        'status',
        'supervisor_id',
        'supervisor_signed_at',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'supervisor_signed_at' => 'datetime',
        ];
    }

    // Relationships

    public function therapist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'therapist_id');
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeForTherapist($query, int $userId)
    {
        return $query->where('therapist_id', $userId);
    }

    public function scopeForResident($query, int $residentId)
    {
        return $query->where('resident_id', $residentId);
    }

    public function scopeInDateRange($query, ?string $from, ?string $to)
    {
        return $query
            ->when($from, fn ($q) => $q->whereDate('session_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('session_date', '<=', $to));
    }

    public function scopeByServiceType($query, string $type)
    {
        return $query->where('service_type', $type);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('session_date', today());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('session_date', '>=', today())
            ->where('status', 'scheduled')
            ->orderBy('session_date')
            ->orderBy('start_time');
    }

    // Accessors

    public function getServiceTypeLabelAttribute(): string
    {
        return match ($this->service_type) {
            'individual' => 'Individual Note',
            'group' => 'Group',
            'intake_assessment' => 'Intake/Assessment',
            'crisis' => 'Crisis',
            'collateral' => 'Collateral',
            'case_management' => 'Case Management',
            'treatment_planning' => 'Treatment Planning',
            'discharge' => 'Discharge',
            'other' => 'Other',
            default => ucfirst($this->service_type),
        };
    }

    public function getServiceTypeColorAttribute(): string
    {
        return match ($this->service_type) {
            'individual' => 'blue',
            'group' => 'purple',
            'intake_assessment' => 'cyan',
            'crisis' => 'red',
            'collateral' => 'amber',
            'case_management' => 'zinc',
            'treatment_planning' => 'green',
            'discharge' => 'rose',
            'other' => 'zinc',
            default => 'zinc',
        };
    }

    public function getChallengeLabelAttribute(): ?string
    {
        if (! $this->challenge_index) {
            return null;
        }

        return match ($this->challenge_index) {
            'substance_use' => '1. Substance Use Disorder',
            'mental_health' => '2. Mental Health',
            'physical_health' => '3. Physical Health',
            'employment_education' => '4. Employment/Education',
            'financial_housing' => '5. Financial/Housing',
            'legal' => '6. Legal',
            'psychosocial_family' => '7. Psycho-Social/Family',
            'spirituality' => '8. Spirituality',
            default => ucfirst(str_replace('_', ' ', $this->challenge_index)),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'Scheduled',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'blue',
            'completed' => 'green',
            'cancelled' => 'zinc',
            'no_show' => 'red',
            default => 'zinc',
        };
    }

    public function getDurationMinutesAttribute(): ?int
    {
        if (! $this->start_time || ! $this->end_time) {
            return null;
        }

        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        return $start->diffInMinutes($end);
    }

    public function getFormattedTimeRangeAttribute(): string
    {
        $start = Carbon::parse($this->start_time)->format('g:i A');
        $end = Carbon::parse($this->end_time)->format('g:i A');

        return "{$start} - {$end}";
    }
}
