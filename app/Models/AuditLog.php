<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByModel($query, string $modelType)
    {
        return $query->where('auditable_type', $modelType);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeInDateRange($query, ?string $from, ?string $to)
    {
        return $query
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to));
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('description', 'like', "%{$term}%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%"));
        });
    }

    // Accessors

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'restored' => 'Restored',
            'login' => 'Logged In',
            'logout' => 'Logged Out',
            'login_failed' => 'Failed Login',
            default => ucfirst($this->action),
        };
    }

    public function getActionColorAttribute(): string
    {
        return match ($this->action) {
            'created' => 'green',
            'updated' => 'blue',
            'deleted' => 'red',
            'restored' => 'amber',
            'login' => 'green',
            'logout' => 'zinc',
            'login_failed' => 'red',
            default => 'zinc',
        };
    }

    public function getModelNameAttribute(): ?string
    {
        if (! $this->auditable_type) {
            return null;
        }

        return class_basename($this->auditable_type);
    }

    public function getChangesSummaryAttribute(): string
    {
        if ($this->action === 'created') {
            return 'New record created';
        }

        if ($this->action === 'deleted') {
            return 'Record deleted';
        }

        if ($this->action === 'updated' && $this->old_values && $this->new_values) {
            $changedFields = array_keys(array_diff_assoc(
                $this->new_values ?? [],
                $this->old_values ?? []
            ));

            if (empty($changedFields)) {
                return 'No visible changes';
            }

            return 'Changed: '.implode(', ', array_map(
                fn ($f) => str_replace('_', ' ', $f),
                array_slice($changedFields, 0, 3)
            )).(count($changedFields) > 3 ? '...' : '');
        }

        return $this->description ?? $this->action_label;
    }

    public function getAuditableRouteAttribute(): ?string
    {
        if (! $this->auditable_type || ! $this->auditable_id) {
            return null;
        }

        // Check if the record still exists
        if (! $this->auditable) {
            return null;
        }

        return match ($this->auditable_type) {
            'App\Models\User' => route('admin.users.edit', $this->auditable_id),
            'App\Models\Resident' => route('residents.show', $this->auditable_id),
            'App\Models\CarePlan' => route('care-plans.show', $this->auditable_id),
            'App\Models\Medication' => route('medications.show', $this->auditable_id),
            'App\Models\Incident' => route('incidents.show', $this->auditable_id),
            default => null,
        };
    }
}
