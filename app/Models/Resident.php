<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Resident extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'photo_path',
        'phone',
        'email',
        'admission_date',
        'discharge_date',
        'room_number',
        'bed_number',
        'status',
        'blood_type',
        'allergies',
        'medical_conditions',
        'mobility_status',
        'dietary_requirements',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'nok_name',
        'nok_phone',
        'nok_email',
        'nok_relationship',
        'nok_address',
        'fall_risk_level',
        'dnr_status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'admission_date' => 'date',
            'discharge_date' => 'date',
            'dnr_status' => 'boolean',
        ];
    }

    // Relationships

    public function carePlans(): HasMany
    {
        return $this->hasMany(CarePlan::class);
    }

    public function medications(): HasMany
    {
        return $this->hasMany(Medication::class);
    }

    public function medicationLogs(): HasMany
    {
        return $this->hasMany(MedicationLog::class);
    }

    public function vitals(): HasMany
    {
        return $this->hasMany(Vital::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
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

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('first_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('room_number', 'like', "%{$term}%");
        });
    }

    // Accessors

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth?->age;
    }

    // Methods

    public function getActiveCarePlansCount(): int
    {
        return $this->carePlans()->where('status', 'active')->count();
    }

    public function initials(): string
    {
        return Str::of($this->full_name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
}
