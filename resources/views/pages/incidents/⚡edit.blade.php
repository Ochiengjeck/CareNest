<?php

use App\Concerns\IncidentValidationRules;
use App\Models\Incident;
use App\Models\Resident;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Edit Incident')]
class extends Component {
    use IncidentValidationRules;

    #[Locked]
    public int $incidentId;

    public ?string $resident_id = null;
    public string $title = '';
    public string $type = 'fall';
    public string $severity = 'minor';
    public string $occurred_at = '';
    public string $location = '';
    public string $description = '';
    public string $immediate_actions = '';
    public string $witnesses = '';
    public string $outcome = '';
    public string $follow_up_actions = '';
    public string $status = 'open';
    public string $notes = '';

    public function mount(Incident $incident): void
    {
        $this->incidentId = $incident->id;
        $this->resident_id = $incident->resident_id ? (string) $incident->resident_id : null;
        $this->title = $incident->title;
        $this->type = $incident->type;
        $this->severity = $incident->severity;
        $this->occurred_at = $incident->occurred_at->format('Y-m-d\TH:i');
        $this->location = $incident->location ?? '';
        $this->description = $incident->description;
        $this->immediate_actions = $incident->immediate_actions ?? '';
        $this->witnesses = $incident->witnesses ?? '';
        $this->outcome = $incident->outcome ?? '';
        $this->follow_up_actions = $incident->follow_up_actions ?? '';
        $this->status = $incident->status;
        $this->notes = $incident->notes ?? '';
    }

    #[Computed]
    public function incident(): Incident
    {
        return Incident::findOrFail($this->incidentId);
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

        $incident = $this->incident;
        $oldStatus = $incident->status;

        // Auto-set reviewed_by when status changes
        if ($validated['status'] !== $oldStatus && in_array($validated['status'], ['resolved', 'closed'])) {
            $validated['reviewed_by'] = auth()->id();
            $validated['reviewed_at'] = now();
        }

        $incident->update($validated);

        session()->flash('status', 'Incident updated successfully.');
        $this->redirect(route('incidents.show', $this->incidentId), navigate: true);
    }
}; ?>

<flux:main>
    <div class="max-w-3xl space-y-6">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" :href="route('incidents.show', $this->incidentId)" wire:navigate icon="arrow-left" />
            <div>
                <flux:heading size="xl">{{ __('Edit Incident') }}</flux:heading>
                <flux:subheading>{{ __('Update incident report details') }}</flux:subheading>
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            {{-- Incident Details --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Incident Details') }}</flux:heading>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <flux:input wire:model="title" :label="__('Title')" required />
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

                    <flux:select wire:model="status" :label="__('Status')" required>
                        <flux:select.option value="open">{{ __('Open') }}</flux:select.option>
                        <flux:select.option value="under_investigation">{{ __('Under Investigation') }}</flux:select.option>
                        <flux:select.option value="resolved">{{ __('Resolved') }}</flux:select.option>
                        <flux:select.option value="closed">{{ __('Closed') }}</flux:select.option>
                    </flux:select>

                    <flux:input wire:model="occurred_at" :label="__('Occurred At')" type="datetime-local" required />
                    <flux:input wire:model="location" :label="__('Location')" />

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
                <flux:textarea wire:model="description" :label="__('What happened?')" rows="4" required />
                <flux:textarea wire:model="immediate_actions" :label="__('Immediate Actions Taken')" rows="3" />
            </flux:card>

            {{-- Outcome & Follow-up --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Outcome & Follow-up') }}</flux:heading>
                <flux:separator />
                <flux:textarea wire:model="outcome" :label="__('Outcome')" rows="3" placeholder="What was the outcome of this incident?" />
                <flux:textarea wire:model="follow_up_actions" :label="__('Follow-up Actions')" rows="3" placeholder="What follow-up actions are planned?" />
            </flux:card>

            {{-- Additional Info --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Additional Information') }}</flux:heading>
                <flux:separator />
                <flux:textarea wire:model="witnesses" :label="__('Witnesses')" rows="2" />
                <flux:textarea wire:model="notes" :label="__('Notes')" rows="2" />
            </flux:card>

            {{-- Actions --}}
            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" :href="route('incidents.show', $this->incidentId)" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="primary" type="submit">
                    {{ __('Update Incident') }}
                </flux:button>
            </div>
        </form>
    </div>
</flux:main>
