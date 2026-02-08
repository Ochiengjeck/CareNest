<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentorshipAttachment extends Model
{
    protected $fillable = [
        'topic_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'display_name',
        'description',
        'sort_order',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    // Relationships

    public function topic(): BelongsTo
    {
        return $this->belongsTo(MentorshipTopic::class, 'topic_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Accessors

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' B';
    }

    public function getFileIconAttribute(): string
    {
        return match ($this->file_type) {
            'pdf' => 'document-text',
            'doc', 'docx' => 'document',
            'jpg', 'jpeg', 'png' => 'photo',
            default => 'paper-clip',
        };
    }

    public function getDisplayNameOrFilenameAttribute(): string
    {
        return $this->display_name ?? $this->file_name;
    }
}
