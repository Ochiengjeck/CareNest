<?php

use App\Services\SettingsService;

if (! function_exists('system_setting')) {
    function system_setting(string $key, mixed $default = null): mixed
    {
        return app(SettingsService::class)->get($key, $default);
    }
}
