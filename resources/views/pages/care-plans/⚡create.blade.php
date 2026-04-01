<?php

use App\Concerns\CarePlanValidationRules;
use App\Models\CarePlan;
use App\Models\Resident;
use App\Services\AI\AiManager;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Create Care Plan')]
class extends Component {
    use CarePlanValidationRules;

    #[Locked]
    public ?int $preselectedResidentId = null;

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

    public bool $isGenerating = false;

    public function mount(?Resident $resident = null): void
    {
        $this->start_date = now()->format('Y-m-d');
        $this->review_date = now()->addMonth()->format('Y-m-d');

        $this->planGoals = [
            [
                'problem_description'  => '',
                'case_manager_actions' => '',
                'client_actions'       => '',
                'progress_status'      => 'not_started',
                'target_date'          => '',
            ],
        ];

        $residentName = '';
        if ($resident && $resident->exists) {
            $this->preselectedResidentId = $resident->id;
            $this->resident_id = (string) $resident->id;
            $residentName = $resident->full_name;
        }

        $this->recoveryTeam = [
            ['role' => 'Resident',         'name' => $residentName, 'title' => '', 'date' => ''],
            ['role' => 'Staff Management', 'name' => '',            'title' => '', 'date' => ''],
            ['role' => 'BHP',              'name' => '',            'title' => '', 'date' => ''],
            ['role' => 'Family/Guardian',  'name' => '',            'title' => '', 'date' => ''],
            ['role' => 'Other',            'name' => '',            'title' => '', 'date' => ''],
        ];
    }

