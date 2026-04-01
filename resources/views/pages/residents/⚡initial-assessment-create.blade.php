<?php

use App\Concerns\InitialAssessmentValidationRules;
use App\Models\InitialAssessment;
use App\Models\Resident;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('New Initial Assessment')]
class extends Component {
    use InitialAssessmentValidationRules;

    #[Locked]
    public int $residentId;

    // Section 1
    public string $assessment_date = '';
    public string $assessment_time = '';
    public string $referral_source = '';
    public string $primary_language = '';
    public string $assessor_name = '';
    public bool $court_ordered = false;

    // Section 2
    public string $marital_status = '';
    public string $employment_status = '';
    public string $education_level = '';
    public string $living_situation = '';
    public bool $veteran_status = false;

    // Section 3
    public string $chief_complaint = '';
    public string $presenting_problem = '';
    public string $duration_of_problem = '';
    public string $previous_treatments = '';
    public string $goals_for_treatment = '';

    // Section 4 — initialized in mount()
    public array $mental_status = [];

    // Section 5 — initialized in mount()
    public array $substance_use = [];

    // Section 6
    public string $current_medications = '';
    public string $medical_conditions = '';
    public string $medication_allergies = '';
    public string $other_allergies = '';
    public string $hospitalizations = '';

    // Section 7
    public string $psychiatric_diagnoses = '';
    public string $psychiatric_hospitalizations = '';
    public string $psychiatric_medications = '';
    public string $psych_provider_name = '';
    public string $psych_provider_phone = '';

    // Section 8
    public string $legal_status = '';
    public string $legal_history = '';
    public string $employment_history = '';
    public string $family_history = '';
    public string $trauma_history = '';
    public string $social_support = '';
    public string $cultural_considerations = '';

    // Section 9
    public string $suicidal_ideation = '';
    public bool $suicide_plan = false;
    public string $suicide_history = '';
    public string $homicidal_ideation = '';
    public string $self_harm_history = '';
    public string $risk_level = '';

    // Section 10
    public string $clinical_summary = '';
    public string $primary_diagnosis = '';
    public string $secondary_diagnosis = '';
    public string $asam_level = '';
    public string $level_of_care = '';
    public string $treatment_goals = '';
    public string $recommendations = '';

    // Signature
    public array $signers = [];
    public ?int $signature_id = null;
    public string $rawSignatureData = '';

    public function mount(Resident $resident): void
    {
        abort_unless(auth()->user()->can('manage-residents'), 403);
        $this->residentId      = $resident->id;
        $this->assessment_date = now()->format('Y-m-d');

        foreach (InitialAssessment::mentalStatusCategories() as $key => $cat) {
            $this->mental_status[$key] = ['selected' => [], 'other' => ''];
        }

        foreach (InitialAssessment::substanceList() as $substance) {
            $this->substance_use[] = [
                'substance'      => $substance,
                'primary'        => false,
                'age_first_use'  => '',
                'current_use'    => false,
                'last_use_date'  => '',
                'frequency'      => '',
                'route'          => '',
                'days_abstinent' => '',
            ];
        }
    }

    #[Computed] public function resident(): Resident { return Resident::findOrFail($this->residentId); }
    #[Computed] public function mySignatures() { return auth()->user()->signatures()->orderByDesc('is_active')->orderBy('name')->get(); }
    #[Computed] public function availableUsers() { return User::orderBy('name')->get(['id', 'name']); }
    #[Computed] public function mentalStatusCategories(): array { return InitialAssessment::mentalStatusCategories(); }

    public function useSignatureOnly(string $dataUrl, string $penColor): void
    {
        if (! str_starts_with($dataUrl, 'data:image/')) return;
        $this->rawSignatureData = $dataUrl;
    }

    public function useAndSaveSignature(string $dataUrl, string $penColor): void
    {
        if (! str_starts_with($dataUrl, 'data:image/')) return;
        $user    = auth()->user();
        $isFirst = ! $user->signatures()->exists();
        $sig = $user->signatures()->create([
            'name'           => 'Initial Assessment — ' . now()->format('M d, Y'),
            'pen_color'      => $penColor,
            'signature_data' => $dataUrl,
            'is_active'      => $isFirst,
        ]);
        $this->signature_id     = $sig->id;
        $this->rawSignatureData = '';
        unset($this->mySignatures);
    }

