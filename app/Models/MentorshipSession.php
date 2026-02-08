<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MentorshipSession extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'topic_id',
        'mentor_id',
        'lesson_id',
        'session_date',
        'start_time',
        'end_time',
        'participant_count',
        'participant_notes',
        'status',
        'session_notes',
        'lesson_content_snapshot',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'participant_count' => 'integer',
        ];
    }

    // Relationships

    public function topic(): BelongsTo
    {
        return $this->belongsTo(MentorshipTopic::class, 'topic_id');
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(MentorshipLesson::class, 'lesson_id');
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

    public function scopeForMentor($query, int $userId)
    {
        return $query->where('mentor_id', $userId);
    }

    public function scopePlanned($query)
    {
        return $query->where('status', 'planned');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeInDateRange($query, $from, $to)
    {
        return $query->whereBetween('session_date', [$from, $to]);
    }

    public function scopeByTopic($query, int $topicId)
    {
        return $query->where('topic_id', $topicId);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('session_date', '>=', today())
            ->whereIn('status', ['planned', 'in_progress'])
            ->orderBy('session_date')
            ->orderBy('start_time');
    }

    // Accessors

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'planned' => 'Planned',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'planned' => 'blue',
            'in_progress' => 'amber',
            'completed' => 'green',
            'cancelled' => 'red',
            default => 'zinc',
        };
    }

    public function getDurationMinutesAttribute(): ?int
    {
        if (! $this->start_time || ! $this->end_time) {
            return null;
        }

        return $this->start_time->diffInMinutes($this->end_time);
    }

    public function getFormattedDurationAttribute(): ?string
    {
        $minutes = $this->duration_minutes;

        if ($minutes === null) {
            return null;
        }

        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($hours > 0 && $mins > 0) {
            return "{$hours}h {$mins}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        }

        return "{$mins}m";
    }

    // Methods

    public function markInProgress(): void
    {
        $this->update([
            'status' => 'in_progress',
            'start_time' => $this->start_time ?? now()->format('H:i'),
            'updated_by' => auth()->id(),
        ]);
    }

    public function markCompleted(int $participantCount, ?string $notes = null): void
    {
        $this->update([
            'status' => 'completed',
            'end_time' => $this->end_time ?? now()->format('H:i'),
            'participant_count' => $participantCount,
            'participant_notes' => $notes,
            'updated_by' => auth()->id(),
        ]);

        // Increment lesson usage count if a lesson was used
        if ($this->lesson_id) {
            $this->lesson?->incrementUsage();
        }
    }

    public function cancel(): void
    {
        $this->update([
            'status' => 'cancelled',
            'updated_by' => auth()->id(),
        ]);
    }

    public function snapshotLessonContent(?string $content = null): void
    {
        $this->update([
            'lesson_content_snapshot' => $content ?? $this->lesson?->content,
            'updated_by' => auth()->id(),
        ]);
    }
}
