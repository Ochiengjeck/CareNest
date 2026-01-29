<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                {{-- Admin Navigation --}}
                @can('manage-users')
                <flux:sidebar.group :heading="__('Administration')" class="grid">
                    <flux:sidebar.item icon="users" :href="route('admin.users.index')" :current="request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*')" wire:navigate>
                        {{ __('Users & Roles') }}
                    </flux:sidebar.item>
                    @can('manage-settings')
                    <flux:sidebar.item icon="cog-6-tooth" :href="route('admin.settings.general')" :current="request()->routeIs('admin.settings.*')" wire:navigate>
                        {{ __('System Settings') }}
                    </flux:sidebar.item>
                    @endcan
                    @can('view-audit-logs')
                    <flux:sidebar.item icon="document-text" href="#" :current="request()->routeIs('admin.logs.*')">
                        {{ __('Audit Logs') }}
                    </flux:sidebar.item>
                    @endcan
                </flux:sidebar.group>
                @endcan

                {{-- Residents Navigation --}}
                @can('view-residents')
                <flux:sidebar.group :heading="__('Residents')" class="grid">
                    <flux:sidebar.item icon="user-group" href="#" :current="request()->routeIs('residents.*')">
                        {{ __('All Residents') }}
                    </flux:sidebar.item>
                    @can('manage-care-plans')
                    <flux:sidebar.item icon="clipboard-document-list" href="#" :current="request()->routeIs('care-plans.*')">
                        {{ __('Care Plans') }}
                    </flux:sidebar.item>
                    @endcan
                </flux:sidebar.group>
                @endcan

                {{-- Clinical Navigation --}}
                @can('manage-medications')
                <flux:sidebar.group :heading="__('Clinical')" class="grid">
                    <flux:sidebar.item icon="beaker" href="#" :current="request()->routeIs('medications.*')">
                        {{ __('Medications') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="heart" href="#" :current="request()->routeIs('vitals.*')">
                        {{ __('Vitals & Observations') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @endcan

                {{-- Staff Navigation --}}
                @can('view-staff')
                <flux:sidebar.group :heading="__('Staff')" class="grid">
                    <flux:sidebar.item icon="identification" href="#" :current="request()->routeIs('staff.*')">
                        {{ __('Staff Directory') }}
                    </flux:sidebar.item>
                    @can('manage-staff')
                    <flux:sidebar.item icon="calendar" href="#" :current="request()->routeIs('shifts.*')">
                        {{ __('Shift Schedule') }}
                    </flux:sidebar.item>
                    @endcan
                </flux:sidebar.group>
                @endcan

                {{-- Reports Navigation --}}
                @can('view-reports')
                <flux:sidebar.group :heading="__('Reports')" class="grid">
                    <flux:sidebar.item icon="chart-bar" href="#" :current="request()->routeIs('reports.*')">
                        {{ __('Reports') }}
                    </flux:sidebar.item>
                    @can('manage-incidents')
                    <flux:sidebar.item icon="exclamation-triangle" href="#" :current="request()->routeIs('incidents.*')">
                        {{ __('Incidents') }}
                    </flux:sidebar.item>
                    @endcan
                </flux:sidebar.group>
                @endcan
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                    {{ __('Repository') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                    {{ __('Documentation') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>


        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
