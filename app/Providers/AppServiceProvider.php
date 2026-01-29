<?php

namespace App\Providers;

use App\Services\AI\AiManager;
use App\Services\SettingsService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
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
}
