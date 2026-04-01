<?php

use App\Concerns\CarePlanValidationRules;
use App\Models\CarePlan;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Edit Care Plan')]
class extends Component {
    use CarePlanValidationRules;

    #[Locked]
    public int $carePlanId;

    #[Locked]
    public int $residentId;

    public string $resident_id = '';
    public string $title = '';
    public string $type = 'general';
    public string $status = 'draft';
    public string $start_date = '';
    public ?string $review_date = null;
    public string $description = '';
    public string $notes = '';
    public array $planGoals = [];
    public array $recoveryTeam = [];

    public function mount(CarePlan $carePlan): void
    {
        $this->carePlanId = $carePlan->id;
        $this->residentId = $carePlan->resident_id;
        $this->resident_id = (string) $carePlan->resident_id;
        $this->title       = $carePlan->title;
        $this->type        = $carePlan->type;
        $this->status      = $carePlan->status;
        $this->start_date  = $carePlan->start_date->format('Y-m-d');
        $this->review_date = $carePlan->review_date?->format('Y-m-d');
        $this->description = $carePlan->description ?? '';
        $this->notes       = $carePlan->notes ?? '';

        // Load existing goals
        $this->planGoals = $carePlan->carePlanGoals
            ->map(fn ($g) => [
                'problem_description'  => $g->problem_description,
                'case_manager_actions' => $g->case_manager_actions ?? '',
                'client_actions'       => $g->client_actions ?? '',
                'progress_status'      => $g->progress_status,
                'target_date'          => $g->target_date?->format('Y-m-d') ?? '',
            ])
            ->toArray();

        if (empty($this->planGoals)) {
            $this->planGoals = [[
                'problem_description'  => '',
                'case_manager_actions' => '',
                'client_actions'       => '',
                'progress_status'      => 'not_started',
                'target_date'          => '',
            ]];
        }

        // Load recovery team or initialize defaults
        $existingTeam = $carePlan->recovery_team ?? [];
        if (!empty($existingTeam)) {
            $this->recoveryTeam = $existingTeam;
        } else {
            $residentName = $carePlan->resident?->full_name ?? '';
            $this->recoveryTeam = [
                ['role' => 'Resident',         'name' => $residentName, 'title' => '', 'date' => ''],
                ['role' => 'Staff Management', 'name' => '',            'title' => '', 'date' => ''],
                ['role' => 'BHP',              'name' => '',            'title' => '', 'date' => ''],
                ['role' => 'Family/Guardian',  'name' => '',            'title' => '', 'date' => ''],
                ['role' => 'Other',            'name' => '',            'title' => '', 'date' => ''],
            ];
        }
    }

    #[Computed]
    public function carePlan(): CarePlan
    {
        return CarePlan::with('resident')->findOrFail($this->carePlanId);
    }

    #[Computed]
    public function residentIsInactive(): bool
    {
        return $this->carePlan->resident && $this->carePlan->resident->isInactive();
    }

    public function addGoal(): void
    {
        $this->planGoals[] = [
            'problem_description'  => '',
            'case_manager_actions' => '',
            'client_actions'       => '',
            'progress_status'      => 'not_started',
            'target_date'          => '',
        ];
    }

    public function removeGoal(int $index): void
    {
        if (count($this->planGoals) > 1) {
            unset($this->planGoals[$index]);
            $this->planGoals = array_values($this->planGoals);
        }
    }

    public function save(): void
    {
        $validated = $this->validate($this->carePlanRules());

        $carePlan = CarePlan::with('carePlanGoals')->findOrFail($this->carePlanId);

        // Keep the original resident_id
        $validated['resident_id'] = $this->residentId;

        // Restrict status for inactive residents
        if ($this->residentIsInactive && !in_array($validated['status'], ['archived'])) {
            $this->addError('status', __('Only "Archived" status is allowed for a :status resident.', ['status' => $carePlan->resident->status]));
            return;
        }

        $carePlan->update(array_merge([
            'title'         => $validated['title'],
            'type'          => $validated['type'],
            'status'        => $validated['status'],
            'start_date'    => $validated['start_date'],
            'review_date'   => $validated['review_date'],
            'description'   => $validated['description'],
            'notes'         => $validated['notes'],
            'recovery_team' => $this->buildRecoveryTeam(),
            'updated_by'    => auth()->id(),
        ]));

        // Sync goals: delete old, create new
        $carePlan->carePlanGoals()->delete();
        foreach (array_values($this->planGoals) as $index => $goal) {
            if (trim($goal['problem_description']) === '') {
                continue;
            }
            $carePlan->carePlanGoals()->create([
                'problem_description'  => $goal['problem_description'],
                'case_manager_actions' => $goal['case_manager_actions'] ?: null,
                'client_actions'       => $goal['client_actions'] ?: null,
                'progress_status'      => $goal['progress_status'] ?? 'not_started',
                'target_date'          => $goal['target_date'] ?: null,
                'sort_order'           => $index,
            ]);
        }

        session()->flash('status', 'Care plan updated successfully.');
        $this->redirect(route('care-plans.show', $carePlan), navigate: true);
    }

