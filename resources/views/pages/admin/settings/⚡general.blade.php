<?php

use App\Concerns\GeneralSettingsValidationRules;
use App\Services\SettingsService;
use App\Services\ThemeService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new
#[Layout('layouts.app.sidebar')]
#[Title('General Settings')]
class extends Component {
    use GeneralSettingsValidationRules, WithFileUploads;

    // General
    public string $system_name = '';
    public string $system_tagline = '';
    public string $timezone = '';
    public string $date_format = '';
    public string $time_format = '';
    public string $language = '';

    // Theme
    public string $active_theme = 'ocean-blue';

    // Branding
    public $logo = null;
    public $favicon = null;
    public string $primary_color = '';
    public string $sidebar_name = '';
    public ?string $current_logo = null;
    public ?string $current_favicon = null;

    // Contact
    public string $address_line_1 = '';
    public string $address_line_2 = '';
    public string $city = '';
    public string $state_province = '';
    public string $postal_code = '';
    public string $country = '';
    public string $phone = '';
    public string $email = '';
    public string $website = '';

    // Social
    public string $facebook_url = '';
    public string $twitter_url = '';
    public string $linkedin_url = '';
    public string $instagram_url = '';

    public function mount(): void
    {
        $settings = app(SettingsService::class);

        $this->system_name = $settings->get('system_name', 'CareNest') ?? '';
        $this->system_tagline = $settings->get('system_tagline', '') ?? '';
        $this->timezone = $settings->get('timezone', 'UTC') ?? 'UTC';
        $this->date_format = $settings->get('date_format', 'M d, Y') ?? 'M d, Y';
        $this->time_format = $settings->get('time_format', 'h:i A') ?? 'h:i A';
        $this->language = $settings->get('language', 'en') ?? 'en';

        $this->active_theme = $settings->get('active_theme', 'ocean-blue') ?? 'ocean-blue';
        $this->primary_color = $settings->get('primary_color', '#2872A1') ?? '#2872A1';
        $this->sidebar_name = $settings->get('sidebar_name', 'CareNest') ?? 'CareNest';
        $this->current_logo = $settings->get('logo_path');
        $this->current_favicon = $settings->get('favicon_path');

        foreach (['address_line_1', 'address_line_2', 'city', 'state_province', 'postal_code', 'country', 'phone', 'email', 'website'] as $field) {
            $this->{$field} = $settings->get($field, '') ?? '';
        }

        foreach (['facebook_url', 'twitter_url', 'linkedin_url', 'instagram_url'] as $field) {
            $this->{$field} = $settings->get($field, '') ?? '';
        }
    }

    public function saveGeneral(): void
    {
        $this->validate($this->generalSettingsRules());

        $settings = app(SettingsService::class);
        $settings->setMany([
            'system_name' => $this->system_name,
            'system_tagline' => $this->system_tagline,
            'timezone' => $this->timezone,
            'date_format' => $this->date_format,
            'time_format' => $this->time_format,
            'language' => $this->language,
        ], 'general');

        config(['app.name' => $this->system_name]);

        $this->dispatch('general-saved');
    }

    public function saveTheme(): void
    {
        $this->validate($this->themeRules());

        $settings = app(SettingsService::class);
        $themeService = app(ThemeService::class);
        $themes = $themeService->getThemes();

        $settings->set('active_theme', $this->active_theme, 'branding');

        // Sync primary_color for backwards compatibility
        if (isset($themes[$this->active_theme])) {
            $settings->set('primary_color', $themes[$this->active_theme]['primary'], 'branding');
            $this->primary_color = $themes[$this->active_theme]['primary'];
        }

        $settings->clearCache('active_theme');

        $this->dispatch('theme-saved');
    }

    public function saveBranding(): void
    {
        $this->validate($this->brandingRules());

        $settings = app(SettingsService::class);

        if ($this->logo) {
            $path = $this->logo->store('branding', 'public');
            $settings->set('logo_path', $path, 'branding', 'image');
            $this->current_logo = $path;
            $this->logo = null;
        }

        if ($this->favicon) {
            $path = $this->favicon->store('branding', 'public');
            $settings->set('favicon_path', $path, 'branding', 'image');
            $this->current_favicon = $path;
            $this->favicon = null;
        }

        $settings->setMany([
            'sidebar_name' => $this->sidebar_name,
        ], 'branding');

        $this->dispatch('branding-saved');
    }

    public function removeLogo(): void
    {
        $settings = app(SettingsService::class);

        if ($this->current_logo) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($this->current_logo);
        }

