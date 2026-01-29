<?php

use App\Models\Incident;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Incident Details')]
class extends Component {
    #[Locked]
    public int $incidentId;

    public function mount(Incident $incident): void
    {
        $this->incidentId = $incident->id;
    }

    #[Computed]
    public function incident(): Incident
    {
        return Incident::with(['resident', 'reporter', 'reviewer'])->findOrFail($this->incidentId);
    }
}; ?>

<flux:main>
    <div class="max-w-4xl space-y-6">
        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <flux:button variant="ghost" :href="route('incidents.index')" wire:navigate icon="arrow-left" />
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <flux:heading size="xl">{{ $this->incident->title }}</flux:heading>
                        <flux:badge :color="$this->incident->severity_color">
                            {{ ucfirst($this->incident->severity) }}
                        </flux:badge>
                        <flux:badge :color="$this->incident->status_color">
                            {{ str_replace('_', ' ', ucfirst($this->incident->status)) }}
                        </flux:badge>
                    </div>
                    <flux:subheading>
                        <flux:badge size="sm" color="zinc">{{ $this->incident->type_label }}</flux:badge>
                        {{ __('on') }} {{ $this->incident->occurred_at->format('M d, Y \a\t H:i') }}
                    </flux:subheading>
                </div>
            </div>

            @can('manage-incidents')
                <flux:button :href="route('incidents.edit', $this->incident)" wire:navigate icon="pencil">
                    {{ __('Edit') }}
                </flux:button>
            @endcan
        </div>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle" class="mb-4">
                {{ session('status') }}
            </flux:callout>
        @endif

        {{-- Resident Info --}}
        @if($this->incident->resident)
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Resident Involved') }}</flux:heading>
                <flux:separator />
                <div class="flex items-center gap-3">
                    <flux:avatar :name="$this->incident->resident->full_name" size="sm" />
                    <div>
                        <flux:link :href="route('residents.show', $this->incident->resident)" wire:navigate class="font-medium">
                            {{ $this->incident->resident->full_name }}
                        </flux:link>
                        <flux:text size="sm" class="text-zinc-500">
                            {{ __('Room') }} {{ $this->incident->resident->room_number }}
                        </flux:text>
                    </div>
                </div>
            </flux:card>
        @endif

        {{-- Description --}}
        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('Description') }}</flux:heading>
            <flux:separator />
            <flux:text class="whitespace-pre-line">{{ $this->incident->description }}</flux:text>
        </flux:card>

        {{-- Immediate Actions --}}
        @if($this->incident->immediate_actions)
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Immediate Actions Taken') }}</flux:heading>
                <flux:separator />
                <flux:text class="whitespace-pre-line">{{ $this->incident->immediate_actions }}</flux:text>
            </flux:card>
        @endif

        {{-- Location & Witnesses --}}
        @if($this->incident->location || $this->incident->witnesses)
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Additional Details') }}</flux:heading>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-2">
                    @if($this->incident->location)
                        <div>
                            <flux:subheading size="sm">{{ __('Location') }}</flux:subheading>
                            <flux:text>{{ $this->incident->location }}</flux:text>
                        </div>
                    @endif
                    @if($this->incident->witnesses)
                        <div>
                            <flux:subheading size="sm">{{ __('Witnesses') }}</flux:subheading>
                            <flux:text class="whitespace-pre-line">{{ $this->incident->witnesses }}</flux:text>
                        </div>
                    @endif
                </div>
            </flux:card>
        @endif

        {{-- Outcome & Follow-up --}}
        @if($this->incident->outcome || $this->incident->follow_up_actions)
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Outcome & Follow-up') }}</flux:heading>
                <flux:separator />
                @if($this->incident->outcome)
                    <div>
                        <flux:subheading size="sm">{{ __('Outcome') }}</flux:subheading>
                        <flux:text class="whitespace-pre-line">{{ $this->incident->outcome }}</flux:text>
                    </div>
                @endif
                @if($this->incident->follow_up_actions)
                    <div>
                        <flux:subheading size="sm">{{ __('Follow-up Actions') }}</flux:subheading>
                        <flux:text class="whitespace-pre-line">{{ $this->incident->follow_up_actions }}</flux:text>
                    </div>
                @endif
            </flux:card>
        @endif

        {{-- Notes --}}
        @if($this->incident->notes)
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Notes') }}</flux:heading>
                <flux:separator />
                <flux:text class="whitespace-pre-line">{{ $this->incident->notes }}</flux:text>
            </flux:card>
        @endif

        {{-- Metadata --}}
        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('Report Details') }}</flux:heading>
            <flux:separator />
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:subheading size="sm">{{ __('Reported By') }}</flux:subheading>
                    <flux:text>{{ $this->incident->reporter?->name ?? __('Unknown') }}</flux:text>
                </div>
                <div>
                    <flux:subheading size="sm">{{ __('Reported On') }}</flux:subheading>
                    <flux:text>{{ $this->incident->created_at->format('M d, Y H:i') }}</flux:text>
                </div>
                @if($this->incident->reviewer)
                    <div>
                        <flux:subheading size="sm">{{ __('Reviewed By') }}</flux:subheading>
                        <flux:text>{{ $this->incident->reviewer->name }}</flux:text>
                    </div>
                    <div>
                        <flux:subheading size="sm">{{ __('Reviewed On') }}</flux:subheading>
                        <flux:text>{{ $this->incident->reviewed_at?->format('M d, Y H:i') ?? '-' }}</flux:text>
                    </div>
                @endif
            </div>
        </flux:card>
    </div>
</flux:main>
