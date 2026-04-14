<?php

use App\Concerns\MedicationValidationRules;
use App\Models\Medication;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Edit Medication')]
class extends Component {
    use MedicationValidationRules;

    #[Locked]
    public int $medicationId;

    #[Locked]
    public int $residentId;

    public string $resident_id = '';
    public string $name = '';
    public string $dosage = '';
    public string $frequency = '';
    public array $administration_times = [];
    public string $route = 'oral';
    public string $prescribed_by = '';
    public string $prescribed_date = '';
    public string $start_date = '';
    public ?string $end_date = null;
    public string $status = 'active';
    public string $instructions = '';
    public string $notes = '';

    public function mount(Medication $medication): void
    {
        $this->medicationId = $medication->id;
        $this->residentId = $medication->resident_id;
        $this->resident_id = (string) $medication->resident_id;
        $this->name = $medication->name;
        $this->dosage = $medication->dosage;
        $this->frequency = $medication->frequency;
        $this->route = $medication->route;
        $this->prescribed_by = $medication->prescribed_by;
        $this->prescribed_date = $medication->prescribed_date->format('Y-m-d');
        $this->start_date = $medication->start_date->format('Y-m-d');
        $this->end_date = $medication->end_date?->format('Y-m-d');
        $this->status = $medication->status;
        $this->administration_times = $medication->administration_times ?? [];
        $this->instructions = $medication->instructions ?? '';
        $this->notes = $medication->notes ?? '';
    }

    #[Computed]
    public function medication(): Medication
    {
        return Medication::with('resident')->findOrFail($this->medicationId);
    }

    #[Computed]
    public function residentIsInactive(): bool
    {
        return $this->medication->resident && $this->medication->resident->isInactive();
    }

    public function save(): void
    {
        $validated = $this->validate($this->medicationRules());

        // Restrict status for inactive residents
        if ($this->residentIsInactive && !in_array($validated['status'], ['discontinued', 'completed'])) {
            $this->addError('status', __('Only "Discontinued" or "Completed" status is allowed for a :status resident.', ['status' => $this->medication->resident->status]));
            return;
        }

        $this->medication->update($validated);

        session()->flash('status', 'Medication updated successfully.');
        $this->redirect(route('medications.show', $this->medicationId), navigate: true);
    }
}; ?>

<flux:main>
    <div class="max-w-3xl space-y-6">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" :href="route('medications.show', $this->medicationId)" wire:navigate icon="arrow-left" />
            <div>
                <flux:heading size="xl">{{ __('Edit Medication') }}</flux:heading>
                <flux:subheading>{{ __('Update') }} {{ $this->medication->name }} {{ __('for') }} {{ $this->medication->resident->full_name }}</flux:subheading>
            </div>
        </div>

        @if($this->residentIsInactive)
            <flux:callout icon="exclamation-triangle" color="amber">
                <flux:callout.heading>{{ __('Resident is :status', ['status' => $this->medication->resident->status]) }}</flux:callout.heading>
                <flux:callout.text>
                    {{ __('This medication can only be discontinued or completed. New medications cannot be added for this resident.') }}
                </flux:callout.text>
            </flux:callout>
        @endif

        <form wire:submit="save" class="space-y-6">
            {{-- Medication Details --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Medication Details') }}</flux:heading>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <flux:input :value="$this->medication->resident->full_name" :label="__('Resident')" disabled />
                    </div>

                    <div class="sm:col-span-2">
                        <flux:input wire:model="name" :label="__('Medication Name')" required />
                    </div>

                    <flux:input wire:model="dosage" :label="__('Dosage')" required />
                    <flux:input wire:model="frequency" :label="__('Frequency')" required />

                    <div class="sm:col-span-2"
                        x-data="{
                            times: @entangle('administration_times'),
                            addTime() { if (this.times.length < 4) this.times.push('08:00'); },
                            removeTime(i) { this.times.splice(i, 1); }
                        }">
                        <flux:label>{{ __('Administration Times') }}</flux:label>
                        <flux:description class="mb-2">{{ __('Set specific administration times (up to 4). Leave empty to derive from frequency.') }}</flux:description>
                        <div class="flex flex-wrap gap-2 items-center">
                            <template x-for="(t, i) in times" :key="i">
                                <div class="flex items-center gap-1">
                                    <input type="time" x-model="times[i]"
                                        class="rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-sm px-2 py-1.5 focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                                    <button type="button" @click="removeTime(i)"
                                        class="text-zinc-400 hover:text-red-500 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </template>
                            <button type="button" @click="addTime()" x-show="times.length < 4"
                                class="flex items-center gap-1 text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                {{ __('Add time') }}
                            </button>
                            <span x-show="times.length === 0" class="text-sm text-zinc-400 italic">{{ __('No times set — will derive from frequency') }}</span>
                        </div>
                        @error('administration_times.*')
                            <flux:text class="mt-1 text-sm text-red-500">{{ $message }}</flux:text>
                        @enderror
                    </div>

                    <flux:select wire:model="route" :label="__('Route')" required>
                        <flux:select.option value="oral">{{ __('Oral') }}</flux:select.option>
                        <flux:select.option value="topical">{{ __('Topical') }}</flux:select.option>
                        <flux:select.option value="injection">{{ __('Injection') }}</flux:select.option>
                        <flux:select.option value="inhalation">{{ __('Inhalation') }}</flux:select.option>
                        <flux:select.option value="sublingual">{{ __('Sublingual') }}</flux:select.option>
                        <flux:select.option value="rectal">{{ __('Rectal') }}</flux:select.option>
                        <flux:select.option value="other">{{ __('Other') }}</flux:select.option>
                    </flux:select>

                    @if($this->residentIsInactive)
                        <flux:select wire:model="status" :label="__('Status')" required>
                            <flux:select.option value="completed">{{ __('Completed') }}</flux:select.option>
                            <flux:select.option value="discontinued">{{ __('Discontinued') }}</flux:select.option>
                        </flux:select>
                    @else
                        <flux:select wire:model="status" :label="__('Status')" required>
                            <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                            <flux:select.option value="on_hold">{{ __('On Hold') }}</flux:select.option>
                            <flux:select.option value="completed">{{ __('Completed') }}</flux:select.option>
                            <flux:select.option value="discontinued">{{ __('Discontinued') }}</flux:select.option>
                        </flux:select>
                    @endif
                </div>
            </flux:card>

            {{-- Prescription Info --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Prescription Info') }}</flux:heading>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="prescribed_by" :label="__('Prescribed By')" required />
                    <flux:input wire:model="prescribed_date" :label="__('Prescribed Date')" type="date" required />
                    <flux:input wire:model="start_date" :label="__('Start Date')" type="date" required />
                    <flux:input wire:model="end_date" :label="__('End Date')" type="date" />
                </div>
            </flux:card>

            {{-- Instructions & Notes --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Instructions & Notes') }}</flux:heading>
                <flux:separator />
                <flux:textarea wire:model="instructions" :label="__('Instructions')" rows="3" />
                <flux:textarea wire:model="notes" :label="__('Notes')" rows="2" />
            </flux:card>

            {{-- Actions --}}
            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" :href="route('medications.show', $this->medicationId)" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="primary" type="submit">
                    {{ __('Update Medication') }}
                </flux:button>
            </div>
        </form>
    </div>
</flux:main>
