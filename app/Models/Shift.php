<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shift_date',
        'start_time',
        'end_time',
        'type',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'shift_date' => 'date',
        ];
    }

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes

    public function scopeForDate($query, $date)
    {
        return $query->where('shift_date', $date);
    }

    public function scopeToday($query)
    {
        return $query->where('shift_date', today());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('shift_date', '>=', today());
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeScheduled($query)
    {
        return $query->whereIn('status', ['scheduled', 'in_progress']);
    }

    // Accessors

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'morning' => 'Morning',
            'afternoon' => 'Afternoon',
            'night' => 'Night',
            'custom' => 'Custom',
            default => ucfirst($this->type),
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'morning' => 'amber',
            'afternoon' => 'sky',
            'night' => 'indigo',
            'custom' => 'zinc',
            default => 'zinc',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'sky',
            'in_progress' => 'green',
            'completed' => 'zinc',
            'cancelled' => 'red',
            'no_show' => 'red',
            default => 'zinc',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'Scheduled',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show',
            default => ucfirst($this->status),
        };
    }
}
