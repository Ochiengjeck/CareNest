<?php

use App\Concerns\TherapySessionValidationRules;
use App\Models\Resident;
use App\Models\TherapistAssignment;
use App\Models\TherapySession;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Schedule Session')]
class extends Component {
    use TherapySessionValidationRules;

    public string $therapist_id = '';
    public string $resident_id = '';
    public string $session_date = '';
    public string $start_time = '';
    public string $end_time = '';
    public string $service_type = 'individual';
    public string $challenge_index = '';
    public string $session_topic = '';
    public string $status = 'scheduled';
    public string $notes = '';

    public function mount(): void
    {
        $this->therapist_id = (string) auth()->id();
        $this->session_date = now()->format('Y-m-d');
        $this->start_time = '09:00';
        $this->end_time = '10:00';

        // Pre-select resident if passed via query string
        if (request()->has('resident')) {
            $this->resident_id = (string) request()->get('resident');
        }
    }

    #[Computed]
    public function residents(): array
    {
        // If therapist, only show assigned residents
        if (!auth()->user()->can('manage-therapy')) {
            return TherapistAssignment::query()
                ->forTherapist(auth()->id())
                ->active()
                ->with('resident')
                ->get()
                ->mapWithKeys(fn ($a) => [$a->resident_id => $a->resident->full_name . ' (Room ' . ($a->resident->room_number ?? 'N/A') . ')'])
                ->toArray();
        }

        // Admin can see all active residents
        return Resident::active()
            ->orderBy('first_name')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->id => $r->full_name . ' (Room ' . ($r->room_number ?? 'N/A') . ')'])
            ->toArray();
    }

    #[Computed]
    public function therapists(): array
    {
        if (!auth()->user()->can('manage-therapy')) {
            return [auth()->id() => auth()->user()->name];
        }

        return User::role('therapist')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    #[Computed]
    public function serviceTypes(): array
    {
        return [
            'individual' => 'Individual Note',
            'group' => 'Group',
            'intake_assessment' => 'Intake/Assessment',
            'crisis' => 'Crisis',
            'collateral' => 'Collateral',
            'case_management' => 'Case Management',
            'treatment_planning' => 'Treatment Planning',
            'discharge' => 'Discharge',
            'other' => 'Other',
        ];
    }

    #[Computed]
    public function challengeIndexes(): array
    {
        return [
            'substance_use' => '1. Substance Use Disorder',
            'mental_health' => '2. Mental Health',
            'physical_health' => '3. Physical Health',
            'employment_education' => '4. Employment/Education',
            'financial_housing' => '5. Financial/Housing',
            'legal' => '6. Legal',
            'psychosocial_family' => '7. Psycho-Social/Family',
            'spirituality' => '8. Spirituality',
        ];
    }

    public function save(): void
    {
        $validated = $this->validate($this->therapySessionRules());
        $validated['created_by'] = auth()->id();

        if (empty($validated['challenge_index'])) {
            $validated['challenge_index'] = null;
        }

        $session = TherapySession::create($validated);

        session()->flash('status', 'Session scheduled successfully.');
        $this->redirect(route('therapy.sessions.show', $session), navigate: true);
    }
}; ?>

<flux:main>
    <div class="max-w-3xl mx-auto space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Schedule Therapy Session') }}</flux:heading>
            <flux:subheading>{{ __('Create a new therapy session for a resident') }}</flux:subheading>
        </div>

        <form wire:submit="save" class="space-y-6">
            {{-- Session Details --}}
            <flux:card>
                <flux:heading size="sm" class="mb-4">{{ __('Session Details') }}</flux:heading>

                <div class="grid gap-4 sm:grid-cols-2">
                    @can('manage-therapy')
                    <flux:select wire:model="therapist_id" label="Therapist" required>
                        <flux:select.option value="">{{ __('Select therapist...') }}</flux:select.option>
                        @foreach($this->therapists as $id => $name)
                            <flux:select.option value="{{ $id }}">{{ $name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    @else
                    <input type="hidden" wire:model="therapist_id" />
                    @endcan

                    <flux:select wire:model="resident_id" label="Resident" required>
                        <flux:select.option value="">{{ __('Select resident...') }}</flux:select.option>
                        @foreach($this->residents as $id => $name)
                            <flux:select.option value="{{ $id }}">{{ $name }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:input
                        wire:model="session_date"
                        type="date"
                        label="Session Date"
                        required
                    />

                    <flux:select wire:model="service_type" label="Service Type" required>
                        @foreach($this->serviceTypes as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:input
                        wire:model="start_time"
                        type="time"
                        label="Start Time"
                        required
                    />

                    <flux:input
                        wire:model="end_time"
                        type="time"
                        label="End Time"
                        required
                    />

                    <flux:select wire:model="challenge_index" label="Treatment Plan Index">
                        <flux:select.option value="">{{ __('Select challenge/barrier...') }}</flux:select.option>
                        @foreach($this->challengeIndexes as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model="status" label="Status" required>
                        <flux:select.option value="scheduled">{{ __('Scheduled') }}</flux:select.option>
                        <flux:select.option value="completed">{{ __('Completed') }}</flux:select.option>
                        <flux:select.option value="cancelled">{{ __('Cancelled') }}</flux:select.option>
                        <flux:select.option value="no_show">{{ __('No Show') }}</flux:select.option>
                    </flux:select>
                </div>

                <div class="mt-4">
                    <flux:input
                        wire:model="session_topic"
                        label="Session Topic"
                        placeholder="e.g., DBT-Informed - Sitting With Discomfort Safely"
                        required
                    />
                </div>
            </flux:card>

            {{-- Notes --}}
            <flux:card>
                <flux:heading size="sm" class="mb-4">{{ __('Notes') }}</flux:heading>

                <flux:textarea
                    wire:model="notes"
                    label="Session Notes"
                    placeholder="Any additional notes about this session..."
                    rows="3"
                />

                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Note: Detailed documentation (interventions, progress notes, client plan) can be added after marking the session as completed.') }}
                </p>
            </flux:card>

            {{-- Actions --}}
            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" :href="route('therapy.sessions.index')" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ __('Schedule Session') }}
                </flux:button>
            </div>
        </form>
    </div>
</flux:main>
