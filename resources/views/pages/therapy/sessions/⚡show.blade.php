<?php

use App\Models\TherapySession;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Session Details')]
class extends Component {
    #[Locked]
    public int $sessionId;

    public function mount(TherapySession $session): void
    {
        $this->sessionId = $session->id;
    }

    #[Computed]
    public function session(): TherapySession
    {
        return TherapySession::with(['therapist', 'resident', 'supervisor', 'creator'])->findOrFail($this->sessionId);
    }

    public function markCompleted(): void
    {
        $this->session->update(['status' => 'completed']);
        $this->redirect(route('therapy.sessions.document', $this->session), navigate: true);
    }

    public function markCancelled(): void
    {
        $this->session->update(['status' => 'cancelled']);
        $this->dispatch('session-cancelled');
    }

    public function markNoShow(): void
    {
        $this->session->update(['status' => 'no_show']);
        $this->dispatch('session-no-show');
    }
}; ?>

<flux:main>
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Back Button --}}
        <flux:button variant="ghost" size="sm" :href="route('therapy.sessions.index')" wire:navigate icon="arrow-left">
            {{ __('Back to Sessions') }}
        </flux:button>

        {{-- Session Header Card --}}
        <flux:card>
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        <flux:badge :color="$this->session->status_color" size="sm">
                            {{ $this->session->status_label }}
                        </flux:badge>
                        <flux:badge :color="$this->session->service_type_color" size="sm">
                            {{ $this->session->service_type_label }}
                        </flux:badge>
                    </div>

                    <flux:heading size="xl" class="mb-2">{{ __('Session Details') }}</flux:heading>

                    <div class="flex flex-wrap gap-4 text-sm text-zinc-600 dark:text-zinc-400">
                        <div class="flex items-center gap-1">
                            <flux:icon.calendar class="size-4" />
                            {{ $this->session->session_date->format('l, F d, Y') }}
                        </div>
                        <div class="flex items-center gap-1">
                            <flux:icon.clock class="size-4" />
                            {{ $this->session->formatted_time_range }}
                        </div>
                        <div class="flex items-center gap-1">
                            <flux:icon.arrow-path class="size-4" />
                            {{ $this->session->duration_minutes }} {{ __('min') }}
                        </div>
                        <div class="flex items-center gap-1">
                            <flux:icon.user class="size-4" />
                            {{ $this->session->therapist->name }}
                        </div>
                        <div class="flex items-center gap-1">
                            <flux:icon.user-circle class="size-4" />
                            {{ $this->session->resident->full_name }}
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @can('manage-therapy')
                        <flux:button variant="ghost" :href="route('therapy.sessions.edit', $this->session)" wire:navigate icon="pencil">
                            {{ __('Edit') }}
                        </flux:button>
                    @endcan
                </div>
            </div>
        </flux:card>

        {{-- Quick Actions --}}
        @if($this->session->status === 'scheduled' && ($this->session->therapist_id === auth()->id() || auth()->user()->can('manage-therapy')))
            <flux:card class="bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 border-blue-200 dark:border-blue-800">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <flux:heading size="sm">{{ __('Session Actions') }}</flux:heading>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Update the status of this session') }}</p>
                    </div>
                    <div class="flex gap-2">
                        <flux:button variant="primary" wire:click="markCompleted" icon="check">
                            {{ __('Mark Completed') }}
                        </flux:button>
                        <flux:button variant="ghost" wire:click="markCancelled">
                            {{ __('Cancel') }}
                        </flux:button>
                        <flux:button variant="ghost" wire:click="markNoShow">
                            {{ __('No Show') }}
                        </flux:button>
                    </div>
                </div>
            </flux:card>
        @elseif($this->session->status === 'completed' && !$this->session->progress_notes && ($this->session->therapist_id === auth()->id() || auth()->user()->can('manage-therapy')))
            <flux:card class="bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <flux:heading size="sm">{{ __('Documentation Required') }}</flux:heading>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('This session needs to be documented') }}</p>
                    </div>
                    <flux:button variant="primary" :href="route('therapy.sessions.document', $this->session)" wire:navigate icon="document-text">
                        {{ __('Document Session') }}
                    </flux:button>
                </div>
            </flux:card>
        @endif

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Session Information --}}
            <flux:card>
                <flux:heading size="sm" class="mb-4">{{ __('Session Information') }}</flux:heading>

                <dl class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">{{ __('Date') }}</dt>
                        <dd class="mt-1 font-medium">{{ $this->session->session_date->format('F d, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">{{ __('Time') }}</dt>
                        <dd class="mt-1 font-medium">{{ $this->session->formatted_time_range }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">{{ __('Duration') }}</dt>
                        <dd class="mt-1 font-medium">{{ $this->session->duration_minutes }} {{ __('minutes') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">{{ __('Service Type') }}</dt>
                        <dd class="mt-1">
                            <flux:badge :color="$this->session->service_type_color">{{ $this->session->service_type_label }}</flux:badge>
                        </dd>
                    </div>
                    @if($this->session->modality)
                    <div>
                        <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">{{ __('Therapy Modality') }}</dt>
                        <dd class="mt-1"><flux:badge color="cyan">{{ $this->session->modality_label }}</flux:badge></dd>
                    </div>
                    @endif
                    @if($this->session->challenge_index)
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">{{ __('Treatment Plan Index') }}</dt>
                        <dd class="mt-1 font-medium">{{ $this->session->challenge_label }}</dd>
                    </div>
                    @endif
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">{{ __('Session Topic') }}</dt>
                        <dd class="mt-1 font-medium">{{ $this->session->session_topic }}</dd>
                    </div>
                </dl>
            </flux:card>

            {{-- Participants --}}
            <flux:card>
                <flux:heading size="sm" class="mb-4">{{ __('Participants') }}</flux:heading>

                <div class="space-y-4">
                    <div>
                        <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide mb-2">{{ __('Therapist (BHT)') }}</div>
                        <div class="flex items-center gap-3">
                            <flux:avatar :name="$this->session->therapist->name" />
                            <div>
                                <div class="font-medium">{{ $this->session->therapist->name }}</div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $this->session->therapist->email }}</div>
                            </div>
                        </div>
                    </div>

                    <flux:separator />

                    <div>
                        <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide mb-2">{{ __('Client') }}</div>
                        <div class="flex items-center gap-3">
                            <flux:avatar :name="$this->session->resident->full_name" />
                            <div>
                                <div class="font-medium">{{ $this->session->resident->full_name }}</div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('Room :room', ['room' => $this->session->resident->room_number ?? 'N/A']) }}
                                    @if($this->session->resident->date_of_birth)
                                        &bull; {{ __('DOB: :dob', ['dob' => $this->session->resident->date_of_birth->format('m/d/Y')]) }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <flux:button variant="ghost" size="sm" :href="route('residents.show', $this->session->resident)" wire:navigate icon="arrow-top-right-on-square">
                                {{ __('View Resident Profile') }}
                            </flux:button>
                        </div>
                    </div>

                    @if($this->session->supervisor)
                    <flux:separator />
                    <div>
                        <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide mb-2">{{ __('Supervisor (BHP)') }}</div>
                        <div class="flex items-center gap-3">
                            <flux:avatar :name="$this->session->supervisor->name" />
                            <div>
                                <div class="font-medium">{{ $this->session->supervisor->name }}</div>
                                @if($this->session->supervisor_signed_at)
                                    <div class="text-sm text-green-600 dark:text-green-400">
                                        {{ __('Signed: :date', ['date' => $this->session->supervisor_signed_at->format('M d, Y g:i A')]) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </flux:card>
        </div>

        {{-- Clinical Documentation --}}
        @if($this->session->interventions || $this->session->progress_notes || $this->session->client_plan)
            <flux:card>
                <flux:heading size="sm" class="mb-4">{{ __('Clinical Documentation') }}</flux:heading>

                <div class="space-y-6">
                    @if($this->session->interventions)
                        <div class="border-l-3 border-blue-300 dark:border-blue-700 pl-4">
                            <h4 class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">{{ __('Provider Support & Interventions') }}</h4>
                            <div class="text-sm text-zinc-600 dark:text-zinc-400 bg-zinc-50 dark:bg-zinc-800 rounded-lg p-4 whitespace-pre-wrap">{{ $this->session->interventions }}</div>
                        </div>
                    @endif

                    @if($this->session->progress_notes)
                        <div class="border-l-3 border-green-300 dark:border-green-700 pl-4">
                            <h4 class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">{{ __("Client's Progress") }}</h4>
                            <div class="text-sm text-zinc-600 dark:text-zinc-400 bg-zinc-50 dark:bg-zinc-800 rounded-lg p-4 whitespace-pre-wrap">{{ $this->session->progress_notes }}</div>
                        </div>
                    @endif

                    @if($this->session->client_plan)
                        <div class="border-l-3 border-purple-300 dark:border-purple-700 pl-4">
                            <h4 class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">{{ __("Client's Plan") }}</h4>
                            <div class="text-sm text-zinc-600 dark:text-zinc-400 bg-zinc-50 dark:bg-zinc-800 rounded-lg p-4 whitespace-pre-wrap">{{ $this->session->client_plan }}</div>
                        </div>
                    @endif
                </div>
            </flux:card>
        @endif

        {{-- Notes --}}
        @if($this->session->notes)
            <flux:card>
                <flux:heading size="sm" class="mb-4">{{ __('Additional Notes') }}</flux:heading>
                <div class="text-sm text-zinc-600 dark:text-zinc-400 whitespace-pre-wrap">{{ $this->session->notes }}</div>
            </flux:card>
        @endif

        {{-- Metadata --}}
        <flux:card>
            <flux:heading size="sm" class="mb-4">{{ __('Record Information') }}</flux:heading>

            <div class="grid gap-4 sm:grid-cols-4 text-sm">
                <div>
                    <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">{{ __('Session ID') }}</div>
                    <div class="mt-1 font-mono">#{{ $this->session->id }}</div>
                </div>
                <div>
                    <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">{{ __('Created By') }}</div>
                    <div class="mt-1">{{ $this->session->creator?->name ?? __('System') }}</div>
                </div>
                <div>
                    <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">{{ __('Created At') }}</div>
                    <div class="mt-1">{{ $this->session->created_at->format('M d, Y g:i A') }}</div>
                </div>
                <div>
                    <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">{{ __('Last Updated') }}</div>
                    <div class="mt-1">{{ $this->session->updated_at->diffForHumans() }}</div>
                </div>
            </div>
        </flux:card>

        {{-- Actions --}}
        <flux:card>
            <div class="flex flex-wrap justify-end gap-3">
                @can('view-reports')
                    {{-- Export buttons for completed + documented sessions --}}
                    @if($this->session->status === 'completed' && $this->session->progress_notes)
                        <a href="{{ route('therapy.reports.export.individual.pdf', $this->session) }}" target="_blank">
                            <flux:button variant="outline" icon="document-arrow-down">
                                {{ __('Export PDF') }}
                            </flux:button>
                        </a>
                        <a href="{{ route('therapy.reports.export.individual.word', $this->session) }}" target="_blank">
                            <flux:button variant="outline" icon="document-text">
                                {{ __('Export Word') }}
                            </flux:button>
                        </a>
                    @endif
                    <flux:button variant="primary" :href="route('therapy.reports.generate', ['session' => $this->session->id])" wire:navigate icon="document-chart-bar">
                        {{ __('AI Report') }}
                    </flux:button>
                @endcan
            </div>
        </flux:card>
    </div>

    @script
    <script>
        $wire.on('session-cancelled', () => {
            Flux.toast({ text: '{{ __('Session has been cancelled.') }}', heading: '{{ __('Cancelled') }}', variant: 'warning' })
        })
        $wire.on('session-no-show', () => {
            Flux.toast({ text: '{{ __('Session marked as no show.') }}', heading: '{{ __('No Show') }}', variant: 'warning' })
        })
    </script>
    @endscript
</flux:main>
