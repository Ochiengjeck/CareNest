<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Signature extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'pen_color',
        'signature_data',
        'is_active',
    ];

    protected $hidden = [
        'signature_data',
    ];

    protected function casts(): array
    {
        return [
            'signature_data' => 'encrypted',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getDataUri(): ?string
    {
        if (! $this->signature_data) {
            return null;
        }

        $data = $this->signature_data;

        if (str_starts_with($data, 'data:image/')) {
            return $data;
        }

        return 'data:image/png;base64,'.$data;
    }
}
