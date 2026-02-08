<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <a href="{{ route('mentorship.dashboard') }}" wire:navigate class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600">
                        <flux:icon.academic-cap class="size-6 text-white" />
                    </div>
                    <div>
                        <div class="font-bold text-zinc-900 dark:text-white">{{ __('Mentorship') }}</div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ system_setting('system_name', 'CareNest') }}</div>
                    </div>
                </a>
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                {{-- Main Navigation --}}
                <flux:sidebar.group
                    :heading="__('Mentoring')"
                    icon="academic-cap"
                    expandable
                    :expanded="true"
                    class="grid"
                >
                    <flux:sidebar.item icon="home" :href="route('mentorship.dashboard')" :current="request()->routeIs('mentorship.dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="calendar" :href="route('mentorship.topics.week')" :current="request()->routeIs('mentorship.topics.week')" wire:navigate>
                        {{ __('Weekly Topics') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="presentation-chart-bar" :href="route('mentorship.sessions.index')" :current="request()->routeIs('mentorship.sessions.*')" wire:navigate>
                        {{ __('My Sessions') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                {{-- Management Navigation --}}
                @can('manage-mentorship')
                <flux:sidebar.group
                    :heading="__('Management')"
                    icon="cog-6-tooth"
                    expandable
                    :expanded="request()->routeIs('mentorship.topics.index') || request()->routeIs('mentorship.topics.create') || request()->routeIs('mentorship.topics.edit') || request()->routeIs('mentorship.import.*') || request()->routeIs('mentorship.lessons.*') || request()->routeIs('mentorship.settings.*') || request()->routeIs('mentorship.reports.*')"
                    class="grid"
                >
                    <flux:sidebar.item icon="clipboard-document-list" :href="route('mentorship.topics.index')" :current="request()->routeIs('mentorship.topics.index')" wire:navigate>
                        {{ __('Manage Topics') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="plus-circle" :href="route('mentorship.topics.create')" :current="request()->routeIs('mentorship.topics.create')" wire:navigate>
                        {{ __('Add Topic') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="arrow-up-tray" :href="route('mentorship.import.csv')" :current="request()->routeIs('mentorship.import.*')" wire:navigate>
                        {{ __('Import CSV') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="book-open" :href="route('mentorship.lessons.index')" :current="request()->routeIs('mentorship.lessons.*')" wire:navigate>
                        {{ __('Lessons Library') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="chart-bar" :href="route('mentorship.reports.index')" :current="request()->routeIs('mentorship.reports.*')" wire:navigate>
                        {{ __('Reports') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="cpu-chip" :href="route('mentorship.settings.ai')" :current="request()->routeIs('mentorship.settings.ai')" wire:navigate>
                        {{ __('AI Settings') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @endcan

                {{-- Quick Actions --}}
                <flux:sidebar.group
                    :heading="__('Navigation')"
                    icon="arrows-right-left"
                    expandable
                    class="grid"
                >
                    <flux:sidebar.item icon="arrow-left" :href="route('dashboard')" wire:navigate>
                        {{ __('Back to CareNest') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

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
                        >
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        {{-- AI Mentor Chat --}}
        <x-mentorship.ai-mentor-chat />

        @fluxScripts
        @stack('scripts')
    </body>
</html>