    public function clearInlineSignature(): void { $this->rawSignatureData = ''; }

    public function save(): void
    {
        $v = $this->validate($this->initialAssessmentRules());

        InitialAssessment::create([
            'resident_id'                  => $this->residentId,
            'assessment_date'              => $this->assessment_date ?: null,
            'assessment_time'              => $this->assessment_time ?: null,
            'referral_source'              => $this->referral_source ?: null,
            'primary_language'             => $this->primary_language ?: null,
            'assessor_name'                => $this->assessor_name ?: null,
            'court_ordered'                => $this->court_ordered,
            'marital_status'               => $this->marital_status ?: null,
            'employment_status'            => $this->employment_status ?: null,
            'education_level'              => $this->education_level ?: null,
            'living_situation'             => $this->living_situation ?: null,
            'veteran_status'               => $this->veteran_status,
            'chief_complaint'              => $this->chief_complaint ?: null,
            'presenting_problem'           => $this->presenting_problem ?: null,
            'duration_of_problem'          => $this->duration_of_problem ?: null,
            'previous_treatments'          => $this->previous_treatments ?: null,
            'goals_for_treatment'          => $this->goals_for_treatment ?: null,
            'mental_status'                => $this->mental_status,
            'substance_use'                => $this->substance_use,
            'current_medications'          => $this->current_medications ?: null,
            'medical_conditions'           => $this->medical_conditions ?: null,
            'medication_allergies'         => $this->medication_allergies ?: null,
            'other_allergies'              => $this->other_allergies ?: null,
            'hospitalizations'             => $this->hospitalizations ?: null,
            'psychiatric_diagnoses'        => $this->psychiatric_diagnoses ?: null,
            'psychiatric_hospitalizations' => $this->psychiatric_hospitalizations ?: null,
            'psychiatric_medications'      => $this->psychiatric_medications ?: null,
            'psych_provider_name'          => $this->psych_provider_name ?: null,
            'psych_provider_phone'         => $this->psych_provider_phone ?: null,
            'legal_status'                 => $this->legal_status ?: null,
            'legal_history'                => $this->legal_history ?: null,
            'employment_history'           => $this->employment_history ?: null,
            'family_history'               => $this->family_history ?: null,
            'trauma_history'               => $this->trauma_history ?: null,
            'social_support'               => $this->social_support ?: null,
            'cultural_considerations'      => $this->cultural_considerations ?: null,
            'suicidal_ideation'            => $this->suicidal_ideation ?: null,
            'suicide_plan'                 => $this->suicide_plan,
            'suicide_history'              => $this->suicide_history ?: null,
            'homicidal_ideation'           => $this->homicidal_ideation ?: null,
            'self_harm_history'            => $this->self_harm_history ?: null,
            'risk_level'                   => $this->risk_level ?: null,
            'clinical_summary'             => $this->clinical_summary ?: null,
            'primary_diagnosis'            => $this->primary_diagnosis ?: null,
            'secondary_diagnosis'          => $this->secondary_diagnosis ?: null,
            'asam_level'                   => $this->asam_level ?: null,
            'level_of_care'                => $this->level_of_care ?: null,
            'treatment_goals'              => $this->treatment_goals ?: null,
            'recommendations'              => $this->recommendations ?: null,
            'signers'                      => $v['signers'] ?? [],
            'signature_id'                 => $this->signature_id,
            'raw_signature_data'           => ($this->signature_id === null && $this->rawSignatureData !== '') ? $this->rawSignatureData : null,
            'recorded_by'                  => auth()->id(),
        ]);

        session()->flash('status', 'Initial assessment saved successfully.');
        $this->redirect(route('residents.initial-assessments.index', $this->residentId), navigate: true);
    }
}; ?>

