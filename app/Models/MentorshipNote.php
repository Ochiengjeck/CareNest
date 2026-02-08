<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentorshipNote extends Model
{
    protected $fillable = [
        'topic_id',
        'user_id',
        'content',
    ];

    // Relationships

    public function topic(): BelongsTo
    {
        return $this->belongsTo(MentorshipTopic::class, 'topic_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
