<?php

use App\Concerns\MedicationValidationRules;
use App\Models\Medication;
use App\Models\MedicationLog;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Administer Medication')]
class extends Component {
    use MedicationValidationRules;

    #[Locked]
    public int $medicationId;

    public string $medication_id = '';
    public string $administered_at = '';
    public string $status = 'given';
    public string $notes = '';

    public function mount(Medication $medication): void
    {
        if ($medication->resident && $medication->resident->isInactive()) {
            session()->flash('error', __('Cannot administer medication for a :status resident.', ['status' => $medication->resident->status]));
            $this->redirect(route('medications.show', $medication), navigate: true);
            return;
        }

        $this->medicationId = $medication->id;
        $this->medication_id = (string) $medication->id;
        $this->administered_at = now()->format('Y-m-d\TH:i');
    }

    #[Computed]
    public function medication(): Medication
    {
        return Medication::with('resident')->findOrFail($this->medicationId);
    }

    public function save(): void
    {
        $validated = $this->validate($this->medicationLogRules());

        $validated['resident_id'] = $this->medication->resident_id;
        $validated['administered_by'] = auth()->id();

        MedicationLog::create($validated);

        session()->flash('status', 'Medication administered successfully.');
        $this->redirect(route('medications.show', $this->medicationId), navigate: true);
    }
}; ?>

<flux:main>
    <div class="max-w-2xl space-y-6">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" :href="route('medications.show', $this->medicationId)" wire:navigate icon="arrow-left" />
            <div>
                <flux:heading size="xl">{{ __('Administer Medication') }}</flux:heading>
                <flux:subheading>{{ __('Log administration of') }} {{ $this->medication->name }}</flux:subheading>
            </div>
        </div>

        {{-- Medication Info --}}
        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('Medication Info') }}</flux:heading>
            <flux:separator />
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:subheading size="sm">{{ __('Medication') }}</flux:subheading>
                    <flux:text class="font-medium">{{ $this->medication->name }}</flux:text>
                </div>
                <div>
                    <flux:subheading size="sm">{{ __('Resident') }}</flux:subheading>
                    <flux:text>{{ $this->medication->resident->full_name }}</flux:text>
                </div>
                <div>
                    <flux:subheading size="sm">{{ __('Dosage') }}</flux:subheading>
                    <flux:text>{{ $this->medication->dosage }}</flux:text>
                </div>
                <div>
                    <flux:subheading size="sm">{{ __('Route') }}</flux:subheading>
                    <flux:badge size="sm" color="zinc">{{ $this->medication->route_label }}</flux:badge>
                </div>
            </div>
            @if($this->medication->instructions)
                <div>
                    <flux:subheading size="sm">{{ __('Instructions') }}</flux:subheading>
                    <flux:text class="whitespace-pre-line">{{ $this->medication->instructions }}</flux:text>
                </div>
            @endif
        </flux:card>

        {{-- Administration Form --}}
        <form wire:submit="save" class="space-y-6">
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Administration Details') }}</flux:heading>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="administered_at" :label="__('Date & Time')" type="datetime-local" required />

                    <flux:select wire:model="status" :label="__('Status')" required>
                        <flux:select.option value="given">{{ __('Given') }}</flux:select.option>
                        <flux:select.option value="refused">{{ __('Refused') }}</flux:select.option>
                        <flux:select.option value="withheld">{{ __('Withheld') }}</flux:select.option>
                        <flux:select.option value="missed">{{ __('Missed') }}</flux:select.option>
                    </flux:select>

                    <div class="sm:col-span-2">
                        <flux:textarea wire:model="notes" :label="__('Notes')" rows="3" placeholder="Reason for refusal, observations, etc." />
                    </div>
                </div>
            </flux:card>

            {{-- Actions --}}
            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" :href="route('medications.show', $this->medicationId)" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="primary" type="submit">
                    {{ __('Log Administration') }}
                </flux:button>
            </div>
        </form>
    </div>
</flux:main>