    #[Computed]
    public function residents(): array
    {
        return Resident::active()
            ->orderBy('first_name')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->id => $r->full_name.' (Room '.$r->room_number.')'])
            ->toArray();
    }

    #[Computed]
    public function preselectedResident(): ?Resident
    {
        if ($this->preselectedResidentId) {
            return Resident::find($this->preselectedResidentId);
        }

        return null;
    }

    #[Computed]
    public function canUseAi(): bool
    {
        try {
            $aiManager = app(AiManager::class);
            return $aiManager->isUseCaseEnabled('care_assistant')
                && $aiManager->isConfigured($aiManager->getUseCaseProvider('care_assistant'));
        } catch (\Exception) {
            return false;
        }
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

    public function aiSuggest(): void
    {
        if (!$this->canUseAi || !$this->resident_id) {
            return;
        }

        $this->isGenerating = true;

        try {
            $aiManager = app(AiManager::class);
            $resident = Resident::find($this->resident_id);

            if (!$resident) {
                return;
            }

            $existingPlans = CarePlan::where('resident_id', $resident->id)
                ->active()
                ->pluck('title', 'type')
                ->map(fn ($title, $type) => "{$title} ({$type})")
                ->implode(', ');

            $prompt = "You are a care home assistant. Generate a care plan suggestion for a resident.\n\n";
            $prompt .= "Resident Details:\n";
            $prompt .= "- Name: {$resident->full_name}\n";
            $prompt .= "- Age: {$resident->age} years old\n";
            $prompt .= "- Gender: {$resident->gender}\n";
            $prompt .= "- Mobility Status: {$resident->mobility_status}\n";
            $prompt .= "- Fall Risk Level: {$resident->fall_risk_level}\n";

            if ($resident->medical_conditions) {
                $prompt .= "- Medical Conditions: {$resident->medical_conditions}\n";
            }
            if ($resident->allergies) {
                $prompt .= "- Allergies: {$resident->allergies}\n";
            }
            if ($resident->dietary_requirements) {
                $prompt .= "- Dietary Requirements: {$resident->dietary_requirements}\n";
            }

            if ($existingPlans) {
                $prompt .= "\nExisting active care plans (avoid duplicating): {$existingPlans}\n";
            }

            $prompt .= "\nBased on this resident's profile, suggest a care plan that addresses their most important needs.\n";
            $prompt .= "Respond ONLY with a JSON object (no markdown, no code fences, just raw JSON) with these keys:\n";
            $prompt .= '- "title": a concise care plan title' . "\n";
            $prompt .= '- "type": one of general, nutrition, mobility, mental_health, personal_care, medication, social' . "\n";
            $prompt .= '- "description": 2-3 sentences describing the clinical background and purpose' . "\n";

            $response = $aiManager->executeForUseCase('care_assistant', $prompt);

            if ($response->success) {
                $content = trim($response->content);
                $content = preg_replace('/^```(?:json)?\s*/m', '', $content);
                $content = preg_replace('/\s*```$/m', '', $content);

                $data = json_decode($content, true);

                if (is_array($data)) {
                    $this->title = is_string($data['title'] ?? '') ? $data['title'] : '';
                    if (isset($data['type']) && in_array($data['type'], ['general', 'nutrition', 'mobility', 'mental_health', 'personal_care', 'medication', 'social'])) {
                        $this->type = $data['type'];
                    }
                    $this->description = is_string($data['description'] ?? '') ? $data['description'] : '';
                }
            }
        } catch (\Exception) {
            // Silent fail - user can fill manually
        } finally {
            $this->isGenerating = false;
        }
    }

    public function save(): void
    {
        $validated = $this->validate($this->carePlanRules(residentRequired: true));

        $resident = Resident::findOrFail($validated['resident_id']);
        if ($resident->isInactive()) {
            $this->addError('resident_id', __('Cannot create a care plan for a :status resident.', ['status' => $resident->status]));
            return;
        }

        $carePlan = CarePlan::create([
            'resident_id'   => $validated['resident_id'],
            'title'         => $validated['title'],
            'type'          => $validated['type'],
            'status'        => $validated['status'],
            'start_date'    => $validated['start_date'],
            'review_date'   => $validated['review_date'],
            'description'   => $validated['description'],
            'notes'         => $validated['notes'],
            'recovery_team' => $this->buildRecoveryTeam(),
            'created_by'    => auth()->id(),
        ]);

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

        session()->flash('status', 'Care plan created successfully.');
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
            <flux:button variant="ghost" :href="$preselectedResidentId ? route('residents.show', $preselectedResidentId) : route('care-plans.index')" wire:navigate icon="arrow-left" />
            <div>
                <flux:heading size="xl">{{ __('Create Care Plan') }}</flux:heading>
                <flux:subheading>{{ __('Create a new treatment & discharge plan for a resident') }}</flux:subheading>
            </div>
        </div>

        {{-- Preselected Resident Card --}}
        @if($this->preselectedResident)
            <flux:card class="p-4">
                <div class="flex items-center gap-3">
                    @if($this->preselectedResident->photo_path)
                        <img src="{{ Storage::url($this->preselectedResident->photo_path) }}" alt="" class="size-12 rounded-full object-cover ring-2 ring-zinc-200 dark:ring-zinc-700" />
                    @else
                        <flux:avatar name="{{ $this->preselectedResident->full_name }}" />
                    @endif
                    <div>
                        <flux:text class="font-medium">{{ $this->preselectedResident->full_name }}</flux:text>
                        <flux:text class="text-xs text-zinc-500">
                            {{ $this->preselectedResident->age }} {{ __('years old') }}
                            @if($this->preselectedResident->room_number)
                                &middot; {{ __('Room') }} {{ $this->preselectedResident->room_number }}
                            @endif
                        </flux:text>
                        <flux:text class="text-xs text-zinc-500 mt-0.5">
                            @if($this->preselectedResident->ahcccs_id)
                                AHCCCS: {{ $this->preselectedResident->ahcccs_id }}
                            @endif
                            @if($this->preselectedResident->date_of_birth)
                                &middot; DOB: {{ $this->preselectedResident->date_of_birth->format('m/d/Y') }}
                            @endif
                            @if($this->preselectedResident->admission_date)
                                &middot; Intake: {{ $this->preselectedResident->admission_date->format('m/d/Y') }}
                            @endif
                        </flux:text>
                    </div>
                    <div class="ml-auto flex items-center gap-2">
                        <flux:badge size="sm" :color="match($this->preselectedResident->status) {
                            'active' => 'green',
                            'discharged' => 'amber',
                            'deceased' => 'red',
                            'on_leave' => 'blue',
                            default => 'zinc',
                        }">
                            {{ str_replace('_', ' ', ucfirst($this->preselectedResident->status)) }}
                        </flux:badge>
                        @if($this->canUseAi)
                            <flux:button
                                variant="primary"
                                size="sm"
                                wire:click="aiSuggest"
                                wire:loading.attr="disabled"
                                wire:target="aiSuggest"
                                icon="sparkles"
                            >
                                <span wire:loading.remove wire:target="aiSuggest">{{ __('AI Suggest') }}</span>
                                <span wire:loading wire:target="aiSuggest">{{ __('Generating...') }}</span>
                            </flux:button>
                        @endif
                    </div>
                </div>
            </flux:card>
        @endif

        <form wire:submit="save" class="space-y-6">
            {{-- Care Plan Details --}}
            <flux:card class="space-y-4">
                <div class="flex items-center justify-between">
                    <flux:heading size="sm">{{ __('Plan Details') }}</flux:heading>
                    @if($this->canUseAi && !$preselectedResidentId && $resident_id)
                        <flux:button
                            variant="primary"
                            size="sm"
                            wire:click="aiSuggest"
                            wire:loading.attr="disabled"
                            wire:target="aiSuggest"
                            icon="sparkles"
                        >
                            <span wire:loading.remove wire:target="aiSuggest">{{ __('AI Suggest') }}</span>
                            <span wire:loading wire:target="aiSuggest">{{ __('Generating...') }}</span>
                        </flux:button>
                    @endif
                </div>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <flux:input wire:model="title" :label="__('Title')" required :placeholder="__('e.g., Treatment & Discharge Plan')" />
                    </div>

                    @if(!$preselectedResidentId)
                        <div class="sm:col-span-2">
                            <flux:select wire:model.live="resident_id" :label="__('Resident')" required>
                                <flux:select.option value="">{{ __('Select a resident...') }}</flux:select.option>
                                @foreach($this->residents as $id => $name)
                                    <flux:select.option value="{{ $id }}">{{ $name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            @error('resident_id')
                                <flux:text class="mt-1 text-sm text-red-500">{{ $message }}</flux:text>
                            @enderror
                        </div>
                    @endif

                    <flux:select wire:model="type" :label="__('Type')" required>
                        <flux:select.option value="general">{{ __('General') }}</flux:select.option>
                        <flux:select.option value="nutrition">{{ __('Nutrition') }}</flux:select.option>
                        <flux:select.option value="mobility">{{ __('Mobility') }}</flux:select.option>
                        <flux:select.option value="mental_health">{{ __('Mental Health') }}</flux:select.option>
                        <flux:select.option value="personal_care">{{ __('Personal Care') }}</flux:select.option>
                        <flux:select.option value="medication">{{ __('Medication') }}</flux:select.option>
                        <flux:select.option value="social">{{ __('Social') }}</flux:select.option>
                    </flux:select>

                    <flux:select wire:model="status" :label="__('Status')" required>
                        <flux:select.option value="draft">{{ __('Draft') }}</flux:select.option>
                        <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                        <flux:select.option value="under_review">{{ __('Under Review') }}</flux:select.option>
                        <flux:select.option value="archived">{{ __('Archived') }}</flux:select.option>
                    </flux:select>

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
                <flux:button variant="ghost" :href="$preselectedResidentId ? route('residents.show', $preselectedResidentId) : route('care-plans.index')" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="primary" type="submit">
                    {{ __('Create Care Plan') }}
                </flux:button>
            </div>
        </form>
    </div>
</flux:main>
