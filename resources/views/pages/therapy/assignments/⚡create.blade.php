<?php

use App\Concerns\TherapistAssignmentValidationRules;
use App\Models\Resident;
use App\Models\TherapistAssignment;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Create Assignment')]
class extends Component {
    use TherapistAssignmentValidationRules;

    public string $therapist_id = '';
    public string $resident_id = '';
    public string $assigned_date = '';
    public string $status = 'active';
    public string $notes = '';

    public function mount(): void
    {
        $this->assigned_date = now()->format('Y-m-d');

        if (request()->has('therapist')) {
            $this->therapist_id = (string) request()->get('therapist');
        }

        if (request()->has('resident')) {
            $this->resident_id = (string) request()->get('resident');
        }
    }

    #[Computed]
    public function therapists(): array
    {
        return User::role('therapist')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    #[Computed]
    public function residents(): array
    {
        return Resident::active()
            ->orderBy('first_name')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->id => $r->full_name . ' (Room ' . ($r->room_number ?? 'N/A') . ')'])
            ->toArray();
    }

    public function save(): void
    {
        $validated = $this->validate($this->therapistAssignmentRules());

        $resident = Resident::findOrFail($validated['resident_id']);
        if ($resident->isInactive()) {
            $this->addError('resident_id', __('Cannot assign a therapist to a :status resident.', ['status' => $resident->status]));
            return;
        }

        // Check for existing assignment
        $existing = TherapistAssignment::where('therapist_id', $validated['therapist_id'])
            ->where('resident_id', $validated['resident_id'])
            ->first();

        if ($existing) {
            $this->addError('resident_id', 'This resident is already assigned to this therapist.');
            return;
        }

        $validated['assigned_by'] = auth()->id();

        $assignment = TherapistAssignment::create($validated);

        session()->flash('status', 'Assignment created successfully.');
        $this->redirect(route('therapy.assignments.index'), navigate: true);
    }
}; ?>

<flux:main>
    <div class="max-w-2xl mx-auto space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Create Assignment') }}</flux:heading>
            <flux:subheading>{{ __('Assign a therapist to a resident') }}</flux:subheading>
        </div>

        <form wire:submit="save" class="space-y-6">
            <flux:card>
                <flux:heading size="sm" class="mb-4">{{ __('Assignment Details') }}</flux:heading>

                <div class="space-y-4">
                    <flux:select wire:model="therapist_id" label="Therapist" required>
                        <flux:select.option value="">{{ __('Select therapist...') }}</flux:select.option>
                        @foreach($this->therapists as $id => $name)
                            <flux:select.option value="{{ $id }}">{{ $name }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model="resident_id" label="Resident" required>
                        <flux:select.option value="">{{ __('Select resident...') }}</flux:select.option>
                        @foreach($this->residents as $id => $name)
                            <flux:select.option value="{{ $id }}">{{ $name }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:input
                        wire:model="assigned_date"
                        type="date"
                        label="Assigned Date"
                        required
                    />

                    <flux:select wire:model="status" label="Status" required>
                        <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                        <flux:select.option value="inactive">{{ __('Inactive') }}</flux:select.option>
                        <flux:select.option value="completed">{{ __('Completed') }}</flux:select.option>
                    </flux:select>

                    <flux:textarea
                        wire:model="notes"
                        label="Notes"
                        placeholder="Any notes about this assignment..."
                        rows="3"
                    />
                </div>
            </flux:card>

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" :href="route('therapy.assignments.index')" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ __('Create Assignment') }}
                </flux:button>
            </div>
        </form>
    </div>
</flux:main>
