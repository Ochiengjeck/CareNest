<?php

namespace App\Listeners;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class LogAuthenticationEvents
{
    public function handleLogin(Login $event): void
    {
        $this->log($event->user, 'login', 'User logged in');
    }

    public function handleLogout(Logout $event): void
    {
        if ($event->user) {
            $this->log($event->user, 'logout', 'User logged out');
        }
    }

    public function handleFailed(Failed $event): void
    {
        $request = request();
        $email = $event->credentials['email'] ?? 'unknown';

        AuditLog::create([
            'user_id' => null,
            'action' => 'login_failed',
            'auditable_type' => User::class,
            'auditable_id' => null,
            'old_values' => null,
            'new_values' => ['email' => $email],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'description' => "Failed login attempt for '{$email}'",
        ]);
    }

    protected function log($user, string $action, string $description): void
    {
        $request = request();

        AuditLog::create([
            'user_id' => $user->id,
            'action' => $action,
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'old_values' => null,
            'new_values' => null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'description' => $description,
        ]);
    }
}
