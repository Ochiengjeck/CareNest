<?php

use App\Concerns\TherapySessionValidationRules;
use App\Models\Resident;
use App\Models\TherapySession;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Edit Session')]
class extends Component {
    use TherapySessionValidationRules;

    #[Locked]
    public int $sessionId;

    public string $therapist_id = '';
    public string $resident_id = '';
    public string $session_date = '';
    public string $start_time = '';
    public string $end_time = '';
    public string $service_type = '';
    public string $challenge_index = '';
    public string $session_topic = '';
    public string $interventions = '';
    public string $progress_notes = '';
    public string $client_plan = '';
    public string $status = '';
    public string $notes = '';

    public function mount(TherapySession $session): void
    {
        $this->sessionId = $session->id;
        $this->therapist_id = (string) $session->therapist_id;
        $this->resident_id = (string) $session->resident_id;
        $this->session_date = $session->session_date->format('Y-m-d');
        $this->start_time = $session->start_time;
        $this->end_time = $session->end_time;
        $this->service_type = $session->service_type;
        $this->challenge_index = $session->challenge_index ?? '';
        $this->session_topic = $session->session_topic;
        $this->interventions = $session->interventions ?? '';
        $this->progress_notes = $session->progress_notes ?? '';
        $this->client_plan = $session->client_plan ?? '';
        $this->status = $session->status;
        $this->notes = $session->notes ?? '';
    }

    #[Computed]
    public function session(): TherapySession
    {
        return TherapySession::findOrFail($this->sessionId);
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

    #[Computed]
    public function therapists(): array
    {
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
        $validated['updated_by'] = auth()->id();

        if (empty($validated['challenge_index'])) {
            $validated['challenge_index'] = null;
        }

        $this->session->update($validated);

        session()->flash('status', 'Session updated successfully.');
        $this->redirect(route('therapy.sessions.show', $this->session), navigate: true);
    }

    public ?int $deleteSessionId = null;

    public function confirmDelete(): void
    {
        $this->deleteSessionId = $this->sessionId;
    }

    public function deleteSession(): void
    {
        $this->session->delete();
        session()->flash('status', 'Session deleted successfully.');
        $this->redirect(route('therapy.sessions.index'), navigate: true);
    }

    public function cancelDelete(): void
    {
        $this->deleteSessionId = null;
    }
}; ?>

<flux:main>
    <div class="max-w-4xl mx-auto space-y-6">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <flux:button variant="ghost" size="sm" :href="route('therapy.sessions.show', $this->session)" wire:navigate icon="arrow-left">
                    {{ __('Back') }}
                </flux:button>
            </div>
            <flux:heading size="xl">{{ __('Edit Session') }}</flux:heading>
            <flux:subheading>{{ $this->session->session_date->format('F d, Y') }} - {{ $this->session->resident->full_name }}</flux:subheading>
        </div>

        <form wire:submit="save" class="space-y-6">
            {{-- Session Details --}}
            <flux:card>
                <flux:heading size="sm" class="mb-4">{{ __('Session Details') }}</flux:heading>

                <div class="grid gap-4 sm:grid-cols-2">
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
                        required
                    />
                </div>
            </flux:card>

            {{-- Clinical Documentation --}}
            <flux:card>
                <flux:heading size="sm" class="mb-4">{{ __('Clinical Documentation') }}</flux:heading>

                <div class="space-y-4">
                    <flux:textarea
                        wire:model="interventions"
                        label="Provider Support & Interventions"
                        placeholder="Describe the interventions and support provided..."
                        rows="4"
                    />

                    <flux:textarea
                        wire:model="progress_notes"
                        label="Client's Specific Progress"
                        placeholder="Document the client's progress..."
                        rows="4"
                    />

                    <flux:textarea
                        wire:model="client_plan"
                        label="Client's Plan"
                        placeholder="Document the plan moving forward..."
                        rows="3"
                    />
                </div>
            </flux:card>

            {{-- Notes --}}
            <flux:card>
                <flux:heading size="sm" class="mb-4">{{ __('Additional Notes') }}</flux:heading>

                <flux:textarea
                    wire:model="notes"
                    placeholder="Any additional notes..."
                    rows="3"
                />
            </flux:card>

            {{-- Actions --}}
            <div class="flex justify-between">
                <flux:button variant="danger" type="button" wire:click="confirmDelete" icon="trash">
                    {{ __('Delete Session') }}
                </flux:button>

                <div class="flex gap-3">
                    <flux:button variant="ghost" :href="route('therapy.sessions.show', $this->session)" wire:navigate>
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ __('Save Changes') }}
                    </flux:button>
                </div>
            </div>
        </form>

        {{-- Delete Confirmation Modal --}}
        <flux:modal name="confirm-delete" :show="$deleteSessionId !== null" class="max-w-md">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Delete Session') }}</flux:heading>
                    <flux:subheading>
                        {{ __('Are you sure you want to delete this therapy session? This action cannot be undone.') }}
                    </flux:subheading>
                </div>

                <div class="flex justify-end gap-3">
                    <flux:button variant="ghost" wire:click="cancelDelete">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button variant="danger" wire:click="deleteSession">
                        {{ __('Delete Session') }}
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    </div>
</flux:main>
