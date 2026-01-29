<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Incident extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'resident_id',
        'title',
        'type',
        'severity',
        'occurred_at',
        'location',
        'description',
        'immediate_actions',
        'witnesses',
        'outcome',
        'follow_up_actions',
        'status',
        'reported_by',
        'reviewed_by',
        'reviewed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    // Relationships

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Scopes

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // Accessors

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'fall' => 'Fall',
            'medication_error' => 'Medication Error',
            'injury' => 'Injury',
            'behavioral' => 'Behavioral',
            'equipment_failure' => 'Equipment Failure',
            'other' => 'Other',
            default => ucfirst($this->type),
        };
    }

    public function getSeverityColorAttribute(): string
    {
        return match ($this->severity) {
            'minor' => 'zinc',
            'moderate' => 'amber',
            'major' => 'orange',
            'critical' => 'red',
            default => 'zinc',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'open' => 'red',
            'under_investigation' => 'amber',
            'resolved' => 'green',
            'closed' => 'zinc',
            default => 'zinc',
        };
    }
}
