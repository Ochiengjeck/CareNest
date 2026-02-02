<?php

use App\Models\Vital;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Vital Signs Detail')]
class extends Component {
    #[Locked]
    public int $vitalId;

    public function mount(Vital $vital): void
    {
        $this->vitalId = $vital->id;
    }

    #[Computed]
    public function vital(): Vital
    {
        return Vital::with(['resident', 'recordedBy'])->findOrFail($this->vitalId);
    }
}; ?>

<flux:main>
    <div class="max-w-3xl space-y-6">
        {{-- Header --}}
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" :href="route('vitals.index')" wire:navigate icon="arrow-left" />
            <div>
                <div class="flex items-center gap-2">
                    <flux:heading size="xl">{{ __('Vital Signs') }}</flux:heading>
                    @if($this->vital->hasAbnormalValues())
                        <flux:badge color="red">{{ __('Abnormal') }}</flux:badge>
                    @else
                        <flux:badge color="green">{{ __('Normal') }}</flux:badge>
                    @endif
                </div>
                <flux:subheading>
                    {{ __('For') }}
                    <flux:link :href="route('residents.show', $this->vital->resident)" wire:navigate>
                        {{ $this->vital->resident->full_name }}
                    </flux:link>
                    {{ __('on') }} {{ $this->vital->recorded_at->format('M d, Y \a\t H:i') }}
                </flux:subheading>
            </div>
        </div>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle" class="mb-4">
                {{ session('status') }}
            </flux:callout>
        @endif

        {{-- Vital Signs Grid --}}
        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('Vital Signs') }}</flux:heading>
            <flux:separator />

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {{-- Blood Pressure --}}
                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:subheading size="sm">{{ __('Blood Pressure') }}</flux:subheading>
                    @if($this->vital->blood_pressure)
                        <flux:text class="text-2xl font-semibold {{ ($this->vital->blood_pressure_systolic > 140 || $this->vital->blood_pressure_systolic < 90) ? 'text-red-500' : '' }}">
                            {{ $this->vital->blood_pressure }} <span class="text-sm font-normal text-zinc-500">mmHg</span>
                        </flux:text>
                    @else
                        <flux:text class="text-zinc-400">{{ __('Not recorded') }}</flux:text>
                    @endif
                </div>

                {{-- Heart Rate --}}
                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:subheading size="sm">{{ __('Heart Rate') }}</flux:subheading>
                    @if($this->vital->heart_rate)
                        <flux:text class="text-2xl font-semibold {{ ($this->vital->heart_rate > 100 || $this->vital->heart_rate < 60) ? 'text-red-500' : '' }}">
                            {{ $this->vital->heart_rate }} <span class="text-sm font-normal text-zinc-500">bpm</span>
                        </flux:text>
                    @else
                        <flux:text class="text-zinc-400">{{ __('Not recorded') }}</flux:text>
                    @endif
                </div>

                {{-- Temperature --}}
                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:subheading size="sm">{{ __('Temperature') }}</flux:subheading>
                    @if($this->vital->temperature)
                        <flux:text class="text-2xl font-semibold {{ ($this->vital->temperature > 100.0 || $this->vital->temperature < 96.8) ? 'text-red-500' : '' }}">
                            {{ $this->vital->temperature }} <span class="text-sm font-normal text-zinc-500">°F</span>
                        </flux:text>
                    @else
                        <flux:text class="text-zinc-400">{{ __('Not recorded') }}</flux:text>
                    @endif
                </div>

                {{-- Respiratory Rate --}}
                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:subheading size="sm">{{ __('Respiratory Rate') }}</flux:subheading>
                    @if($this->vital->respiratory_rate)
                        <flux:text class="text-2xl font-semibold {{ ($this->vital->respiratory_rate > 20 || $this->vital->respiratory_rate < 12) ? 'text-red-500' : '' }}">
                            {{ $this->vital->respiratory_rate }} <span class="text-sm font-normal text-zinc-500">/min</span>
                        </flux:text>
                    @else
                        <flux:text class="text-zinc-400">{{ __('Not recorded') }}</flux:text>
                    @endif
                </div>

                {{-- Oxygen Saturation --}}
                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:subheading size="sm">{{ __('Oxygen Saturation') }}</flux:subheading>
                    @if($this->vital->oxygen_saturation)
                        <flux:text class="text-2xl font-semibold {{ $this->vital->oxygen_saturation < 95 ? 'text-red-500' : '' }}">
                            {{ $this->vital->oxygen_saturation }}<span class="text-sm font-normal text-zinc-500">%</span>
                        </flux:text>
                    @else
                        <flux:text class="text-zinc-400">{{ __('Not recorded') }}</flux:text>
                    @endif
                </div>

                {{-- Blood Sugar --}}
                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:subheading size="sm">{{ __('Blood Sugar') }}</flux:subheading>
                    @if($this->vital->blood_sugar)
                        <flux:text class="text-2xl font-semibold">
                            {{ $this->vital->blood_sugar }} <span class="text-sm font-normal text-zinc-500">mmol/L</span>
                        </flux:text>
                    @else
                        <flux:text class="text-zinc-400">{{ __('Not recorded') }}</flux:text>
                    @endif
                </div>

                {{-- Weight --}}
                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:subheading size="sm">{{ __('Weight') }}</flux:subheading>
                    @if($this->vital->weight)
                        <flux:text class="text-2xl font-semibold">
                            {{ $this->vital->weight }} <span class="text-sm font-normal text-zinc-500">kg</span>
                        </flux:text>
                    @else
                        <flux:text class="text-zinc-400">{{ __('Not recorded') }}</flux:text>
                    @endif
                </div>

                {{-- Pain Level --}}
                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:subheading size="sm">{{ __('Pain Level') }}</flux:subheading>
                    @if($this->vital->pain_level !== null)
                        <flux:text class="text-2xl font-semibold {{ $this->vital->pain_level >= 7 ? 'text-red-500' : ($this->vital->pain_level >= 4 ? 'text-amber-500' : '') }}">
                            {{ $this->vital->pain_level }}<span class="text-sm font-normal text-zinc-500">/10</span>
                        </flux:text>
                    @else
                        <flux:text class="text-zinc-400">{{ __('Not assessed') }}</flux:text>
                    @endif
                </div>

                {{-- Consciousness Level --}}
                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:subheading size="sm">{{ __('Consciousness (AVPU)') }}</flux:subheading>
                    @if($this->vital->consciousness_level)
                        <flux:badge size="sm" :color="$this->vital->consciousness_level === 'alert' ? 'green' : ($this->vital->consciousness_level === 'verbal' ? 'amber' : 'red')">
                            {{ ucfirst($this->vital->consciousness_level) }}
                        </flux:badge>
                    @else
                        <flux:text class="text-zinc-400">{{ __('Not assessed') }}</flux:text>
                    @endif
                </div>
            </div>
        </flux:card>

        {{-- Notes --}}
        @if($this->vital->notes)
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Notes') }}</flux:heading>
                <flux:separator />
                <flux:text class="whitespace-pre-line">{{ $this->vital->notes }}</flux:text>
            </flux:card>
        @endif

        {{-- Metadata --}}
        <flux:text size="sm" class="text-zinc-400">
            {{ __('Recorded by') }} {{ $this->vital->recordedBy?->name ?? __('System') }}
            {{ __('on') }} {{ $this->vital->created_at->format('M d, Y H:i') }}
        </flux:text>
    </div>
</flux:main>
