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
        $this->instructions = $medication->instructions ?? '';
        $this->notes = $medication->notes ?? '';
    }

    #[Computed]
    public function medication(): Medication
    {
        return Medication::with('resident')->findOrFail($this->medicationId);
    }

    public function save(): void
    {
        $validated = $this->validate($this->medicationRules());

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

                    <flux:select wire:model="route" :label="__('Route')" required>
                        <flux:select.option value="oral">{{ __('Oral') }}</flux:select.option>
                        <flux:select.option value="topical">{{ __('Topical') }}</flux:select.option>
                        <flux:select.option value="injection">{{ __('Injection') }}</flux:select.option>
                        <flux:select.option value="inhalation">{{ __('Inhalation') }}</flux:select.option>
                        <flux:select.option value="sublingual">{{ __('Sublingual') }}</flux:select.option>
                        <flux:select.option value="rectal">{{ __('Rectal') }}</flux:select.option>
                        <flux:select.option value="other">{{ __('Other') }}</flux:select.option>
                    </flux:select>

                    <flux:select wire:model="status" :label="__('Status')" required>
                        <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                        <flux:select.option value="on_hold">{{ __('On Hold') }}</flux:select.option>
                        <flux:select.option value="completed">{{ __('Completed') }}</flux:select.option>
                        <flux:select.option value="discontinued">{{ __('Discontinued') }}</flux:select.option>
                    </flux:select>
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
