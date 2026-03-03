@php
    $user = Auth::user();
    $primaryRole = $user->getPrimaryRole();
    $roleLabel = $primaryRole
        ? Str::of($primaryRole)->replace('_', ' ')->title()->toString()
        : null;
@endphp

{{-- User hero strip --}}
<div class="mb-6 flex items-center gap-5 rounded-xl border border-zinc-200 bg-gradient-to-r from-zinc-50 to-white px-6 py-5 dark:border-zinc-700 dark:from-zinc-800/50 dark:to-zinc-900/30">
    <div class="flex size-14 shrink-0 items-center justify-center rounded-full bg-accent/10 ring-2 ring-accent/20">
        <span class="select-none text-xl font-bold uppercase tracking-wide text-accent">{{ $user->initials() }}</span>
    </div>
    <div class="min-w-0 flex-1">
        <flux:heading size="lg" class="truncate">{{ $user->name }}</flux:heading>
        <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1">
            @if ($roleLabel)
                <flux:badge size="sm" color="zinc">{{ $roleLabel }}</flux:badge>
            @endif
            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ __('Manage your account settings') }}</flux:text>
        </div>
    </div>
</div>

{{-- Horizontal tab navigation --}}
<div class="mb-6 flex gap-0 overflow-x-auto border-b border-zinc-200 dark:border-zinc-700">
    <a
        href="{{ route('profile.edit') }}"
        wire:navigate
        @class([
            'flex shrink-0 items-center gap-1.5 whitespace-nowrap border-b-2 px-4 py-2.5 text-sm font-medium transition-colors -mb-px',
            'border-accent text-accent' => request()->routeIs('profile.edit'),
            'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-200' => ! request()->routeIs('profile.edit'),
        ])
    >
        <flux:icon.user class="size-4" />
        {{ __('Profile') }}
    </a>
    <a
        href="{{ route('user-password.edit') }}"
        wire:navigate
        @class([
            'flex shrink-0 items-center gap-1.5 whitespace-nowrap border-b-2 px-4 py-2.5 text-sm font-medium transition-colors -mb-px',
            'border-accent text-accent' => request()->routeIs('user-password.edit'),
            'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-200' => ! request()->routeIs('user-password.edit'),
        ])
    >
        <flux:icon.lock-closed class="size-4" />
        {{ __('Password') }}
    </a>
    @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
        <a
            href="{{ route('two-factor.show') }}"
            wire:navigate
            @class([
                'flex shrink-0 items-center gap-1.5 whitespace-nowrap border-b-2 px-4 py-2.5 text-sm font-medium transition-colors -mb-px',
                'border-accent text-accent' => request()->routeIs('two-factor.show'),
                'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-200' => ! request()->routeIs('two-factor.show'),
            ])
        >
            <flux:icon.shield-check class="size-4" />
            {{ __('Two-Factor') }}
        </a>
    @endif
    <a
        href="{{ route('appearance.edit') }}"
        wire:navigate
        @class([
            'flex shrink-0 items-center gap-1.5 whitespace-nowrap border-b-2 px-4 py-2.5 text-sm font-medium transition-colors -mb-px',
            'border-accent text-accent' => request()->routeIs('appearance.edit'),
            'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:text-zinc-200' => ! request()->routeIs('appearance.edit'),
        ])
    >
        <flux:icon.paint-brush class="size-4" />
        {{ __('Appearance') }}
    </a>
</div>
