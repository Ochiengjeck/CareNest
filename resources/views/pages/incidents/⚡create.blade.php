<?php

use App\Concerns\IncidentValidationRules;
use App\Models\Incident;
use App\Models\Resident;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Report Incident')]
class extends Component {
    use IncidentValidationRules;

    public ?string $resident_id = null;
    public string $title = '';
    public string $type = 'fall';
    public string $severity = 'minor';
    public string $occurred_at = '';
    public string $location = '';
    public string $description = '';
    public string $immediate_actions = '';
    public string $witnesses = '';
    public string $notes = '';
    public string $status = 'open';

    public function mount(): void
    {
        $this->occurred_at = now()->format('Y-m-d\TH:i');
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
        $validated = $this->validate($this->incidentRules());

        $validated['reported_by'] = auth()->id();

        $incident = Incident::create($validated);

        session()->flash('status', 'Incident reported successfully.');
        $this->redirect(route('incidents.show', $incident), navigate: true);
    }
}; ?>

<flux:main>
    <div class="max-w-3xl space-y-6">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" :href="route('incidents.index')" wire:navigate icon="arrow-left" />
            <div>
                <flux:heading size="xl">{{ __('Report Incident') }}</flux:heading>
                <flux:subheading>{{ __('Document a new incident report') }}</flux:subheading>
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            {{-- Incident Details --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Incident Details') }}</flux:heading>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <flux:input wire:model="title" :label="__('Title')" required placeholder="Brief description of the incident" />
                    </div>

                    <flux:select wire:model="type" :label="__('Type')" required>
                        <flux:select.option value="fall">{{ __('Fall') }}</flux:select.option>
                        <flux:select.option value="medication_error">{{ __('Medication Error') }}</flux:select.option>
                        <flux:select.option value="injury">{{ __('Injury') }}</flux:select.option>
                        <flux:select.option value="behavioral">{{ __('Behavioral') }}</flux:select.option>
                        <flux:select.option value="equipment_failure">{{ __('Equipment Failure') }}</flux:select.option>
                        <flux:select.option value="other">{{ __('Other') }}</flux:select.option>
                    </flux:select>

                    <flux:select wire:model="severity" :label="__('Severity')" required>
                        <flux:select.option value="minor">{{ __('Minor') }}</flux:select.option>
                        <flux:select.option value="moderate">{{ __('Moderate') }}</flux:select.option>
                        <flux:select.option value="major">{{ __('Major') }}</flux:select.option>
                        <flux:select.option value="critical">{{ __('Critical') }}</flux:select.option>
                    </flux:select>

                    <flux:input wire:model="occurred_at" :label="__('Occurred At')" type="datetime-local" required />
                    <flux:input wire:model="location" :label="__('Location')" placeholder="e.g., Room 101, Dining Hall" />

                    <div class="sm:col-span-2">
                        <flux:select wire:model="resident_id" :label="__('Resident (optional)')">
                            <flux:select.option value="">{{ __('No resident involved') }}</flux:select.option>
                            @foreach($this->residents as $id => $residentName)
                                <flux:select.option value="{{ $id }}">{{ $residentName }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>
            </flux:card>

            {{-- Description --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Description') }}</flux:heading>
                <flux:separator />
                <flux:textarea wire:model="description" :label="__('What happened?')" rows="4" required placeholder="Describe the incident in detail..." />
                <flux:textarea wire:model="immediate_actions" :label="__('Immediate Actions Taken')" rows="3" placeholder="What actions were taken immediately after the incident?" />
            </flux:card>

            {{-- Additional Info --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Additional Information') }}</flux:heading>
                <flux:separator />
                <flux:textarea wire:model="witnesses" :label="__('Witnesses')" rows="2" placeholder="Names of any witnesses present..." />
                <flux:textarea wire:model="notes" :label="__('Notes')" rows="2" placeholder="Any additional notes..." />
            </flux:card>

            {{-- Actions --}}
            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" :href="route('incidents.index')" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="primary" type="submit">
                    {{ __('Report Incident') }}
                </flux:button>
            </div>
        </form>
    </div>
</flux:main>
