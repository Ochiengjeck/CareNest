<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // Available icons for selection
    public const ICONS = [
        'home-modern' => 'Home Modern',
        'sun' => 'Sun',
        'cake' => 'Cake',
        'tv' => 'TV',
        'puzzle-piece' => 'Puzzle Piece',
        'book-open' => 'Book Open',
        'musical-note' => 'Musical Note',
        'wifi' => 'WiFi',
        'heart' => 'Heart',
        'star' => 'Star',
        'shield-check' => 'Shield Check',
        'sparkles' => 'Sparkles',
        'fire' => 'Fire',
        'bolt' => 'Bolt',
        'beaker' => 'Beaker',
        'building-office' => 'Building',
        'camera' => 'Camera',
        'clock' => 'Clock',
        'cog' => 'Cog',
        'computer-desktop' => 'Computer',
        'gift' => 'Gift',
        'globe-alt' => 'Globe',
        'light-bulb' => 'Light Bulb',
        'map' => 'Map',
        'paint-brush' => 'Paint Brush',
        'phone' => 'Phone',
        'printer' => 'Printer',
        'shopping-bag' => 'Shopping Bag',
        'truck' => 'Truck',
        'wrench' => 'Wrench',
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
}
