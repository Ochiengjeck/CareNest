<?php

use App\Models\CarePlan;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Care Plan Details')]
class extends Component {
    #[Locked]
    public int $carePlanId;

    public function mount(CarePlan $carePlan): void
    {
        $this->carePlanId = $carePlan->id;
    }

    #[Computed]
    public function carePlan(): CarePlan
    {
        return CarePlan::with(['resident', 'creator', 'reviewer'])->findOrFail($this->carePlanId);
    }
}; ?>

<flux:main>
    <div class="max-w-3xl space-y-6">
        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <flux:button variant="ghost" :href="route('care-plans.index')" wire:navigate icon="arrow-left" />
                <div>
                    <flux:heading size="xl">{{ $this->carePlan->title }}</flux:heading>
                    <div class="mt-1 flex flex-wrap items-center gap-2">
                        <flux:badge size="sm" color="zinc">{{ $this->carePlan->type_label }}</flux:badge>
                        <flux:badge size="sm" :color="$this->carePlan->status_color">
                            {{ str_replace('_', ' ', ucfirst($this->carePlan->status)) }}
                        </flux:badge>
                    </div>
                </div>
            </div>

            @can('manage-care-plans')
                <flux:button variant="primary" :href="route('care-plans.edit', $this->carePlan)" wire:navigate icon="pencil">
                    {{ __('Edit') }}
                </flux:button>
            @endcan
        </div>

        {{-- Resident Info --}}
        @if($this->carePlan->resident)
            <flux:card class="space-y-3">
                <flux:heading size="sm">{{ __('Resident') }}</flux:heading>
                <flux:separator />
                <div class="flex items-center gap-3">
                    @if($this->carePlan->resident->photo_path)
                        <img src="{{ Storage::url($this->carePlan->resident->photo_path) }}" alt="" class="size-10 rounded-full object-cover" />
                    @else
                        <flux:avatar size="sm" name="{{ $this->carePlan->resident->full_name }}" />
                    @endif
                    <div>
                        <flux:link :href="route('residents.show', $this->carePlan->resident)" wire:navigate class="font-medium">
                            {{ $this->carePlan->resident->full_name }}
                        </flux:link>
                        <div class="text-xs text-zinc-500">
                            {{ $this->carePlan->resident->age }} {{ __('years old') }}
                            @if($this->carePlan->resident->room_number)
                                &middot; {{ __('Room') }} {{ $this->carePlan->resident->room_number }}
                            @endif
                        </div>
                    </div>
                </div>
            </flux:card>
        @endif

        {{-- Dates --}}
        <flux:card class="space-y-3">
            <flux:heading size="sm">{{ __('Schedule') }}</flux:heading>
            <flux:separator />
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-zinc-500">{{ __('Start Date') }}</dt>
                    <dd class="font-medium">{{ $this->carePlan->start_date->format('M d, Y') }}</dd>
                </div>
                <div>
                    <dt class="text-zinc-500">{{ __('Review Date') }}</dt>
                    <dd class="font-medium">{{ $this->carePlan->review_date?->format('M d, Y') ?? __('Not set') }}</dd>
                </div>
            </dl>
        </flux:card>

        {{-- Description --}}
        @if($this->carePlan->description)
            <flux:card class="space-y-3">
                <flux:heading size="sm">{{ __('Description') }}</flux:heading>
                <flux:separator />
                <flux:text class="whitespace-pre-wrap text-sm">{{ $this->carePlan->description }}</flux:text>
            </flux:card>
        @endif

        {{-- Goals --}}
        @if($this->carePlan->goals)
            <flux:card class="space-y-3">
                <flux:heading size="sm">{{ __('Goals') }}</flux:heading>
                <flux:separator />
                <flux:text class="whitespace-pre-wrap text-sm">{{ $this->carePlan->goals }}</flux:text>
            </flux:card>
        @endif

        {{-- Interventions --}}
        @if($this->carePlan->interventions)
            <flux:card class="space-y-3">
                <flux:heading size="sm">{{ __('Interventions') }}</flux:heading>
                <flux:separator />
                <flux:text class="whitespace-pre-wrap text-sm">{{ $this->carePlan->interventions }}</flux:text>
            </flux:card>
        @endif

        {{-- Notes --}}
        @if($this->carePlan->notes)
            <flux:card class="space-y-3">
                <flux:heading size="sm">{{ __('Notes') }}</flux:heading>
                <flux:separator />
                <flux:text class="whitespace-pre-wrap text-sm">{{ $this->carePlan->notes }}</flux:text>
            </flux:card>
        @endif

        {{-- Metadata --}}
        <flux:text class="text-xs text-zinc-400">
            {{ __('Created') }} {{ $this->carePlan->created_at->format('M d, Y H:i') }}
            @if($this->carePlan->creator)
                {{ __('by') }} {{ $this->carePlan->creator->name }}
            @endif
            @if($this->carePlan->reviewer)
                &middot; {{ __('Reviewed by') }} {{ $this->carePlan->reviewer->name }}
            @endif
        </flux:text>
    </div>
</flux:main>
