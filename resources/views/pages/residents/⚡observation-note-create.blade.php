<?php

use App\Concerns\ObservationNoteValidationRules;
use App\Models\ObservationNote;
use App\Models\Resident;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('New Observation Note')]
class extends Component {
    use ObservationNoteValidationRules;

    #[Locked]
    public int $residentId;

    public string $observed_at = '';
    public string $observation_type = 'every_15_min';
    public string $behavior = '';
    public string $location = '';
    public string $mood_affect = '';
    public string $safety_status = 'safe';
    public string $notes = '';

    public function mount(Resident $resident): void
    {
        abort_unless(auth()->user()->can('manage-residents'), 403);
        $this->residentId  = $resident->id;
        $this->observed_at = now()->format('Y-m-d\TH:i');
    }

    #[Computed]
    public function resident(): Resident
    {
        return Resident::findOrFail($this->residentId);
    }

    public function save(): void
    {
        $v = $this->validate($this->observationNoteRules());

        ObservationNote::create([
            'resident_id'      => $this->residentId,
            'observed_at'      => $v['observed_at'],
            'observation_type' => $v['observation_type'],
            'behavior'         => $v['behavior'] ?? null,
            'location'         => $v['location'] ?? null,
            'mood_affect'      => $v['mood_affect'] ?? null,
            'safety_status'    => $v['safety_status'],
            'notes'            => $v['notes'] ?? null,
            'observed_by'      => auth()->id(),
        ]);

        session()->flash('status', 'Observation note saved successfully.');
        $this->redirect(route('residents.observation-notes.index', $this->residentId), navigate: true);
    }
}; ?>

<flux:main>
    <div class="max-w-4xl space-y-1">

        <div class="mb-6 flex items-center gap-3">
            <flux:button variant="ghost" :href="route('residents.observation-notes.index', $this->residentId)" wire:navigate icon="arrow-left" />
            <div>
                <flux:heading size="xl">{{ __('New Observation Note') }}</flux:heading>
                <flux:subheading>{{ $this->resident->full_name }}</flux:subheading>
            </div>
        </div>

        {{-- Resident info bar --}}
        <div class="mb-6 rounded-xl border border-blue-100 bg-blue-50/60 px-5 py-3 dark:border-blue-900/40 dark:bg-blue-950/20">
            <div class="flex flex-wrap gap-x-8 gap-y-1 text-sm">
                <div class="flex items-center gap-1.5">
                    <flux:icon name="user" class="size-4 text-blue-400" />
                    <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $this->resident->full_name }}</span>
                </div>
                @if ($this->resident->ahcccs_id)
                    <div class="flex items-center gap-1.5">
                        <flux:icon name="identification" class="size-4 text-blue-400" />
                        <span class="text-zinc-500 dark:text-zinc-400">AHCCCS ID:</span>
                        <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $this->resident->ahcccs_id }}</span>
                    </div>
                @endif
                <div class="flex items-center gap-1.5">
                    <flux:icon name="cake" class="size-4 text-blue-400" />
                    <span class="text-zinc-500 dark:text-zinc-400">DOB:</span>
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $this->resident->date_of_birth->format('M d, Y') }}</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <flux:icon name="calendar" class="size-4 text-blue-400" />
                    <span class="text-zinc-500 dark:text-zinc-400">Admitted:</span>
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $this->resident->admission_date->format('M d, Y') }}</span>
                </div>
            </div>
        </div>

        <form wire:submit="save" class="space-y-4">

            {{-- Date/Time and Type --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="clock" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Observation Details') }}</flux:heading>
                </div>
                <flux:separator />
                <flux:input type="datetime-local" wire:model="observed_at" :label="__('Date / Time of Observation')" required />
                @error('observed_at') <flux:text class="text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text> @enderror

                <div>
                    <flux:label>{{ __('Observation Type') }}</flux:label>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ([
                            'every_15_min' => 'Every 15 Min',
                            'every_30_min' => 'Every 30 Min',
                            'one_to_one'   => '1:1',
                            'continuous'   => 'Continuous',
                        ] as $val => $label)
                            <label class="cursor-pointer select-none">
                                <input type="radio" wire:model.live="observation_type" value="{{ $val }}" class="sr-only" />
                                <span @class([
                                    'inline-block rounded-full border px-3 py-1.5 text-sm font-medium transition',
                                    'border-accent bg-accent/10 text-accent ring-1 ring-accent/30' => $observation_type === $val,
                                    'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400' => $observation_type !== $val,
                                ])>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('observation_type') <flux:text class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text> @enderror
                </div>
            </flux:card>

            {{-- Safety Status --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="shield-check" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Safety Status') }}</flux:heading>
                </div>
                <flux:separator />
                <div class="flex flex-wrap gap-2">
                    @foreach (['safe' => ['Safe', 'green'], 'at_risk' => ['At Risk', 'amber'], 'unsafe' => ['Unsafe', 'red']] as $val => [$label, $color])
                        <label class="cursor-pointer select-none">
                            <input type="radio" wire:model.live="safety_status" value="{{ $val }}" class="sr-only" />
                            <span @class([
                                'inline-block rounded-full border px-4 py-1.5 text-sm font-medium transition',
                                'border-accent bg-accent/10 text-accent ring-1 ring-accent/30' => $safety_status === $val,
                                'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400' => $safety_status !== $val,
                            ])>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('safety_status') <flux:text class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text> @enderror
            </flux:card>

            {{-- Observations --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="eye" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Observations') }}</flux:heading>
                </div>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="mood_affect" :label="__('Mood / Affect')" :placeholder="__('e.g. calm, anxious, agitated, flat...')" />
                    <flux:input wire:model="location" :label="__('Location')" :placeholder="__('e.g. room, common area, hallway...')" />
                </div>
                <flux:textarea wire:model="behavior" :label="__('Behavior')" rows="4"
                    :placeholder="__('Describe observed behavior, activity, interactions...')" />
                <flux:textarea wire:model="notes" :label="__('Additional Notes')" rows="3"
                    :placeholder="__('Any additional observations or concerns...')" />
            </flux:card>

            <div class="flex justify-end gap-3 pb-4">
                <flux:button variant="ghost" :href="route('residents.observation-notes.index', $this->residentId)" wire:navigate>{{ __('Cancel') }}</flux:button>
                <flux:button variant="primary" type="submit" icon="check">{{ __('Save Observation') }}</flux:button>
            </div>

        </form>
    </div>
</flux:main>
