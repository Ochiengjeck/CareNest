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
                {{-- Platform Navigation (always visible) --}}
                <flux:sidebar.group
                    :heading="__('Platform')"
                    icon="squares-2x2"
                    expandable
                    :expanded="request()->routeIs('dashboard')"
                    class="grid"
                >
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                {{-- Administration Navigation --}}
                @can('manage-users')
                <flux:sidebar.group
                    :heading="__('Administration')"
                    icon="cog-6-tooth"
                    expandable
                    :expanded="request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.settings.*') || request()->routeIs('admin.logs.*') || request()->routeIs('admin.agencies.*')"
                    class="grid"
                >
                    <flux:sidebar.item icon="users" :href="route('admin.users.index')" :current="request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*')" wire:navigate>
                        {{ __('Users & Roles') }}
                    </flux:sidebar.item>
                    @can('manage-settings')
                    <flux:sidebar.item icon="cog-6-tooth" :href="route('admin.settings.general')" :current="request()->routeIs('admin.settings.*') || request()->routeIs('admin.agencies.*')" wire:navigate>
                        {{ __('System Settings') }}
                    </flux:sidebar.item>
                    @endcan
                    @can('view-audit-logs')
                    <flux:sidebar.item icon="document-text" :href="route('admin.logs.index')" :current="request()->routeIs('admin.logs.*')" wire:navigate>
                        {{ __('Audit Logs') }}
                    </flux:sidebar.item>
                    @endcan
                </flux:sidebar.group>
                @endcan

                {{-- Website Content Navigation --}}
                @can('manage-settings')
                <flux:sidebar.group
                    :heading="__('Website Content')"
                    icon="globe-alt"
                    expandable
                    :expanded="request()->routeIs('admin.website.*')"
                    class="grid"
                >
                    <flux:sidebar.item icon="document-text" :href="route('admin.website.settings')" :current="request()->routeIs('admin.website.settings')" wire:navigate>
                        {{ __('Content Settings') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="chat-bubble-bottom-center-text" :href="route('admin.website.testimonials')" :current="request()->routeIs('admin.website.testimonials')" wire:navigate>
                        {{ __('Testimonials') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="users" :href="route('admin.website.team')" :current="request()->routeIs('admin.website.team')" wire:navigate>
                        {{ __('Team Members') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="question-mark-circle" :href="route('admin.website.faq')" :current="request()->routeIs('admin.website.faq')" wire:navigate>
                        {{ __('FAQ Items') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="photo" :href="route('admin.website.gallery')" :current="request()->routeIs('admin.website.gallery')" wire:navigate>
                        {{ __('Gallery') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="rectangle-stack" :href="route('admin.website.services')" :current="request()->routeIs('admin.website.services')" wire:navigate>
                        {{ __('Services') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="inbox" :href="route('admin.website.contact-submissions')" :current="request()->routeIs('admin.website.contact-submissions')" wire:navigate>
                        {{ __('Contact Submissions') }}
                        @php $newContactCount = \App\Models\ContactSubmission::new()->count(); @endphp
                        @if($newContactCount > 0)
                            <flux:badge color="amber" size="sm">{{ $newContactCount }}</flux:badge>
                        @endif
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @endcan

                {{-- Residents Navigation --}}
                @can('view-residents')
                <flux:sidebar.group
                    :heading="__('Residents')"
                    icon="user-group"
                    expandable
                    :expanded="request()->routeIs('residents.*') || request()->routeIs('care-plans.*')"
                    class="grid"
                >
                    <flux:sidebar.item icon="users" :href="route('residents.index')" :current="request()->routeIs('residents.*')" wire:navigate>
                        {{ __('All Residents') }}
                    </flux:sidebar.item>
                    @can('view-care-plans')
                    <flux:sidebar.item icon="clipboard-document-list" :href="route('care-plans.index')" :current="request()->routeIs('care-plans.*')" wire:navigate>
                        {{ __('Care Plans') }}
                    </flux:sidebar.item>
                    @endcan
                </flux:sidebar.group>
                @endcan

                {{-- Clinical Navigation --}}
                @can('manage-medications')
                <flux:sidebar.group
                    :heading="__('Clinical')"
                    icon="beaker"
                    expandable
                    :expanded="request()->routeIs('medications.*') || request()->routeIs('vitals.*')"
                    class="grid"
                >
                    <flux:sidebar.item icon="beaker" :href="route('medications.index')" :current="request()->routeIs('medications.*')" wire:navigate>
                        {{ __('Medications') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="heart" :href="route('vitals.index')" :current="request()->routeIs('vitals.*')" wire:navigate>
                        {{ __('Vitals & Observations') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @endcan

                {{-- Staff Navigation --}}
                @can('view-staff')
                <flux:sidebar.group
                    :heading="__('Staff')"
                    icon="identification"
                    expandable
                    :expanded="request()->routeIs('staff.*') || request()->routeIs('shifts.*')"
                    class="grid"
                >
                    <flux:sidebar.item icon="identification" :href="route('staff.index')" :current="request()->routeIs('staff.*')" wire:navigate>
                        {{ __('Staff Directory') }}
                    </flux:sidebar.item>
                    @can('manage-staff')
                    <flux:sidebar.item icon="calendar" :href="route('shifts.index')" :current="request()->routeIs('shifts.*')" wire:navigate>
                        {{ __('Shift Schedule') }}
                    </flux:sidebar.item>
                    @endcan
                </flux:sidebar.group>
                @endcan

                {{-- Therapy Navigation --}}
                @canany(['view-therapy', 'conduct-therapy', 'manage-therapy'])
                <flux:sidebar.group
                    :heading="__('Therapy')"
                    icon="heart"
                    expandable
                    :expanded="request()->routeIs('therapy.*')"
                    class="grid"
                >
                    @if(auth()->user()->hasRole('therapist'))
                    <flux:sidebar.item icon="users" :href="route('therapy.my-residents')" :current="request()->routeIs('therapy.my-residents')" wire:navigate>
                        {{ __('My Residents') }}
                    </flux:sidebar.item>
                    @endif
                    @canany(['view-therapy', 'conduct-therapy'])
                    <flux:sidebar.item icon="clipboard-document-list" :href="route('therapy.sessions.index')" :current="request()->routeIs('therapy.sessions.*')" wire:navigate>
                        {{ __('Sessions') }}
                    </flux:sidebar.item>
                    @endcanany
                    @can('manage-therapy')
                    <flux:sidebar.item icon="user-group" :href="route('therapy.therapists.index')" :current="request()->routeIs('therapy.therapists.*')" wire:navigate>
                        {{ __('Therapists') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="arrows-right-left" :href="route('therapy.assignments.index')" :current="request()->routeIs('therapy.assignments.*')" wire:navigate>
                        {{ __('Assignments') }}
                    </flux:sidebar.item>
                    @endcan
                    @can('view-reports')
                    <flux:sidebar.item icon="document-chart-bar" :href="route('therapy.reports.generate')" :current="request()->routeIs('therapy.reports.*')" wire:navigate>
                        {{ __('Generate Reports') }}
                    </flux:sidebar.item>
                    @endcan
                </flux:sidebar.group>
                @endcanany

                {{-- Reports Navigation --}}
                @can('view-reports')
                <flux:sidebar.group
                    :heading="__('Reports')"
                    icon="chart-bar"
                    expandable
                    :expanded="request()->routeIs('reports.*') || request()->routeIs('incidents.*')"
                    class="grid"
                >
                    <flux:sidebar.item icon="chart-bar" :href="route('reports.index')" :current="request()->routeIs('reports.*')" wire:navigate>
                        {{ __('Reports') }}
                    </flux:sidebar.item>
                    @can('manage-incidents')
                    <flux:sidebar.item icon="exclamation-triangle" :href="route('incidents.index')" :current="request()->routeIs('incidents.*')" wire:navigate>
                        {{ __('Incidents') }}
                    </flux:sidebar.item>
                    @endcan
                </flux:sidebar.group>
                @endcan

                {{-- Mentorship Platform --}}
                <flux:separator class="my-2" />
                <flux:sidebar.item
                    icon="academic-cap"
                    :href="route('mentorship.dashboard')"
                    :current="request()->routeIs('mentorship.*')"
                    wire:navigate
                    class="bg-gradient-to-r from-indigo-500/10 to-purple-500/10 dark:from-indigo-500/20 dark:to-purple-500/20 rounded-lg"
                >
                    {{ __('Mentorship Platform') }}
                </flux:sidebar.item>
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
                            data-test="logout-button"
                        >
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        {{-- Floating AI Chatbot --}}
        @persist('floating-chatbot')
        <livewire:floating-chatbot />
        @endpersist

        @fluxScripts
    </body>
</html>
