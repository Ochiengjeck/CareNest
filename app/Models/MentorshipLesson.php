<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MentorshipLesson extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'category',
        'content',
        'summary',
        'source_topic_id',
        'is_ai_generated',
        'is_published',
        'visibility',
        'times_used',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_ai_generated' => 'boolean',
            'is_published' => 'boolean',
            'times_used' => 'integer',
        ];
    }

    // Relationships

    public function sourceTopic(): BelongsTo
    {
        return $this->belongsTo(MentorshipTopic::class, 'source_topic_id');
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

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeUnpublished($query)
    {
        return $query->where('is_published', false);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeAiGenerated($query)
    {
        return $query->where('is_ai_generated', true);
    }

    public function scopeShared($query)
    {
        return $query->where('visibility', 'shared');
    }

    public function scopePrivate($query)
    {
        return $query->where('visibility', 'private');
    }

    public function scopeForMentor($query, int $userId)
    {
        return $query->where('created_by', $userId);
    }

    public function scopeAvailableToMentor($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('visibility', 'shared')
              ->orWhere('created_by', $userId);
        });
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

    public function getContentPreviewAttribute(): string
    {
        return Str::limit(strip_tags($this->content), 200);
    }

    public function getSummaryOrPreviewAttribute(): string
    {
        return $this->summary ?? $this->content_preview;
    }

    public function getVisibilityLabelAttribute(): string
    {
        return match ($this->visibility) {
            'private' => 'Private',
            'shared' => 'Shared',
            default => ucfirst($this->visibility ?? 'private'),
        };
    }

    public function getVisibilityColorAttribute(): string
    {
        return match ($this->visibility) {
            'private' => 'zinc',
            'shared' => 'green',
            default => 'zinc',
        };
    }

    // Methods

    public function incrementUsage(): void
    {
        $this->increment('times_used');
    }

    public function share(): void
    {
        $this->update(['visibility' => 'shared']);
    }

    public function makePrivate(): void
    {
        $this->update(['visibility' => 'private']);
    }
}
