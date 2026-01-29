<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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

        return array_unique($widgets);
    }
}