    protected function buildRecoveryTeam(): array
    {
        return collect($this->recoveryTeam)
            ->map(fn ($member) => [
                'role'  => $member['role'],
                'name'  => $member['name'] ?? '',
                'title' => $member['title'] ?? '',
                'date'  => $member['date'] ?? '',
            ])
            ->toArray();
    }
}; ?>

<flux:main>
    <div class="max-w-3xl space-y-6">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" :href="route('care-plans.show', $carePlanId)" wire:navigate icon="arrow-left" />
            <div>
                <flux:heading size="xl">{{ __('Edit Care Plan') }}</flux:heading>
                <flux:subheading>{{ $title }}</flux:subheading>
            </div>
        </div>

        {{-- Resident Card --}}
        @if($this->carePlan->resident)
            <flux:card class="p-4">
                <div class="flex items-center gap-3">
                    @if($this->carePlan->resident->photo_path)
                        <img src="{{ Storage::url($this->carePlan->resident->photo_path) }}" alt="" class="size-12 rounded-full object-cover ring-2 ring-zinc-200 dark:ring-zinc-700" />
                    @else
                        <flux:avatar name="{{ $this->carePlan->resident->full_name }}" />
                    @endif
                    <div>
                        <flux:text class="font-medium">{{ $this->carePlan->resident->full_name }}</flux:text>
                        <flux:text class="text-xs text-zinc-500">
                            {{ $this->carePlan->resident->age }} {{ __('years old') }}
                            @if($this->carePlan->resident->room_number)
                                &middot; {{ __('Room') }} {{ $this->carePlan->resident->room_number }}
                            @endif
                        </flux:text>
                        <flux:text class="text-xs text-zinc-500 mt-0.5">
                            @if($this->carePlan->resident->ahcccs_id)
                                AHCCCS: {{ $this->carePlan->resident->ahcccs_id }}
                            @endif
                            @if($this->carePlan->resident->date_of_birth)
                                &middot; DOB: {{ $this->carePlan->resident->date_of_birth->format('m/d/Y') }}
                            @endif
                            @if($this->carePlan->resident->admission_date)
                                &middot; Intake: {{ $this->carePlan->resident->admission_date->format('m/d/Y') }}
                            @endif
                        </flux:text>
                    </div>
                    <flux:badge size="sm" :color="match($this->carePlan->resident->status) {
                        'active' => 'green',
                        'discharged' => 'amber',
                        'deceased' => 'red',
                        'on_leave' => 'blue',
                        default => 'zinc',
                    }" class="ml-auto">
                        {{ str_replace('_', ' ', ucfirst($this->carePlan->resident->status)) }}
                    </flux:badge>
                </div>
            </flux:card>
        @endif

        @if($this->residentIsInactive)
            <flux:callout icon="exclamation-triangle" color="amber">
                <flux:callout.heading>{{ __('Resident is :status', ['status' => $this->carePlan->resident->status]) }}</flux:callout.heading>
                <flux:callout.text>
                    {{ __('This care plan can only be archived. New care plans cannot be created for this resident.') }}
                </flux:callout.text>
            </flux:callout>
        @endif

        <form wire:submit="save" class="space-y-6">
            {{-- Care Plan Details --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Plan Details') }}</flux:heading>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <flux:input wire:model="title" :label="__('Title')" required />
                    </div>

                    <flux:select wire:model="type" :label="__('Type')" required>
                        <flux:select.option value="general">{{ __('General') }}</flux:select.option>
                        <flux:select.option value="nutrition">{{ __('Nutrition') }}</flux:select.option>
                        <flux:select.option value="mobility">{{ __('Mobility') }}</flux:select.option>
                        <flux:select.option value="mental_health">{{ __('Mental Health') }}</flux:select.option>
                        <flux:select.option value="personal_care">{{ __('Personal Care') }}</flux:select.option>
                        <flux:select.option value="medication">{{ __('Medication') }}</flux:select.option>
                        <flux:select.option value="social">{{ __('Social') }}</flux:select.option>
                    </flux:select>

                    @if($this->residentIsInactive)
                        <flux:select wire:model="status" :label="__('Status')" required>
                            <flux:select.option value="archived">{{ __('Archived') }}</flux:select.option>
                        </flux:select>
                    @else
                        <flux:select wire:model="status" :label="__('Status')" required>
                            <flux:select.option value="draft">{{ __('Draft') }}</flux:select.option>
                            <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                            <flux:select.option value="under_review">{{ __('Under Review') }}</flux:select.option>
                            <flux:select.option value="archived">{{ __('Archived') }}</flux:select.option>
                        </flux:select>
                    @endif

                    <flux:input wire:model="start_date" :label="__('Start Date')" type="date" required />
                    <flux:input wire:model="review_date" :label="__('Tx Plan Review Date')" type="date" />
                </div>
            </flux:card>

            {{-- Clinical Background --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Clinical Background') }}</flux:heading>
                <flux:separator />
                <flux:textarea wire:model="description" :label="__('Clinical Background & Assessment')" rows="4"
                    :placeholder="__('Describe clinical history, assessment findings, and treatment context...')" />
            </flux:card>

            {{-- Treatment Goals --}}
            <flux:card class="space-y-4">
                <div class="flex items-center justify-between">
                    <flux:heading size="sm">{{ __('Treatment Goals') }}</flux:heading>
                    <flux:button variant="ghost" size="sm" wire:click="addGoal" icon="plus">
                        {{ __('Add Goal') }}
                    </flux:button>
                </div>
                <flux:separator />

                @foreach($planGoals as $index => $goal)
                    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 space-y-3" wire:key="goal-{{ $index }}">
                        <div class="flex items-center justify-between">
                            <flux:text class="text-xs font-semibold text-zinc-500 uppercase tracking-wide">
                                {{ __('Goal') }} {{ $index + 1 }}
                            </flux:text>
                            @if(count($planGoals) > 1)
                                <flux:button variant="ghost" size="xs" wire:click="removeGoal({{ $index }})" icon="x-mark" />
                            @endif
                        </div>

                        <flux:textarea
                            wire:model="planGoals.{{ $index }}.problem_description"
                            :label="__('Problem / Challenge')"
                            rows="2"
                            :placeholder="__('Describe the diagnosis or concern...')"
                        />
                        @error('planGoals.'.$index.'.problem_description')
                            <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
                        @enderror

                        <div class="grid gap-3 sm:grid-cols-2">
                            <flux:textarea
                                wire:model="planGoals.{{ $index }}.case_manager_actions"
                                :label="__('Case Manager Will')"
                                rows="3"
                                :placeholder="__('Staff interventions and actions...')"
                            />
                            <flux:textarea
                                wire:model="planGoals.{{ $index }}.client_actions"
                                :label="__('Client Will')"
                                rows="3"
                                :placeholder="__('What the resident will do...')"
                            />
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <flux:select wire:model="planGoals.{{ $index }}.progress_status" :label="__('Progress Status')">
                                <flux:select.option value="not_started">{{ __('Not Started') }}</flux:select.option>
                                <flux:select.option value="making_progress">{{ __('Making Progress') }}</flux:select.option>
                                <flux:select.option value="achieved">{{ __('Achieved') }}</flux:select.option>
                                <flux:select.option value="not_achieved">{{ __('Not Achieved') }}</flux:select.option>
                            </flux:select>
                            <flux:input wire:model="planGoals.{{ $index }}.target_date"
                                :label="__('Target Date')" type="date" />
                        </div>
                    </div>
                @endforeach
            </flux:card>

            {{-- Additional Notes --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Additional Notes') }}</flux:heading>
                <flux:separator />
                <flux:textarea wire:model="notes" :label="__('Notes')" rows="2" :placeholder="__('Any additional notes...')" />
            </flux:card>

            {{-- Recovery Team --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Recovery Team') }}</flux:heading>
                <flux:separator />
                <div class="space-y-3">
                    @foreach($recoveryTeam as $index => $member)
                        <div class="grid gap-3 sm:grid-cols-3 items-end" wire:key="team-{{ $index }}">
                            <div @class(['sm:pt-6' => !$loop->first])>
                                @if($loop->first)
                                    <flux:text class="mb-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Role') }}</flux:text>
                                @endif
                                <flux:text class="text-sm font-medium">{{ $member['role'] }}</flux:text>
                            </div>
                            <flux:input wire:model="recoveryTeam.{{ $index }}.name"
                                :label="$loop->first ? __('Name') : null"
                                :placeholder="__('Full name...')" />
                            <flux:input wire:model="recoveryTeam.{{ $index }}.title"
                                :label="$loop->first ? __('Title / Credentials') : null"
                                :placeholder="__('Title or credentials...')" />
                        </div>
                    @endforeach
                </div>
            </flux:card>

            {{-- Actions --}}
            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" :href="route('care-plans.show', $carePlanId)" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="primary" type="submit">
                    {{ __('Save Changes') }}
                </flux:button>
            </div>
        </form>
    </div>
</flux:main>
