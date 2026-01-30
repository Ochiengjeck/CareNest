<?php

namespace App\Providers;

use App\Listeners\LogAuthenticationEvents;
use App\Models\CarePlan;
use App\Models\Incident;
use App\Models\Medication;
use App\Models\Resident;
use App\Models\User;
use App\Observers\AuditableObserver;
use App\Services\AI\AiManager;
use App\Services\SettingsService;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SettingsService::class);
        $this->app->singleton(AiManager::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->shareSettings();
        $this->registerAuditObservers();
        $this->registerAuthListeners();
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }

    protected function shareSettings(): void
    {
        try {
            $settings = app(SettingsService::class);
            $systemName = $settings->get('system_name', config('app.name'));
            $systemLogo = $settings->get('logo_path');

            config(['app.name' => $systemName]);

            View::share('systemName', $systemName);
            View::share('systemLogo', $systemLogo);
        } catch (\Exception) {
            // Table may not exist yet during migrations
        }
    }

    protected function registerAuditObservers(): void
    {
        try {
            $observer = new AuditableObserver;

            User::observe($observer);
            Resident::observe($observer);
            CarePlan::observe($observer);
            Medication::observe($observer);
            Incident::observe($observer);
        } catch (\Exception) {
            // Table may not exist yet during migrations
        }
    }

    protected function registerAuthListeners(): void
    {
        $listener = new LogAuthenticationEvents;

        Event::listen(Login::class, [$listener, 'handleLogin']);
        Event::listen(Logout::class, [$listener, 'handleLogout']);
        Event::listen(Failed::class, [$listener, 'handleFailed']);
    }
}
