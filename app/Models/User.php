<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'signature_data',
        'signature_updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
        'signature_data',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'signature_data' => 'encrypted',
            'signature_updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get the user's primary role (first assigned role)
     */
    public function getPrimaryRole(): ?string
    {
        return $this->roles->first()?->name;
    }

    /**
     * Check if user has any of the specified roles
     */
    public function hasAnyRoles(array $roles): bool
    {
        return $this->hasAnyRole($roles);
    }

    // Staff relationships

    public function staffProfile(): HasOne
    {
        return $this->hasOne(StaffProfile::class);
    }

    public function qualifications(): HasMany
    {
        return $this->hasMany(Qualification::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function staffDocuments(): HasMany
    {
        return $this->hasMany(StaffDocument::class);
    }

    // Therapy relationships

    public function therapistAssignments(): HasMany
    {
        return $this->hasMany(TherapistAssignment::class, 'therapist_id');
    }

    public function therapySessions(): HasMany
    {
        return $this->hasMany(TherapySession::class, 'therapist_id');
    }

    public function assignedResidents(): HasMany
    {
        return $this->hasMany(TherapistAssignment::class, 'therapist_id')->where('status', 'active');
    }

    // Signature relationships and methods

    public function signatures(): HasMany
    {
        return $this->hasMany(Signature::class);
    }

    public function hasSignature(): bool
    {
        return $this->signatures()->exists() || $this->signature_data !== null;
    }

    public function getSignatureDataUri(): ?string
    {
        // Prefer the active signature from the new signatures table
        $active = $this->signatures()->active()->first();
        if ($active) {
            return $active->getDataUri();
        }

        // Fall back to legacy signature_data column
        if (! $this->signature_data) {
            return null;
        }

        $data = $this->signature_data;

        if (str_starts_with($data, 'data:image/')) {
            return $data;
        }

        return 'data:image/png;base64,'.$data;
    }

    /**
     * Get dashboard widgets based on user's roles
     */
    public function getDashboardWidgets(): array
    {
        $widgets = [];
        $roles = $this->roles->pluck('name')->toArray();

        if (in_array('system_admin', $roles)) {
            $widgets = array_merge($widgets, ['user-stats', 'system-alerts', 'audit-log']);
        }

        if (in_array('care_home_manager', $roles)) {
            $widgets = array_merge($widgets, ['resident-overview', 'staff-on-duty', 'compliance-status']);
        }

        if (in_array('nurse', $roles)) {
            $widgets = array_merge($widgets, ['my-residents', 'medication-schedule', 'clinical-alerts']);
        }

        if (in_array('caregiver', $roles)) {
            $widgets = array_merge($widgets, ['assigned-residents', 'daily-tasks', 'shift-info']);
        }

        if (in_array('therapist', $roles)) {
            $widgets = array_merge($widgets, ['therapy-sessions-today', 'my-therapy-residents', 'upcoming-sessions']);
        }

        return array_unique($widgets);
    }
}