        $settings->set('logo_path', null, 'branding', 'image');
        $this->current_logo = null;
    }

    public function removeFavicon(): void
    {
        $settings = app(SettingsService::class);

        if ($this->current_favicon) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($this->current_favicon);
        }

        $settings->set('favicon_path', null, 'branding', 'image');
        $this->current_favicon = null;
    }

    public function saveContact(): void
    {
        $this->validate($this->contactRules());

        $settings = app(SettingsService::class);
        $settings->setMany([
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'city' => $this->city,
            'state_province' => $this->state_province,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
        ], 'contact');

        $this->dispatch('contact-saved');
    }

    public function saveSocial(): void
    {
        $this->validate($this->socialRules());

        $settings = app(SettingsService::class);
        $settings->setMany([
            'facebook_url' => $this->facebook_url,
            'twitter_url' => $this->twitter_url,
            'linkedin_url' => $this->linkedin_url,
            'instagram_url' => $this->instagram_url,
        ], 'social');

        $this->dispatch('social-saved');
    }
}; ?>

<flux:main>
    <x-pages.admin.settings-layout
        :heading="__('General Settings')"
        :subheading="__('Configure system identity, branding, contact information, and social links')">

        <div class="space-y-8 max-w-3xl">
            {{-- Theme --}}
            <flux:card>
                <flux:heading size="sm" class="mb-4">{{ __('Theme') }}</flux:heading>

                <form wire:submit="saveTheme" class="space-y-4">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Choose a color theme for your system. This affects buttons, links, and accent colors across the interface.') }}
                    </p>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach(app(\App\Services\ThemeService::class)->getThemes() as $slug => $theme)
                            <label
                                class="relative cursor-pointer rounded-xl border-2 p-3 transition-all {{ $active_theme === $slug ? 'border-zinc-900 dark:border-white ring-2 ring-zinc-900 dark:ring-white' : 'border-zinc-200 dark:border-zinc-700 hover:border-zinc-400 dark:hover:border-zinc-500' }}"
                            >
                                <input
                                    type="radio"
                                    wire:model="active_theme"
                                    value="{{ $slug }}"
                                    class="sr-only"
                                />
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="w-6 h-6 rounded-full border border-zinc-300 dark:border-zinc-600" style="background-color: {{ $theme['primary'] }}"></div>
                                    <div class="w-6 h-6 rounded-full border border-zinc-300 dark:border-zinc-600" style="background-color: {{ $theme['secondary'] }}"></div>
                                </div>
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $theme['name'] }}</span>

                                @if($active_theme === $slug)
                                    <div class="absolute top-2 right-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-zinc-900 dark:text-white" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @endif
                            </label>
                        @endforeach
                    </div>

                    @error('active_theme') <flux:error>{{ $message }}</flux:error> @enderror

                    <div class="flex items-center gap-4">
                        <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
                        <x-action-message on="theme-saved">{{ __('Saved.') }}</x-action-message>
                    </div>
                </form>
            </flux:card>

            {{-- System Identity --}}
            <flux:card>
                <flux:heading size="sm" class="mb-4">{{ __('System Identity') }}</flux:heading>

                <form wire:submit="saveGeneral" class="space-y-4">
                    <flux:input wire:model="system_name" :label="__('System Name')" required />
                    <flux:input wire:model="system_tagline" :label="__('Tagline')" :placeholder="__('A short description of your organization')" />

                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:select wire:model="timezone" :label="__('Timezone')">
                            @foreach(timezone_identifiers_list() as $tz)
                                <flux:select.option :value="$tz">{{ $tz }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:select wire:model="language" :label="__('Language')">
                            <flux:select.option value="en">{{ __('English') }}</flux:select.option>
                        </flux:select>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:select wire:model="date_format" :label="__('Date Format')">
                            <flux:select.option value="M d, Y">Jan 29, 2026</flux:select.option>
                            <flux:select.option value="d/m/Y">29/01/2026</flux:select.option>
                            <flux:select.option value="Y-m-d">2026-01-29</flux:select.option>
                            <flux:select.option value="d-m-Y">29-01-2026</flux:select.option>
                            <flux:select.option value="m/d/Y">01/29/2026</flux:select.option>
                        </flux:select>
                        <flux:select wire:model="time_format" :label="__('Time Format')">
                            <flux:select.option value="h:i A">12:30 PM (12-hour)</flux:select.option>
                            <flux:select.option value="H:i">14:30 (24-hour)</flux:select.option>
                        </flux:select>
                    </div>

                    <div class="flex items-center gap-4">
                        <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
                        <x-action-message on="general-saved">{{ __('Saved.') }}</x-action-message>
                    </div>
                </form>
            </flux:card>

            {{-- Branding --}}
            <flux:card>
                <flux:heading size="sm" class="mb-4">{{ __('Branding') }}</flux:heading>

                <form wire:submit="saveBranding" class="space-y-4">
                    <div>
                        <flux:label>{{ __('Logo') }}</flux:label>
                        <div class="mt-2 flex items-center gap-4">
                            @if($current_logo)
                                <div class="relative">
                                    <img src="{{ Storage::url($current_logo) }}" class="h-12 w-auto rounded" alt="Logo" />
                                    <flux:button variant="ghost" size="xs" wire:click="removeLogo" type="button" class="absolute -top-2 -right-2">
                                        &times;
                                    </flux:button>
                                </div>
                            @endif
                            <input type="file" wire:model="logo" accept="image/*" class="text-sm text-zinc-500 file:mr-2 file:rounded file:border-0 file:bg-zinc-100 file:px-3 file:py-1.5 file:text-sm file:text-zinc-700 dark:file:bg-zinc-800 dark:file:text-zinc-300" />
                        </div>
                        @error('logo') <flux:error>{{ $message }}</flux:error> @enderror
                    </div>

                    <div>
                        <flux:label>{{ __('Favicon') }}</flux:label>
                        <div class="mt-2 flex items-center gap-4">
                            @if($current_favicon)
                                <div class="relative">
                                    <img src="{{ Storage::url($current_favicon) }}" class="h-8 w-auto rounded" alt="Favicon" />
                                    <flux:button variant="ghost" size="xs" wire:click="removeFavicon" type="button" class="absolute -top-2 -right-2">
                                        &times;
                                    </flux:button>
                                </div>
                            @endif
                            <input type="file" wire:model="favicon" accept="image/png,image/x-icon,image/svg+xml" class="text-sm text-zinc-500 file:mr-2 file:rounded file:border-0 file:bg-zinc-100 file:px-3 file:py-1.5 file:text-sm file:text-zinc-700 dark:file:bg-zinc-800 dark:file:text-zinc-300" />
                        </div>
                        @error('favicon') <flux:error>{{ $message }}</flux:error> @enderror
                    </div>

                    <flux:input wire:model="sidebar_name" :label="__('Sidebar Display Name')" :placeholder="__('Shown in the sidebar header')" />

                    <div class="flex items-center gap-4">
                        <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
                        <x-action-message on="branding-saved">{{ __('Saved.') }}</x-action-message>
                    </div>
                </form>
            </flux:card>

            {{-- Contact Information --}}
            <flux:card>
                <flux:heading size="sm" class="mb-4">{{ __('Contact Information') }}</flux:heading>

                <form wire:submit="saveContact" class="space-y-4">
                    <flux:input wire:model="address_line_1" :label="__('Address Line 1')" />
                    <flux:input wire:model="address_line_2" :label="__('Address Line 2')" />

                    <div class="grid gap-4 sm:grid-cols-3">
                        <flux:input wire:model="city" :label="__('City')" />
                        <flux:input wire:model="state_province" :label="__('State / Province')" />
                        <flux:input wire:model="postal_code" :label="__('Postal Code')" />
                    </div>

                    <flux:input wire:model="country" :label="__('Country')" />

                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:input wire:model="phone" :label="__('Phone')" type="tel" />
                        <flux:input wire:model="email" :label="__('Contact Email')" type="email" />
                    </div>

                    <flux:input wire:model="website" :label="__('Website')" type="url" placeholder="https://" />

                    <div class="flex items-center gap-4">
                        <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
                        <x-action-message on="contact-saved">{{ __('Saved.') }}</x-action-message>
                    </div>
                </form>
            </flux:card>

            {{-- Social Links --}}
            <flux:card>
                <flux:heading size="sm" class="mb-4">{{ __('Social Links') }}</flux:heading>

                <form wire:submit="saveSocial" class="space-y-4">
                    <flux:input wire:model="facebook_url" :label="__('Facebook')" type="url" placeholder="https://facebook.com/..." />
                    <flux:input wire:model="twitter_url" :label="__('Twitter / X')" type="url" placeholder="https://x.com/..." />
                    <flux:input wire:model="linkedin_url" :label="__('LinkedIn')" type="url" placeholder="https://linkedin.com/..." />
                    <flux:input wire:model="instagram_url" :label="__('Instagram')" type="url" placeholder="https://instagram.com/..." />

                    <div class="flex items-center gap-4">
                        <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
                        <x-action-message on="social-saved">{{ __('Saved.') }}</x-action-message>
                    </div>
                </form>
            </flux:card>
        </div>
    </x-pages.admin.settings-layout>
</flux:main>