<flux:main>
    <div class="max-w-5xl space-y-1">

        <div class="mb-6 flex items-center gap-3">
            <flux:button variant="ghost" :href="route('residents.initial-assessments.index', $this->residentId)" wire:navigate icon="arrow-left" />
            <div>
                <flux:heading size="xl">{{ __('New Initial Assessment') }}</flux:heading>
                <flux:subheading>{{ $this->resident->full_name }}</flux:subheading>
            </div>
        </div>

        {{-- Resident info bar --}}
        <div class="mb-6 rounded-xl border border-blue-100 bg-blue-50/60 px-5 py-3 dark:border-blue-900/40 dark:bg-blue-950/20">
            <div class="flex flex-wrap gap-x-8 gap-y-1 text-sm">
                <div class="flex items-center gap-1.5">
                    <flux:icon name="user" class="size-4 text-blue-400" />
                    <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $this->resident->full_name }}</span>
                </div>
                @if ($this->resident->ahcccs_id)
                    <div class="flex items-center gap-1.5">
                        <flux:icon name="identification" class="size-4 text-blue-400" />
                        <span class="text-zinc-500 dark:text-zinc-400">AHCCCS ID:</span>
                        <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $this->resident->ahcccs_id }}</span>
                    </div>
                @endif
                <div class="flex items-center gap-1.5">
                    <flux:icon name="cake" class="size-4 text-blue-400" />
                    <span class="text-zinc-500 dark:text-zinc-400">DOB:</span>
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $this->resident->date_of_birth->format('M d, Y') }}</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <flux:icon name="calendar" class="size-4 text-blue-400" />
                    <span class="text-zinc-500 dark:text-zinc-400">Assessment Date:</span>
                    <div class="ml-1">
                        <input type="date" wire:model="assessment_date" class="rounded-md border border-zinc-300 bg-white px-2 py-0.5 text-sm text-zinc-800 focus:border-accent focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200" />
                    </div>
                </div>
            </div>
            @error('assessment_date') <flux:text class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</flux:text> @enderror
        </div>

        <form wire:submit="save" class="space-y-4">

            @php
                $pillClass   = 'inline-block rounded-full border px-3 py-1 text-xs font-medium cursor-pointer select-none transition';
                $activeClass = 'border-accent bg-accent/10 text-accent ring-1 ring-accent/30';
                $idleClass   = 'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400';
            @endphp

            {{-- Section 1: Assessment Information --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2"><flux:icon name="clipboard-document-list" class="size-5 text-accent" /><flux:heading size="sm">{{ __('Assessment Information') }}</flux:heading></div>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="assessment_time" :label="__('Time of Assessment')" placeholder="e.g. 10:30 AM" />
                    <flux:input wire:model="assessor_name" :label="__('Assessor Name')" />
                    <flux:input wire:model="referral_source" :label="__('Referral Source')" />
                    <flux:input wire:model="primary_language" :label="__('Primary Language')" />
                </div>
                <div>
                    <flux:label>{{ __('Court Ordered') }}</flux:label>
                    <div class="mt-2 flex gap-2">
                        <label class="cursor-pointer select-none">
                            <input type="checkbox" wire:model.live="court_ordered" class="sr-only" />
                            <span @class([$pillClass, $activeClass => $court_ordered, $idleClass => !$court_ordered])>{{ __('Court Ordered') }}</span>
                        </label>
                    </div>
                </div>
            </flux:card>

            {{-- Section 2: Psychosocial / Demographics --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2"><flux:icon name="user-circle" class="size-5 text-accent" /><flux:heading size="sm">{{ __('Psychosocial / Demographics') }}</flux:heading></div>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <flux:label>{{ __('Marital Status') }}</flux:label>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach (['Single', 'Married', 'Divorced', 'Separated', 'Widowed', 'Other'] as $opt)
                                <label class="cursor-pointer select-none">
                                    <input type="radio" wire:model.live="marital_status" value="{{ $opt }}" class="sr-only" />
                                    <span @class([$pillClass, $activeClass => $marital_status === $opt, $idleClass => $marital_status !== $opt])>{{ $opt }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <flux:label>{{ __('Employment Status') }}</flux:label>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach (['Employed', 'Unemployed', 'Disabled', 'Retired', 'Student', 'Other'] as $opt)
                                <label class="cursor-pointer select-none">
                                    <input type="radio" wire:model.live="employment_status" value="{{ $opt }}" class="sr-only" />
                                    <span @class([$pillClass, $activeClass => $employment_status === $opt, $idleClass => $employment_status !== $opt])>{{ $opt }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <flux:label>{{ __('Education Level') }}</flux:label>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach (['Less than HS', 'HS Diploma', 'Some College', 'Associates', 'Bachelors', 'Graduate', 'Other'] as $opt)
                                <label class="cursor-pointer select-none">
                                    <input type="radio" wire:model.live="education_level" value="{{ $opt }}" class="sr-only" />
                                    <span @class([$pillClass, $activeClass => $education_level === $opt, $idleClass => $education_level !== $opt])>{{ $opt }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <flux:input wire:model="living_situation" :label="__('Living Situation')" placeholder="e.g. Alone, With family, Group home..." />
                </div>
                <div>
                    <flux:label>{{ __('Veteran Status') }}</flux:label>
                    <div class="mt-2 flex gap-2">
                        <label class="cursor-pointer select-none">
                            <input type="checkbox" wire:model.live="veteran_status" class="sr-only" />
                            <span @class([$pillClass, $activeClass => $veteran_status, $idleClass => !$veteran_status])>{{ __('U.S. Veteran') }}</span>
                        </label>
                    </div>
                </div>
            </flux:card>

            {{-- Section 3: Presenting Problem --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2"><flux:icon name="chat-bubble-left-right" class="size-5 text-accent" /><flux:heading size="sm">{{ __('Presenting Problem') }}</flux:heading></div>
                <flux:separator />
                <flux:textarea wire:model="chief_complaint" :label="__('Chief Complaint / Reason for Admission')" rows="3" />
                <flux:textarea wire:model="presenting_problem" :label="__('Description of Presenting Problem')" rows="4" />
                <flux:input wire:model="duration_of_problem" :label="__('Duration of Problem')" placeholder="e.g. 6 months, 2 years..." />
                <flux:textarea wire:model="previous_treatments" :label="__('Previous Treatment Attempts')" rows="3" />
                <flux:textarea wire:model="goals_for_treatment" :label="__('Goals for Treatment')" rows="3" />
            </flux:card>

            {{-- Section 4: Mental Status --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2"><flux:icon name="face-smile" class="size-5 text-accent" /><flux:heading size="sm">{{ __('Mental Status') }}</flux:heading></div>
                <flux:separator />
                <div class="space-y-5">
                    @foreach ($this->mentalStatusCategories as $key => $cat)
                        <div>
                            <flux:label class="mb-2 block">{{ $cat['label'] }}</flux:label>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($cat['options'] as $option)
                                    <label class="cursor-pointer select-none">
                                        <input type="checkbox" wire:model.live="mental_status.{{ $key }}.selected" value="{{ $option }}" class="sr-only" />
                                        <span class="{{ $pillClass }}" :class="{{ json_encode($this->mental_status[$key]['selected'] ?? []) }}.includes('{{ $option }}') ? '{{ $activeClass }}' : '{{ $idleClass }}'">
                                            {{ $option }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @if (in_array('Other', $cat['options']) && in_array('Other', $mental_status[$key]['selected'] ?? []))
                                <input type="text" wire:model="mental_status.{{ $key }}.other" placeholder="{{ __('Specify other...') }}" class="mt-2 w-full rounded-md border border-zinc-300 bg-white px-3 py-1.5 text-sm text-zinc-800 placeholder-zinc-400 focus:border-accent focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200" />
                            @endif
                        </div>
                    @endforeach
                </div>
            </flux:card>

            {{-- Section 5: Substance Use History --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2"><flux:icon name="beaker" class="size-5 text-accent" /><flux:heading size="sm">{{ __('Substance Use History') }}</flux:heading></div>
                <flux:separator />
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                <th class="pb-2 text-left text-xs font-semibold text-zinc-500 w-32">Substance</th>
                                <th class="pb-2 text-center text-xs font-semibold text-zinc-500 w-16">Primary</th>
                                <th class="pb-2 text-center text-xs font-semibold text-zinc-500 w-20">Age First Use</th>
                                <th class="pb-2 text-center text-xs font-semibold text-zinc-500 w-20">Current Use</th>
                                <th class="pb-2 text-left text-xs font-semibold text-zinc-500 w-28">Last Use Date</th>
                                <th class="pb-2 text-left text-xs font-semibold text-zinc-500 w-28">Frequency</th>
                                <th class="pb-2 text-left text-xs font-semibold text-zinc-500 w-24">Route</th>
                                <th class="pb-2 text-left text-xs font-semibold text-zinc-500 w-24">Days Abstinent</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach ($substance_use as $i => $row)
                                <tr wire:key="substance-{{ $i }}">
                                    <td class="py-2 pr-3 font-medium text-zinc-700 dark:text-zinc-300 text-xs">{{ $row['substance'] }}</td>
                                    <td class="py-2 text-center">
                                        <input type="checkbox" wire:model="substance_use.{{ $i }}.primary" class="h-4 w-4 rounded border-zinc-300 text-accent focus:ring-accent" />
                                    </td>
                                    <td class="py-2 px-1">
                                        <input type="text" wire:model="substance_use.{{ $i }}.age_first_use" placeholder="—" class="w-full rounded border border-zinc-300 bg-white px-2 py-1 text-xs text-zinc-800 focus:border-accent focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200" />
                                    </td>
                                    <td class="py-2 text-center">
                                        <input type="checkbox" wire:model="substance_use.{{ $i }}.current_use" class="h-4 w-4 rounded border-zinc-300 text-accent focus:ring-accent" />
                                    </td>
                                    <td class="py-2 px-1">
                                        <input type="text" wire:model="substance_use.{{ $i }}.last_use_date" placeholder="—" class="w-full rounded border border-zinc-300 bg-white px-2 py-1 text-xs text-zinc-800 focus:border-accent focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200" />
                                    </td>
                                    <td class="py-2 px-1">
                                        <input type="text" wire:model="substance_use.{{ $i }}.frequency" placeholder="—" class="w-full rounded border border-zinc-300 bg-white px-2 py-1 text-xs text-zinc-800 focus:border-accent focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200" />
                                    </td>
                                    <td class="py-2 px-1">
                                        <input type="text" wire:model="substance_use.{{ $i }}.route" placeholder="—" class="w-full rounded border border-zinc-300 bg-white px-2 py-1 text-xs text-zinc-800 focus:border-accent focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200" />
                                    </td>
                                    <td class="py-2 px-1">
                                        <input type="text" wire:model="substance_use.{{ $i }}.days_abstinent" placeholder="—" class="w-full rounded border border-zinc-300 bg-white px-2 py-1 text-xs text-zinc-800 focus:border-accent focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </flux:card>

            {{-- Section 6: Medical History --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2"><flux:icon name="heart" class="size-5 text-accent" /><flux:heading size="sm">{{ __('Medical History') }}</flux:heading></div>
                <flux:separator />
                <flux:textarea wire:model="current_medications" :label="__('Current Medications')" rows="3" placeholder="{{ __('List all current medications, dosages, and prescribing providers') }}" />
                <flux:textarea wire:model="medical_conditions" :label="__('Medical Conditions')" rows="3" />
                <flux:textarea wire:model="medication_allergies" :label="__('Medication Allergies')" rows="2" />
                <flux:textarea wire:model="other_allergies" :label="__('Other Allergies')" rows="2" />
                <flux:textarea wire:model="hospitalizations" :label="__('Medical Hospitalizations / Surgeries')" rows="3" />
            </flux:card>

            {{-- Section 7: Psychiatric History --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2"><flux:icon name="sparkles" class="size-5 text-accent" /><flux:heading size="sm">{{ __('Psychiatric History') }}</flux:heading></div>
                <flux:separator />
                <flux:textarea wire:model="psychiatric_diagnoses" :label="__('Psychiatric Diagnoses')" rows="3" />
                <flux:textarea wire:model="psychiatric_hospitalizations" :label="__('Psychiatric Hospitalizations')" rows="3" />
                <flux:textarea wire:model="psychiatric_medications" :label="__('Psychiatric Medications')" rows="3" />
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="psych_provider_name" :label="__('Psychiatric Provider Name')" />
                    <flux:input wire:model="psych_provider_phone" :label="__('Psychiatric Provider Phone')" type="tel" />
                </div>
            </flux:card>

            {{-- Section 8: Psychosocial / Legal History --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2"><flux:icon name="scale" class="size-5 text-accent" /><flux:heading size="sm">{{ __('Psychosocial / Legal History') }}</flux:heading></div>
                <flux:separator />
                <flux:input wire:model="legal_status" :label="__('Current Legal Status')" placeholder="e.g. No legal issues, Probation, Pending charges..." />
                <flux:textarea wire:model="legal_history" :label="__('Legal History')" rows="3" />
                <flux:textarea wire:model="employment_history" :label="__('Employment History')" rows="3" />
                <flux:textarea wire:model="family_history" :label="__('Family History')" rows="3" />
                <flux:textarea wire:model="trauma_history" :label="__('Trauma / Abuse History')" rows="3" />
                <flux:textarea wire:model="social_support" :label="__('Social Support System')" rows="3" />
                <flux:textarea wire:model="cultural_considerations" :label="__('Cultural / Spiritual Considerations')" rows="2" />
            </flux:card>

            {{-- Section 9: Risk Assessment --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2"><flux:icon name="shield-exclamation" class="size-5 text-accent" /><flux:heading size="sm">{{ __('Risk Assessment') }}</flux:heading></div>
                <flux:separator />

                <div>
                    <flux:label>{{ __('Suicidal Ideation') }}</flux:label>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach (['none' => 'None', 'passive' => 'Passive', 'active' => 'Active'] as $val => $label)
                            <label class="cursor-pointer select-none">
                                <input type="radio" wire:model.live="suicidal_ideation" value="{{ $val }}" class="sr-only" />
                                <span @class([$pillClass, $activeClass => $suicidal_ideation === $val, $idleClass => $suicidal_ideation !== $val])>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <flux:label>{{ __('Suicide Plan') }}</flux:label>
                    <div class="mt-2 flex gap-2">
                        <label class="cursor-pointer select-none">
                            <input type="checkbox" wire:model.live="suicide_plan" class="sr-only" />
                            <span @class([$pillClass, 'border-red-400 bg-red-50 text-red-700 ring-1 ring-red-300' => $suicide_plan, $idleClass => !$suicide_plan])>{{ __('Has Suicide Plan') }}</span>
                        </label>
                    </div>
                </div>

                <flux:textarea wire:model="suicide_history" :label="__('Suicide / Self-Harm History')" rows="3" />

                <div>
                    <flux:label>{{ __('Homicidal Ideation') }}</flux:label>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach (['none' => 'None', 'passive' => 'Passive', 'active' => 'Active'] as $val => $label)
                            <label class="cursor-pointer select-none">
                                <input type="radio" wire:model.live="homicidal_ideation" value="{{ $val }}" class="sr-only" />
                                <span @class([$pillClass, $activeClass => $homicidal_ideation === $val, $idleClass => $homicidal_ideation !== $val])>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <flux:textarea wire:model="self_harm_history" :label="__('Self-Harm History')" rows="3" />

                <div>
                    <flux:label>{{ __('Overall Risk Level') }}</flux:label>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <label class="cursor-pointer select-none">
                            <input type="radio" wire:model.live="risk_level" value="low" class="sr-only" />
                            <span @class([$pillClass, 'border-green-400 bg-green-50 text-green-700 ring-1 ring-green-300' => $risk_level === 'low', $idleClass => $risk_level !== 'low'])>Low</span>
                        </label>
                        <label class="cursor-pointer select-none">
                            <input type="radio" wire:model.live="risk_level" value="moderate" class="sr-only" />
                            <span @class([$pillClass, 'border-yellow-400 bg-yellow-50 text-yellow-700 ring-1 ring-yellow-300' => $risk_level === 'moderate', $idleClass => $risk_level !== 'moderate'])>Moderate</span>
                        </label>
                        <label class="cursor-pointer select-none">
                            <input type="radio" wire:model.live="risk_level" value="high" class="sr-only" />
                            <span @class([$pillClass, 'border-orange-400 bg-orange-50 text-orange-700 ring-1 ring-orange-300' => $risk_level === 'high', $idleClass => $risk_level !== 'high'])>High</span>
                        </label>
                        <label class="cursor-pointer select-none">
                            <input type="radio" wire:model.live="risk_level" value="imminent" class="sr-only" />
                            <span @class([$pillClass, 'border-red-500 bg-red-50 text-red-700 ring-1 ring-red-400' => $risk_level === 'imminent', $idleClass => $risk_level !== 'imminent'])>Imminent</span>
                        </label>
                    </div>
                </div>
            </flux:card>

            {{-- Section 10: Diagnostic Summary & Recommendations --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2"><flux:icon name="document-magnifying-glass" class="size-5 text-accent" /><flux:heading size="sm">{{ __('Diagnostic Summary & Recommendations') }}</flux:heading></div>
                <flux:separator />
                <flux:textarea wire:model="clinical_summary" :label="__('Clinical Summary')" rows="4" />
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="primary_diagnosis" :label="__('Primary Diagnosis (DSM-5)')" placeholder="e.g. F11.20 Opioid Use Disorder, Severe" />
                    <flux:input wire:model="secondary_diagnosis" :label="__('Secondary Diagnosis')" placeholder="e.g. F32.1 Major Depressive Disorder" />
                    <flux:input wire:model="asam_level" :label="__('ASAM Level of Care')" placeholder="e.g. 3.1, 3.5, 3.7..." />
                    <flux:input wire:model="level_of_care" :label="__('Level of Care Recommendation')" placeholder="e.g. Residential, Outpatient..." />
                </div>
                <flux:textarea wire:model="treatment_goals" :label="__('Treatment Goals')" rows="3" />
                <flux:textarea wire:model="recommendations" :label="__('Recommendations & Referrals')" rows="3" />
            </flux:card>

            {{-- Signers --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2"><flux:icon name="users" class="size-5 text-accent" /><flux:heading size="sm">{{ __('Signers') }}</flux:heading></div>
                <flux:separator />
                <div class="flex flex-wrap gap-2">
                    @foreach ($this->availableUsers as $user)
                        <label wire:key="signer-{{ $user->id }}" class="cursor-pointer select-none">
                            <input type="checkbox" wire:model="signers" value="{{ $user->id }}" class="sr-only" />
                            <span class="inline-block rounded-full border px-3 py-1.5 text-sm font-medium transition"
                                :class="$wire.signers.includes({{ $user->id }}) ? 'border-accent bg-accent/10 text-accent ring-1 ring-accent/30' : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600'">
                                {{ $user->name }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </flux:card>

            {{-- Signature (optional) --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="pencil" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Employee Signature') }} <span class="ml-1 text-xs font-normal text-zinc-400">({{ __('optional') }})</span></flux:heading>
                </div>
                <flux:separator />
                @if ($this->mySignatures->isEmpty())
                    <div
                        x-data="{
                            drawnUri: @js($rawSignatureData),
                            pad: null, isEmpty: true, penColor: '#000000',
                            get hasDrawing() { return this.drawnUri !== ''; },
                            initPad(canvas) {
                                if (this.pad) return;
                                canvas.width = canvas.offsetWidth; canvas.height = 180;
                                this.pad = new SignaturePad(canvas, { backgroundColor: 'rgb(255,255,255)', penColor: this.penColor, minWidth: 1, maxWidth: 3 });
                                this.pad.addEventListener('beginStroke', () => { this.isEmpty = false; });
                                this.$watch('penColor', c => { if (this.pad) this.pad.penColor = c; });
                            },
                            clear() { if (this.pad) { this.pad.clear(); this.isEmpty = true; } },
                            applyOnly() {
                                if (!this.pad || this.pad.isEmpty()) return;
                                const uri = this.pad.toDataURL('image/png');
                                this.drawnUri = uri;
                                $wire.call('useSignatureOnly', uri, this.penColor);
                            },
                            applyAndSave() {
                                if (!this.pad || this.pad.isEmpty()) return;
                                const uri = this.pad.toDataURL('image/png');
                                this.drawnUri = uri;
                                $wire.call('useAndSaveSignature', uri, this.penColor);
                            }
                        }"
                        x-init="$nextTick(() => { const c = $refs.padCanvas; if (c) initPad(c); })"
                        wire:ignore
                    >
                        <div x-show="hasDrawing" class="mb-3 rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-700/40 dark:bg-green-900/20">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-2">
                                    <flux:icon name="check-circle" class="size-5 shrink-0 text-green-600" />
                                    <p class="text-sm font-semibold text-green-800 dark:text-green-300">{{ __('Signature applied') }}</p>
                                </div>
                                <button type="button" class="shrink-0 text-xs text-green-700 underline dark:text-green-400" x-on:click="drawnUri = ''; clear(); $wire.call('clearInlineSignature')">{{ __('Redo') }}</button>
                            </div>
                            <div class="mt-3 rounded-md bg-white p-3 dark:bg-zinc-900">
                                <img :src="drawnUri" class="max-h-16 object-contain" />
                            </div>
                        </div>
                        <div x-show="!hasDrawing" class="space-y-3">
                            <div class="flex flex-wrap items-center gap-3">
                                <flux:label>{{ __('Pen Color') }}</flux:label>
                                <input type="color" x-model="penColor" class="h-8 w-10 cursor-pointer rounded border border-zinc-300 bg-white p-0.5" />
                                <div class="flex gap-1.5">
                                    @foreach (['#000000', '#1e40af', '#15803d', '#7c3aed', '#b91c1c'] as $preset)
                                        <button type="button" class="size-6 rounded-full border-2 border-white ring-1 ring-zinc-300 hover:ring-zinc-500" style="background-color: {{ $preset }}" x-on:click="penColor = '{{ $preset }}'"></button>
                                    @endforeach
                                </div>
                            </div>
                            <div class="overflow-hidden rounded-lg border-2 border-zinc-300 bg-white dark:border-zinc-600">
                                <canvas x-ref="padCanvas" style="width: 100%; height: 180px; cursor: crosshair; touch-action: none; display: block;"></canvas>
                            </div>
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <flux:button type="button" size="sm" variant="ghost" icon="arrow-path" x-on:click="clear()">{{ __('Clear') }}</flux:button>
                                <div class="flex gap-2">
                                    <flux:button type="button" size="sm" variant="outline" x-on:click="applyOnly()" x-bind:disabled="isEmpty">{{ __('Use for This Record') }}</flux:button>
                                    <flux:button type="button" size="sm" variant="primary" x-on:click="applyAndSave()" x-bind:disabled="isEmpty">{{ __('Save to Profile & Use') }}</flux:button>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach ($this->mySignatures as $sig)
                            <label class="cursor-pointer" wire:key="sig-{{ $sig->id }}">
                                <input type="radio" wire:model.number="signature_id" value="{{ $sig->id }}" class="sr-only" />
                                <div class="rounded-xl border-2 p-3 transition" :class="$wire.signature_id === {{ $sig->id }} ? 'border-accent bg-accent/5 ring-2 ring-accent/20' : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700'">
                                    <div class="flex h-16 items-center justify-center rounded-lg bg-white p-2 dark:bg-zinc-900">
                                        <img src="{{ $sig->getDataUri() }}" alt="{{ $sig->name }}" class="max-h-full max-w-full object-contain" />
                                    </div>
                                    <span class="mt-2 block truncate text-xs font-medium text-zinc-700 dark:text-zinc-300">{{ $sig->name }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @endif
            </flux:card>

            <div class="flex justify-end gap-3 pb-4">
                <flux:button variant="ghost" :href="route('residents.initial-assessments.index', $this->residentId)" wire:navigate>{{ __('Cancel') }}</flux:button>
                <flux:button variant="primary" type="submit" icon="check">{{ __('Save Assessment') }}</flux:button>
            </div>

        </form>
    </div>
</flux:main>
