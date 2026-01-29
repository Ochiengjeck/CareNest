<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'medication_id',
        'resident_id',
        'administered_at',
        'status',
        'notes',
        'administered_by',
    ];

    protected function casts(): array
    {
        return [
            'administered_at' => 'datetime',
        ];
    }

    // Relationships

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function administeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'administered_by');
    }

    // Accessors

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'given' => 'green',
            'refused' => 'amber',
            'withheld' => 'blue',
            'missed' => 'red',
            default => 'zinc',
        };
    }
}
