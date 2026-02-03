<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'icon',
        'image_path',
        'features',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // Available icons for selection
    public const ICONS = [
        'home' => 'Home',
        'heart' => 'Heart',
        'clock' => 'Clock',
        'arrow-path' => 'Arrow Path',
        'shield-check' => 'Shield Check',
        'user-group' => 'User Group',
        'star' => 'Star',
        'hand-raised' => 'Hand Raised',
        'sparkles' => 'Sparkles',
        'sun' => 'Sun',
        'moon' => 'Moon',
        'academic-cap' => 'Academic Cap',
        'beaker' => 'Beaker',
        'building-office' => 'Building Office',
        'cake' => 'Cake',
        'chat-bubble-left-right' => 'Chat',
        'clipboard-document-check' => 'Clipboard Check',
        'face-smile' => 'Face Smile',
        'gift' => 'Gift',
        'light-bulb' => 'Light Bulb',
        'musical-note' => 'Musical Note',
        'puzzle-piece' => 'Puzzle Piece',
        'trophy' => 'Trophy',
        'wrench-screwdriver' => 'Tools',
    ];

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at', 'desc');
    }

    // Accessors

    public function getIconLabelAttribute(): string
    {
        return self::ICONS[$this->icon] ?? ucfirst(str_replace('-', ' ', $this->icon));
    }

    public function getFeaturesArrayAttribute(): array
    {
        return $this->features ?? [];
    }
}
