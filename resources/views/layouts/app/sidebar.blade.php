<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-slate-50 antialiased dark:bg-zinc-800">

        {{-- ══════════════════════════════════════════════════════
             SIDEBAR
        ══════════════════════════════════════════════════════ --}}
        <flux:sidebar
            sticky
            collapsible="true"
            class="cn-clean-sidebar"
        >
            {{-- Logo / Brand --}}
            <flux:sidebar.header class="cn-sidebar-logo-divider">
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse />
            </flux:sidebar.header>

            {{-- User profile strip --}}
            <div class="cn-sidebar-profile px-3 pb-2 pt-1">
                <div class="flex items-center gap-2.5">
                    <flux:avatar
                        :name="auth()->user()->name"
                        :initials="auth()->user()->initials()"
                        class="size-8 shrink-0"
                    />
                    <div class="cn-sidebar-profile-text min-w-0 flex-1">
                        <p class="truncate text-xs font-semibold text-slate-800 dark:text-zinc-100">{{ auth()->user()->name }}</p>
                        <p class="truncate text-[10px] text-slate-400 dark:text-zinc-500">
                            {{ implode(', ', array_map(fn($r) => str_replace('_', ' ', ucwords($r, '_')), auth()->user()->getRoleNames()->toArray())) }}
                        </p>
                    </div>
                </div>
            </div>

            <flux:sidebar.nav>

                {{-- ── Platform ── --}}
                <flux:sidebar.group
                    :heading="__('Platform')"
                    icon="squares-2x2"
                    expandable
                    :expanded="request()->routeIs('dashboard')"
                    class="grid"
                >
                    <flux:sidebar.item
                        icon="home"
                        :href="route('dashboard')"
                        :current="request()->routeIs('dashboard')"
                        wire:navigate
                    >
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                {{-- ── Administration ── --}}
                @can('manage-users')
                <flux:sidebar.group
                    :heading="__('Administration')"
                    icon="cog-6-tooth"
                    expandable
                    :expanded="request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.settings.*') || request()->routeIs('admin.logs.*') || request()->routeIs('admin.agencies.*')"
                    class="grid"
                >
                    <flux:sidebar.item
                        icon="users"
                        :href="route('admin.users.index')"
                        :current="request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*')"
                        wire:navigate
                    >
                        {{ __('Users & Roles') }}
                    </flux:sidebar.item>
                    @can('manage-settings')
                    <flux:sidebar.item
                        icon="cog-6-tooth"
                        :href="route('admin.settings.general')"
                        :current="request()->routeIs('admin.settings.*') || request()->routeIs('admin.agencies.*')"
                        wire:navigate
                    >
                        {{ __('System Settings') }}
                    </flux:sidebar.item>
                    @endcan
                    @can('view-audit-logs')
                    <flux:sidebar.item
                        icon="document-text"
                        :href="route('admin.logs.index')"
                        :current="request()->routeIs('admin.logs.*')"
                        wire:navigate
                    >
                        {{ __('Audit Logs') }}
                    </flux:sidebar.item>
                    @endcan
                </flux:sidebar.group>
                @endcan

                {{-- ── Website Content ── --}}
                @can('manage-settings')
                <flux:sidebar.group
                    :heading="__('Website')"
                    icon="globe-alt"
                    expandable
                    :expanded="request()->routeIs('admin.website.*')"
                    class="grid"
                >
                    <flux:sidebar.item icon="document-text"                :href="route('admin.website.settings')"            :current="request()->routeIs('admin.website.settings')"            wire:navigate>{{ __('Content Settings') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="chat-bubble-bottom-center-text" :href="route('admin.website.testimonials')"         :current="request()->routeIs('admin.website.testimonials')"         wire:navigate>{{ __('Testimonials') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="users"                         :href="route('admin.website.team')"                :current="request()->routeIs('admin.website.team')"                wire:navigate>{{ __('Team Members') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="question-mark-circle"          :href="route('admin.website.faq')"                 :current="request()->routeIs('admin.website.faq')"                 wire:navigate>{{ __('FAQ Items') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="photo"                         :href="route('admin.website.gallery')"             :current="request()->routeIs('admin.website.gallery')"             wire:navigate>{{ __('Gallery') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="rectangle-stack"               :href="route('admin.website.services')"            :current="request()->routeIs('admin.website.services')"            wire:navigate>{{ __('Services') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="inbox"                         :href="route('admin.website.contact-submissions')" :current="request()->routeIs('admin.website.contact-submissions')" wire:navigate>
                        {{ __('Contact') }}
                        @php $newContactCount = \App\Models\ContactSubmission::new()->count(); @endphp
                        @if($newContactCount > 0)
                            <flux:badge color="amber" size="sm">{{ $newContactCount }}</flux:badge>
                        @endif
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @endcan

                {{-- ── Residents ── --}}
                @can('view-residents')
                <flux:sidebar.group
                    :heading="__('Residents')"
                    icon="user-group"
                    expandable
                    :expanded="request()->routeIs('residents.*') || request()->routeIs('care-plans.*')"
                    class="grid"
                >
                    <flux:sidebar.item
                        icon="users"
                        :href="route('residents.index')"
                        :current="request()->routeIs('residents.*')"
                        wire:navigate
                    >
                        {{ __('All Residents') }}
                    </flux:sidebar.item>
                    @can('view-care-plans')
                    <flux:sidebar.item
                        icon="clipboard-document-list"
                        :href="route('care-plans.index')"
                        :current="request()->routeIs('care-plans.*')"
                        wire:navigate
                    >
                        {{ __('Care Plans') }}
                    </flux:sidebar.item>
                    @endcan
                </flux:sidebar.group>
                @endcan

                {{-- ── Clinical ── --}}
                @can('manage-medications')
                <flux:sidebar.group
                    :heading="__('Clinical')"
                    icon="beaker"
                    expandable
                    :expanded="request()->routeIs('medications.*') || request()->routeIs('vitals.*')"
                    class="grid"
                >
                    <flux:sidebar.item
                        icon="beaker"
                        :href="route('medications.index')"
                        :current="request()->routeIs('medications.*')"
                        wire:navigate
                    >
                        {{ __('Medications') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item
                        icon="heart"
                        :href="route('vitals.index')"
                        :current="request()->routeIs('vitals.*')"
                        wire:navigate
                    >
                        {{ __('Vitals & Observations') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @endcan

                {{-- ── Staff ── --}}
                @can('view-staff')
                <flux:sidebar.group
                    :heading="__('Staff')"
                    icon="identification"
                    expandable
                    :expanded="request()->routeIs('staff.*') || request()->routeIs('shifts.*')"
                    class="grid"
                >
                    <flux:sidebar.item
                        icon="identification"
                        :href="route('staff.index')"
                        :current="request()->routeIs('staff.*')"
                        wire:navigate
                    >
                        {{ __('Staff Directory') }}
                    </flux:sidebar.item>
                    @can('manage-staff')
                    <flux:sidebar.item
                        icon="calendar"
                        :href="route('shifts.index')"
                        :current="request()->routeIs('shifts.*')"
                        wire:navigate
                    >
                        {{ __('Shift Schedule') }}
                    </flux:sidebar.item>
                    @endcan
                </flux:sidebar.group>
                @endcan

                {{-- ── Therapy ── --}}
                @canany(['view-therapy', 'conduct-therapy', 'manage-therapy'])
                <flux:sidebar.group
                    :heading="__('Therapy')"
                    icon="heart"
                    expandable
                    :expanded="request()->routeIs('therapy.*')"
                    class="grid"
                >
                    @if(auth()->user()->hasRole('therapist'))
                    <flux:sidebar.item icon="users" :href="route('therapy.my-residents')" :current="request()->routeIs('therapy.my-residents')" wire:navigate>{{ __('My Residents') }}</flux:sidebar.item>
                    @endif
                    @canany(['view-therapy', 'conduct-therapy'])
                    <flux:sidebar.item icon="clipboard-document-list" :href="route('therapy.sessions.index')" :current="request()->routeIs('therapy.sessions.*')" wire:navigate>{{ __('Sessions') }}</flux:sidebar.item>
                    @endcanany
                    @can('manage-therapy')
                    <flux:sidebar.item icon="user-group"        :href="route('therapy.therapists.index')"  :current="request()->routeIs('therapy.therapists.*')"  wire:navigate>{{ __('Therapists') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="arrows-right-left" :href="route('therapy.assignments.index')" :current="request()->routeIs('therapy.assignments.*')" wire:navigate>{{ __('Assignments') }}</flux:sidebar.item>
                    @endcan
                    @can('view-reports')
                    <flux:sidebar.item icon="document-chart-bar" :href="route('therapy.reports.generate')" :current="request()->routeIs('therapy.reports.*')" wire:navigate>{{ __('Generate Reports') }}</flux:sidebar.item>
                    @endcan
                </flux:sidebar.group>
                @endcanany

                {{-- ── Reports ── --}}
                @can('view-reports')
                <flux:sidebar.group
                    :heading="__('Reports')"
                    icon="chart-bar"
                    expandable
                    :expanded="request()->routeIs('reports.*') || request()->routeIs('incidents.*')"
                    class="grid"
                >
                    <flux:sidebar.item icon="chart-bar" :href="route('reports.index')" :current="request()->routeIs('reports.*')" wire:navigate>{{ __('Reports') }}</flux:sidebar.item>
                    @can('manage-incidents')
                    <flux:sidebar.item icon="exclamation-triangle" :href="route('incidents.index')" :current="request()->routeIs('incidents.*')" wire:navigate>{{ __('Incidents') }}</flux:sidebar.item>
                    @endcan
                </flux:sidebar.group>
                @endcan

                {{-- ── Mentorship ── --}}
                <div class="px-1 pt-1">
                    <flux:separator class="my-2 opacity-30" />
                </div>
                <flux:sidebar.item
                    icon="academic-cap"
                    :href="route('mentorship.dashboard')"
                    :current="request()->routeIs('mentorship.*')"
                    wire:navigate
                    class="cn-mentorship-item mx-1 mb-1"
                >
                    <span class="font-semibold tracking-tight">{{ __('Mentorship') }}</span>
                    <flux:badge size="sm" color="blue" class="ml-auto opacity-80">{{ __('Platform') }}</flux:badge>
                </flux:sidebar.item>

            </flux:sidebar.nav>

            <flux:spacer />

            {{-- Appearance toggle — uses $flux.appearance which writes to "flux.appearance" key --}}
            <div class="cn-sidebar-footer border-t px-2 py-1">
                <button
                    x-data
                    @click="$flux.appearance = $flux.appearance === 'dark' ? 'light' : 'dark'"
                    class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm transition"
                >
                    <flux:icon name="moon" variant="outline" class="size-4 shrink-0" x-show="$flux.appearance !== 'dark'" />
                    <flux:icon name="sun"  variant="outline" class="size-4 shrink-0" x-show="$flux.appearance === 'dark'" />
                    <span class="in-data-flux-sidebar-collapsed-desktop:hidden truncate">
                        <span x-show="$flux.appearance !== 'dark'">Switch to Dark</span>
                        <span x-show="$flux.appearance === 'dark'">Switch to Light</span>
                    </span>
                </button>
            </div>

            {{-- Desktop user menu --}}
            <div class="cn-sidebar-footer border-t pt-1">
                <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
            </div>
        </flux:sidebar>


        {{-- ══════════════════════════════════════════════════════
             MOBILE HEADER
        ══════════════════════════════════════════════════════ --}}
        <flux:header class="border-b border-slate-200/80 bg-white/90 backdrop-blur-xl dark:border-white/[0.06] dark:bg-zinc-900/80 lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />
                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="flex items-center gap-2 px-3 py-2 text-sm">
                            <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />
                            <div class="grid flex-1 leading-tight">
                                <flux:heading class="truncate text-sm">{{ auth()->user()->name }}</flux:heading>
                                <flux:text class="truncate text-xs">{{ auth()->user()->email }}</flux:text>
                            </div>
                        </div>
                    </flux:menu.radio.group>
                    <flux:menu.separator />
                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>
                    <flux:menu.separator />
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full cursor-pointer" data-test="logout-button">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>


        {{-- ══════════════════════════════════════════════════════
             MAIN CONTENT
        ══════════════════════════════════════════════════════ --}}
        {{ $slot }}

        {{-- Floating AI Chatbot --}}
        @persist('floating-chatbot')
        <livewire:floating-chatbot />
        @endpersist

        @fluxScripts
    </body>
</html>
