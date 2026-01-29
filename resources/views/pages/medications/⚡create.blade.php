<?php

use App\Concerns\MedicationValidationRules;
use App\Models\Medication;
use App\Models\Resident;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Add Medication')]
class extends Component {
    use MedicationValidationRules;

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

    public function mount(): void
    {
        $this->prescribed_date = now()->format('Y-m-d');
        $this->start_date = now()->format('Y-m-d');
    }

    #[Computed]
    public function residents(): array
    {
        return Resident::active()
            ->orderBy('first_name')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->id => $r->full_name . ' (Room ' . $r->room_number . ')'])
            ->toArray();
    }

    public function save(): void
    {
        $validated = $this->validate($this->medicationRules());

        $validated['created_by'] = auth()->id();

        $medication = Medication::create($validated);

        session()->flash('status', 'Medication added successfully.');
        $this->redirect(route('medications.show', $medication), navigate: true);
    }
}; ?>

<flux:main>
    <div class="max-w-3xl space-y-6">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" :href="route('medications.index')" wire:navigate icon="arrow-left" />
            <div>
                <flux:heading size="xl">{{ __('Add Medication') }}</flux:heading>
                <flux:subheading>{{ __('Create a new medication prescription') }}</flux:subheading>
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            {{-- Medication Details --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Medication Details') }}</flux:heading>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <flux:select wire:model="resident_id" :label="__('Resident')" required>
                            <flux:select.option value="">{{ __('Select a resident...') }}</flux:select.option>
                            @foreach($this->residents as $id => $residentName)
                                <flux:select.option value="{{ $id }}">{{ $residentName }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        @error('resident_id')
                            <flux:text class="mt-1 text-sm text-red-500">{{ $message }}</flux:text>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <flux:input wire:model="name" :label="__('Medication Name')" required placeholder="e.g., Metformin, Lisinopril" />
                    </div>

                    <flux:input wire:model="dosage" :label="__('Dosage')" required placeholder="e.g., 500mg, 10ml" />
                    <flux:input wire:model="frequency" :label="__('Frequency')" required placeholder="e.g., Twice daily, Every 8 hours" />

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
                    <flux:input wire:model="prescribed_by" :label="__('Prescribed By')" required placeholder="Doctor's name" />
                    <flux:input wire:model="prescribed_date" :label="__('Prescribed Date')" type="date" required />
                    <flux:input wire:model="start_date" :label="__('Start Date')" type="date" required />
                    <flux:input wire:model="end_date" :label="__('End Date')" type="date" />
                </div>
            </flux:card>

            {{-- Instructions & Notes --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Instructions & Notes') }}</flux:heading>
                <flux:separator />
                <flux:textarea wire:model="instructions" :label="__('Instructions')" rows="3" placeholder="Administration instructions, special considerations..." />
                <flux:textarea wire:model="notes" :label="__('Notes')" rows="2" placeholder="Any additional notes..." />
            </flux:card>

            {{-- Actions --}}
            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" :href="route('medications.index')" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="primary" type="submit">
                    {{ __('Add Medication') }}
                </flux:button>
            </div>
        </form>
    </div>
</flux:main>
