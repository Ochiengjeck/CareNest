<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'resident_id',
        'name',
        'dosage',
        'frequency',
        'administration_times',
        'route',
        'prescribed_by',
        'prescribed_date',
        'start_date',
        'end_date',
        'status',
        'instructions',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'prescribed_date' => 'date',
            'start_date' => 'date',
            'end_date' => 'date',
            'administration_times' => 'array',
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

    public function logs(): HasMany
    {
        return $this->hasMany(MedicationLog::class);
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

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // Accessors

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active' => 'green',
            'completed' => 'zinc',
            'discontinued' => 'red',
            'on_hold' => 'amber',
            default => 'zinc',
        };
    }

    public function getScheduledTimesAttribute(): array
    {
        if (!empty($this->administration_times)) {
            return $this->administration_times;
        }
        $freq = strtolower($this->frequency ?? '');
        if (preg_match('/\bqid\b|four.times|4\s*times/i', $freq)) {
            return ['08:00', '12:00', '16:00', '20:00'];
        }
        if (preg_match('/\btid\b|three.times|3\s*times/i', $freq)) {
            return ['08:00', '14:00', '20:00'];
        }
        if (preg_match('/\bbid\b|twice|two.times|2\s*times/i', $freq)) {
            return ['08:00', '20:00'];
        }
        return ['08:00'];
    }

    public function getRouteLabelAttribute(): string
    {
        return match ($this->route) {
            'oral' => 'Oral',
            'topical' => 'Topical',
            'injection' => 'Injection',
            'inhalation' => 'Inhalation',
            'sublingual' => 'Sublingual',
            'rectal' => 'Rectal',
            'other' => 'Other',
            default => ucfirst($this->route),
        };
    }
}
