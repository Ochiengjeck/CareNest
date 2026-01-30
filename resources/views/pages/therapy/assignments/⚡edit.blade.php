<?php

use App\Concerns\TherapistAssignmentValidationRules;
use App\Models\Resident;
use App\Models\TherapistAssignment;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Edit Assignment')]
class extends Component {
    use TherapistAssignmentValidationRules;

    #[Locked]
    public int $assignmentId;

    public string $therapist_id = '';
    public string $resident_id = '';
    public string $assigned_date = '';
    public string $status = '';
    public string $notes = '';

    public ?int $deleteAssignmentId = null;

    public function mount(TherapistAssignment $assignment): void
    {
        $this->assignmentId = $assignment->id;
        $this->therapist_id = (string) $assignment->therapist_id;
        $this->resident_id = (string) $assignment->resident_id;
        $this->assigned_date = $assignment->assigned_date->format('Y-m-d');
        $this->status = $assignment->status;
        $this->notes = $assignment->notes ?? '';
    }

    #[Computed]
    public function assignment(): TherapistAssignment
    {
        return TherapistAssignment::with(['therapist', 'resident'])->findOrFail($this->assignmentId);
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

        // Check for existing assignment if therapist or resident changed
        if ($validated['therapist_id'] != $this->assignment->therapist_id ||
            $validated['resident_id'] != $this->assignment->resident_id) {
            $existing = TherapistAssignment::where('therapist_id', $validated['therapist_id'])
                ->where('resident_id', $validated['resident_id'])
                ->where('id', '!=', $this->assignmentId)
                ->first();

            if ($existing) {
                $this->addError('resident_id', 'This resident is already assigned to this therapist.');
                return;
            }
        }

        $this->assignment->update($validated);

        session()->flash('status', 'Assignment updated successfully.');
        $this->redirect(route('therapy.assignments.index'), navigate: true);
    }

    public function confirmDelete(): void
    {
        $this->deleteAssignmentId = $this->assignmentId;
    }

    public function deleteAssignment(): void
    {
        $this->assignment->delete();
        session()->flash('status', 'Assignment deleted successfully.');
        $this->redirect(route('therapy.assignments.index'), navigate: true);
    }

    public function cancelDelete(): void
    {
        $this->deleteAssignmentId = null;
    }
}; ?>

<flux:main>
    <div class="max-w-2xl mx-auto space-y-6">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <flux:button variant="ghost" size="sm" :href="route('therapy.assignments.index')" wire:navigate icon="arrow-left">
                    {{ __('Back') }}
                </flux:button>
            </div>
            <flux:heading size="xl">{{ __('Edit Assignment') }}</flux:heading>
            <flux:subheading>
                {{ $this->assignment->therapist->name }} &rarr; {{ $this->assignment->resident->full_name }}
            </flux:subheading>
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

            <div class="flex justify-between">
                <flux:button variant="danger" type="button" wire:click="confirmDelete" icon="trash">
                    {{ __('Delete') }}
                </flux:button>

                <div class="flex gap-3">
                    <flux:button variant="ghost" :href="route('therapy.assignments.index')" wire:navigate>
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ __('Save Changes') }}
                    </flux:button>
                </div>
            </div>
        </form>

        {{-- Delete Confirmation Modal --}}
        <flux:modal name="confirm-delete" :show="$deleteAssignmentId !== null" class="max-w-md">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Delete Assignment') }}</flux:heading>
                    <flux:subheading>
                        {{ __('Are you sure you want to delete this assignment? This will not delete the associated therapy sessions.') }}
                    </flux:subheading>
                </div>

                <div class="flex justify-end gap-3">
                    <flux:button variant="ghost" wire:click="cancelDelete">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button variant="danger" wire:click="deleteAssignment">
                        {{ __('Delete Assignment') }}
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    </div>
</flux:main>
