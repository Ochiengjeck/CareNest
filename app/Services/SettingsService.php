<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class SettingsService
{
    private const CACHE_PREFIX = 'system_setting:';

    private const CACHE_TTL = 3600;

    public function get(string $key, mixed $default = null): mixed
    {
        $setting = Cache::remember(
            self::CACHE_PREFIX.$key,
            self::CACHE_TTL,
            fn () => SystemSetting::byKey($key)->first()
        );

        if (! $setting) {
            return $default;
        }

        $value = $setting->is_encrypted ? $setting->decrypted_value : $setting->value;

        return $this->castValue($value, $setting->type) ?? $default;
    }

    public function getGroup(string $group): array
    {
        return Cache::remember(
            self::CACHE_PREFIX.'group:'.$group,
            self::CACHE_TTL,
            function () use ($group) {
                return SystemSetting::group($group)->get()
                    ->mapWithKeys(fn ($s) => [
                        $s->key => $this->castValue(
                            $s->is_encrypted ? $s->decrypted_value : $s->value,
                            $s->type
                        ),
                    ])
                    ->toArray();
            }
        );
    }

    public function set(string $key, mixed $value, ?string $group = null, ?string $type = null): void
    {
        $setting = SystemSetting::byKey($key)->first();

        $storeValue = is_array($value) || is_object($value)
            ? json_encode($value)
            : ($value === null ? null : (string) $value);

        if ($setting) {
            if ($setting->is_encrypted && $storeValue !== null && $storeValue !== '') {
                $storeValue = Crypt::encryptString($storeValue);
            }
            $setting->update(array_filter([
                'value' => $storeValue,
                'type' => $type,
            ], fn ($v) => $v !== null));
        } else {
            $isEncrypted = in_array($key, ['groq_api_key', 'gemini_api_key']);
            if ($isEncrypted && $storeValue !== null && $storeValue !== '') {
                $storeValue = Crypt::encryptString($storeValue);
            }
            SystemSetting::create([
                'key' => $key,
                'group' => $group ?? 'general',
                'value' => $storeValue,
                'is_encrypted' => $isEncrypted,
                'type' => $type ?? 'string',
            ]);
        }

        $this->clearCache($key);
    }

    public function setMany(array $settings, string $group): void
    {
        foreach ($settings as $key => $value) {
            $this->set($key, $value, $group);
        }
        $this->clearGroupCache($group);
    }

    private function castValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'json' => json_decode($value, true),
            'image', 'string' => $value,
            default => $value,
        };
    }

    public function clearCache(string $key): void
    {
        Cache::forget(self::CACHE_PREFIX.$key);

        // Also clear the group cache for this key's group
        $setting = SystemSetting::byKey($key)->first();
        if ($setting) {
            Cache::forget(self::CACHE_PREFIX.'group:'.$setting->group);
        }
    }

    public function clearGroupCache(string $group): void
    {
        Cache::forget(self::CACHE_PREFIX.'group:'.$group);

        SystemSetting::group($group)->pluck('key')->each(
            fn ($key) => Cache::forget(self::CACHE_PREFIX.$key)
        );
    }

    public function clearAllCache(): void
    {
        SystemSetting::pluck('key')->each(
            fn ($key) => Cache::forget(self::CACHE_PREFIX.$key)
        );

        foreach (['general', 'branding', 'contact', 'social', 'ai'] as $group) {
            Cache::forget(self::CACHE_PREFIX.'group:'.$group);
        }
    }
}
