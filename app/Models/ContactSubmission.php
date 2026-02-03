<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
        'admin_notes',
        'replied_by',
        'read_at',
        'replied_at',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'replied_at' => 'datetime',
        ];
    }

    // Status constants
    public const STATUS_NEW = 'new';
    public const STATUS_READ = 'read';
    public const STATUS_REPLIED = 'replied';
    public const STATUS_ARCHIVED = 'archived';

    public const STATUSES = [
        self::STATUS_NEW => 'New',
        self::STATUS_READ => 'Read',
        self::STATUS_REPLIED => 'Replied',
        self::STATUS_ARCHIVED => 'Archived',
    ];

    // Subject options (matching the contact form)
    public const SUBJECTS = [
        'Schedule a Tour' => 'Schedule a Tour',
        'Admissions Inquiry' => 'Admissions Inquiry',
        'Services Information' => 'Services Information',
        'Pricing & Payment' => 'Pricing & Payment',
        'General Question' => 'General Question',
        'Other' => 'Other',
    ];

    // Relationships

    public function repliedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replied_by');
    }

    // Scopes

    public function scopeNew($query)
    {
        return $query->where('status', self::STATUS_NEW);
    }

    public function scopeUnread($query)
    {
        return $query->whereIn('status', [self::STATUS_NEW]);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeBySubject($query, string $subject)
    {
        return $query->where('subject', $subject);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('message', 'like', "%{$search}%");
        });
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Accessors

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_NEW => 'amber',
            self::STATUS_READ => 'blue',
            self::STATUS_REPLIED => 'green',
            self::STATUS_ARCHIVED => 'zinc',
            default => 'zinc',
        };
    }

    public function getIsNewAttribute(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    // Methods

    public function markAsRead(): void
    {
        if ($this->status === self::STATUS_NEW) {
            $this->update([
                'status' => self::STATUS_READ,
                'read_at' => now(),
            ]);
        }
    }

    public function markAsReplied(int $userId): void
    {
        $this->update([
            'status' => self::STATUS_REPLIED,
            'replied_by' => $userId,
            'replied_at' => now(),
        ]);
    }

    public function archive(): void
    {
        $this->update(['status' => self::STATUS_ARCHIVED]);
    }

    public function restore(): void
    {
        $this->update(['status' => self::STATUS_READ]);
    }
}
