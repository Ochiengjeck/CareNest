<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CarePlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'resident_id',
        'title',
        'type',
        'status',
        'start_date',
        'review_date',
        'description',
        'goals',
        'interventions',
        'notes',
        'created_by',
        'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'review_date' => 'date',
        ];
    }

    // Relationships

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForResident($query, int $residentId)
    {
        return $query->where('resident_id', $residentId);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // Helpers

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'general' => 'General',
            'nutrition' => 'Nutrition',
            'mobility' => 'Mobility',
            'mental_health' => 'Mental Health',
            'personal_care' => 'Personal Care',
            'medication' => 'Medication',
            'social' => 'Social',
            default => ucfirst($this->type),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active' => 'green',
            'draft' => 'zinc',
            'archived' => 'amber',
            'under_review' => 'blue',
            default => 'zinc',
        };
    }
}
