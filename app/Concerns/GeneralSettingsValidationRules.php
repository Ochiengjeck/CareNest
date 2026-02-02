<?php

namespace App\Concerns;

trait GeneralSettingsValidationRules
{
    protected function generalSettingsRules(): array
    {
        return [
            'system_name' => ['required', 'string', 'max:255'],
            'system_tagline' => ['nullable', 'string', 'max:500'],
            'timezone' => ['required', 'string', 'timezone:all'],
            'date_format' => ['required', 'string', 'in:M d, Y,d/m/Y,Y-m-d,d-m-Y,m/d/Y'],
            'time_format' => ['required', 'string', 'in:h:i A,H:i'],
            'language' => ['required', 'string', 'in:en'],
        ];
    }

    protected function themeRules(): array
    {
        return [
            'active_theme' => ['required', 'string', 'in:ocean-blue,soft-sage,deep-burgundy,vibrant-orange,blush-peach,pale-cream'],
        ];
    }

    protected function brandingRules(): array
    {
        return [
            'logo' => ['nullable', 'image', 'mimes:svg,png,jpg,jpeg,webp', 'max:1024'],
            'favicon' => ['nullable', 'image', 'mimes:png,ico,svg', 'max:512'],
            'sidebar_name' => ['nullable', 'string', 'max:50'],
        ];
    }

    protected function contactRules(): array
    {
        return [
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state_province' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
        ];
    }

    protected function socialRules(): array
    {
        return [
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'twitter_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
        ];
    }
}
