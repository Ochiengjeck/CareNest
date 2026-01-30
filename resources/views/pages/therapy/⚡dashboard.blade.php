<?php

use App\Models\TherapistAssignment;
use App\Models\TherapySession;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Therapy Dashboard')]
class extends Component {
    #[Computed]
    public function todaySessions()
    {
        return TherapySession::query()
            ->forTherapist(auth()->id())
            ->today()
            ->with('resident')
            ->orderBy('start_time')
            ->get();
    }

    #[Computed]
    public function upcomingSessions()
    {
        return TherapySession::query()
            ->forTherapist(auth()->id())
            ->upcoming()
            ->where('session_date', '>', today())
            ->with('resident')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function assignedResidentsCount(): int
    {
        return TherapistAssignment::query()
            ->forTherapist(auth()->id())
            ->active()
            ->count();
    }

    #[Computed]
    public function completedSessionsThisWeek(): int
    {
        return TherapySession::query()
            ->forTherapist(auth()->id())
            ->completed()
            ->whereBetween('session_date', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();
    }

    #[Computed]
    public function pendingDocumentation()
    {
        return TherapySession::query()
            ->forTherapist(auth()->id())
            ->where('status', 'completed')
            ->whereNull('progress_notes')
            ->with('resident')
            ->orderBy('session_date', 'desc')
            ->limit(5)
            ->get();
    }
}; ?>

<flux:main>
    <div class="space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Therapy Dashboard') }}</flux:heading>
            <flux:subheading>{{ __('Welcome back, :name', ['name' => auth()->user()->name]) }}</flux:subheading>
        </div>

        {{-- Quick Stats --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <flux:card>
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                        <flux:icon name="calendar" class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $this->todaySessions->count() }}</div>
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ __("Today's Sessions") }}</div>
                    </div>
                </div>
            </flux:card>

            <flux:card>
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                        <flux:icon name="users" class="h-6 w-6 text-green-600 dark:text-green-400" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $this->assignedResidentsCount }}</div>
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Assigned Residents') }}</div>
                    </div>
                </div>
            </flux:card>

            <flux:card>
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/30">
                        <flux:icon name="check-circle" class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $this->completedSessionsThisWeek }}</div>
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Completed This Week') }}</div>
                    </div>
                </div>
            </flux:card>

            <flux:card>
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/30">
                        <flux:icon name="document-text" class="h-6 w-6 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $this->pendingDocumentation->count() }}</div>
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Pending Documentation') }}</div>
                    </div>
                </div>
            </flux:card>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Today's Sessions --}}
            <flux:card>
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="sm">{{ __("Today's Sessions") }}</flux:heading>
                    <flux:button variant="ghost" size="sm" :href="route('therapy.sessions.create')" wire:navigate icon="plus">
                        {{ __('New Session') }}
                    </flux:button>
                </div>

                @if($this->todaySessions->isEmpty())
                    <div class="py-8 text-center">
                        <flux:icon name="calendar" class="mx-auto h-12 w-12 text-zinc-300 dark:text-zinc-600" />
                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('No sessions scheduled for today') }}</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($this->todaySessions as $session)
                            <div class="flex items-center justify-between rounded-lg border border-zinc-200 dark:border-zinc-700 p-3">
                                <div class="flex items-center gap-3">
                                    <div class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                                        {{ Carbon\Carbon::parse($session->start_time)->format('g:i A') }}
                                    </div>
                                    <div>
                                        <div class="font-medium">{{ $session->resident->full_name }}</div>
                                        <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $session->session_topic }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <flux:badge size="sm" :color="$session->status_color">{{ $session->status_label }}</flux:badge>
                                    @if($session->status === 'completed' && !$session->progress_notes)
                                        <flux:button variant="primary" size="sm" :href="route('therapy.sessions.document', $session)" wire:navigate>
                                            {{ __('Document') }}
                                        </flux:button>
                                    @elseif($session->status === 'scheduled')
                                        <flux:button variant="ghost" size="sm" :href="route('therapy.sessions.show', $session)" wire:navigate icon="eye" />
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </flux:card>

            {{-- Upcoming Sessions --}}
            <flux:card>
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="sm">{{ __('Upcoming Sessions') }}</flux:heading>
                    <flux:button variant="ghost" size="sm" :href="route('therapy.sessions.index')" wire:navigate>
                        {{ __('View All') }}
                    </flux:button>
                </div>

                @if($this->upcomingSessions->isEmpty())
                    <div class="py-8 text-center">
                        <flux:icon name="calendar" class="mx-auto h-12 w-12 text-zinc-300 dark:text-zinc-600" />
                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('No upcoming sessions') }}</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($this->upcomingSessions as $session)
                            <div class="flex items-center justify-between rounded-lg border border-zinc-200 dark:border-zinc-700 p-3">
                                <div>
                                    <div class="font-medium">{{ $session->resident->full_name }}</div>
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $session->session_date->format('M d, Y') }} at {{ Carbon\Carbon::parse($session->start_time)->format('g:i A') }}
                                    </div>
                                </div>
                                <flux:badge size="sm" :color="$session->service_type_color">{{ $session->service_type_label }}</flux:badge>
                            </div>
                        @endforeach
                    </div>
                @endif
            </flux:card>
        </div>

        {{-- Pending Documentation --}}
        @if($this->pendingDocumentation->isNotEmpty())
            <flux:card>
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="sm">{{ __('Pending Documentation') }}</flux:heading>
                </div>

                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Resident') }}</flux:table.column>
                        <flux:table.column>{{ __('Session Date') }}</flux:table.column>
                        <flux:table.column>{{ __('Topic') }}</flux:table.column>
                        <flux:table.column class="w-24"></flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach($this->pendingDocumentation as $session)
                            <flux:table.row :key="$session->id">
                                <flux:table.cell class="font-medium">{{ $session->resident->full_name }}</flux:table.cell>
                                <flux:table.cell>{{ $session->session_date->format('M d, Y') }}</flux:table.cell>
                                <flux:table.cell>{{ Str::limit($session->session_topic, 40) }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:button variant="primary" size="sm" :href="route('therapy.sessions.document', $session)" wire:navigate>
                                        {{ __('Document') }}
                                    </flux:button>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </flux:card>
        @endif

        {{-- Quick Actions --}}
        <div class="flex flex-wrap gap-3">
            <flux:button variant="primary" :href="route('therapy.sessions.create')" wire:navigate icon="plus">
                {{ __('New Session') }}
            </flux:button>
            <flux:button variant="ghost" :href="route('therapy.my-residents')" wire:navigate icon="users">
                {{ __('My Residents') }}
            </flux:button>
            @can('view-reports')
            <flux:button variant="ghost" :href="route('therapy.reports.generate')" wire:navigate icon="document-chart-bar">
                {{ __('Generate Report') }}
            </flux:button>
            @endcan
        </div>
    </div>
</flux:main>
