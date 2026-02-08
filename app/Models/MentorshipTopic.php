<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MentorshipTopic extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'topic_date',
        'day_of_week',
        'time_slot',
        'title',
        'category',
        'description',
        'is_published',
        'created_by',
        'updated_by',
        'ai_lesson_content',
        'ai_lesson_generated_at',
        'ai_lesson_saved_to',
    ];

    protected function casts(): array
    {
        return [
            'topic_date' => 'date',
            'is_published' => 'boolean',
            'ai_lesson_generated_at' => 'datetime',
        ];
    }

    // Relationships

    public function attachments(): HasMany
    {
        return $this->hasMany(MentorshipAttachment::class, 'topic_id')->orderBy('sort_order');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(MentorshipNote::class, 'topic_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(MentorshipSession::class, 'topic_id');
    }

    public function savedLesson(): BelongsTo
    {
        return $this->belongsTo(MentorshipLesson::class, 'ai_lesson_saved_to');
    }

    // Scopes

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeUnpublished($query)
    {
        return $query->where('is_published', false);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('topic_date', $date);
    }

    public function scopeForWeek($query, $startDate, $endDate)
    {
        // Format dates to Y-m-d to avoid datetime comparison issues with SQLite
        $start = $startDate instanceof \Carbon\Carbon ? $startDate->format('Y-m-d') : $startDate;
        $end = $endDate instanceof \Carbon\Carbon ? $endDate->format('Y-m-d') : $endDate;

        return $query->whereBetween('topic_date', [$start, $end])
            ->orderBy('topic_date')
            ->orderBy('time_slot');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('topic_date', '>=', today())
            ->orderBy('topic_date')
            ->orderBy('time_slot');
    }

    // Accessors

    public function getCategoryColorAttribute(): string
    {
        return match ($this->category) {
            'Mental Health' => 'blue',
            'Substance Use Disorder' => 'purple',
            'Employment/Education' => 'green',
            'Physical Health' => 'red',
            'Financial/Housing' => 'amber',
            'Psycho-Social/Family' => 'cyan',
            'Spirituality' => 'rose',
            default => 'zinc',
        };
    }

    public function getTimeSlotLabelAttribute(): string
    {
        return \Carbon\Carbon::parse($this->time_slot)->format('g:i A');
    }

    public function getHasAiLessonAttribute(): bool
    {
        return ! empty($this->ai_lesson_content);
    }

    public function getTimesTaughtAttribute(): int
    {
        return $this->sessions()->completed()->count();
    }

    public function getLastTaughtAtAttribute(): ?\Carbon\Carbon
    {
        return $this->sessions()
            ->completed()
            ->latest('session_date')
            ->value('session_date');
    }

    public function getTotalParticipantsAttribute(): int
    {
        return $this->sessions()->completed()->sum('participant_count');
    }
}
