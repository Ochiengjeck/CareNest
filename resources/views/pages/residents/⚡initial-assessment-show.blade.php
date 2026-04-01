<?php

use App\Models\InitialAssessment;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Initial Assessment')]
class extends Component {
    #[Locked]
    public int $recordId;

    public function mount(InitialAssessment $initialAssessment): void
    {
        abort_unless(auth()->user()->can('view-residents'), 403);
        $this->recordId = $initialAssessment->id;
    }

    #[Computed]
    public function record(): InitialAssessment
    {
        return InitialAssessment::with(['resident', 'recorder', 'signature'])->findOrFail($this->recordId);
    }

    #[Computed]
    public function signerNames(): array
    {
        $signers = $this->record->signers ?? [];
        return empty($signers) ? [] : User::whereIn('id', $signers)->orderBy('name')->pluck('name')->toArray();
    }

    #[Computed]
    public function mentalStatusCategories(): array { return InitialAssessment::mentalStatusCategories(); }
}; ?>

<flux:main>
    @php $record = $this->record; @endphp
    <div class="max-w-4xl space-y-5">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                <flux:button variant="ghost" :href="route('residents.initial-assessments.index', $record->resident_id)" wire:navigate icon="arrow-left" />
                <div>
                    <flux:heading size="xl">{{ __('Initial Assessment') }}</flux:heading>
                    <flux:subheading>{{ $record->resident->full_name }}</flux:subheading>
                </div>
            </div>
            <a href="{{ route('initial-assessments.export.pdf', $record->id) }}" target="_blank">
                <flux:button variant="outline" icon="arrow-down-tray">{{ __('Download PDF') }}</flux:button>
            </a>
        </div>

        {{-- Info strip --}}
        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-zinc-200 bg-zinc-50 px-5 py-3 dark:border-zinc-700 dark:bg-zinc-800/40">
            @if ($record->assessment_date)
                <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $record->assessment_date->format('M d, Y') }}</span>
            @endif
            @if ($record->risk_level)
                <flux:badge :color="match($record->risk_level) {
                    'low'      => 'green',
                    'moderate' => 'yellow',
                    'high'     => 'amber',
                    'imminent' => 'red',
                    default    => 'zinc',
                }">{{ ucfirst($record->risk_level) }} Risk</flux:badge>
            @endif
            <span class="ml-auto flex items-center gap-1.5 text-sm text-zinc-500">
                <flux:icon name="user" class="size-4" />{{ $record->recorder?->name ?? '—' }}
                <span class="text-zinc-400">&bull;</span>{{ $record->created_at->diffForHumans() }}
            </span>
        </div>

        {{-- Resident info --}}
        <div class="rounded-lg border border-blue-100 bg-blue-50/60 px-5 py-2.5 dark:border-blue-900/40 dark:bg-blue-950/20">
            <div class="flex flex-wrap gap-x-8 gap-y-1 text-sm">
                <div><span class="text-zinc-400">AHCCCS ID:</span> <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $record->resident->ahcccs_id ?? '—' }}</span></div>
                <div><span class="text-zinc-400">DOB:</span> <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $record->resident->date_of_birth->format('M d, Y') }}</span></div>
            </div>
        </div>

        {{-- Section 1: Assessment Information --}}
        <flux:card class="space-y-3">
            <div class="flex items-center gap-2"><flux:icon name="clipboard-document-list" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Assessment Information') }}</flux:heading></div>
            <flux:separator />
            <div class="grid grid-cols-2 gap-x-8 gap-y-2 text-sm sm:grid-cols-3">
                <div><div class="text-xs text-zinc-400">Assessment Date</div><div class="font-medium">{{ $record->assessment_date?->format('M d, Y') ?? '—' }}</div></div>
                <div><div class="text-xs text-zinc-400">Time</div><div class="font-medium">{{ $record->assessment_time ?? '—' }}</div></div>
                <div><div class="text-xs text-zinc-400">Assessor</div><div class="font-medium">{{ $record->assessor_name ?? '—' }}</div></div>
                <div><div class="text-xs text-zinc-400">Referral Source</div><div class="font-medium">{{ $record->referral_source ?? '—' }}</div></div>
                <div><div class="text-xs text-zinc-400">Primary Language</div><div class="font-medium">{{ $record->primary_language ?? '—' }}</div></div>
                <div><div class="text-xs text-zinc-400">Court Ordered</div><div class="font-medium">{{ $record->court_ordered ? 'Yes' : 'No' }}</div></div>
            </div>
        </flux:card>

        {{-- Section 2: Psychosocial / Demographics --}}
        <flux:card class="space-y-3">
            <div class="flex items-center gap-2"><flux:icon name="user-circle" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Psychosocial / Demographics') }}</flux:heading></div>
            <flux:separator />
            <div class="grid grid-cols-2 gap-x-8 gap-y-2 text-sm sm:grid-cols-3">
                <div><div class="text-xs text-zinc-400">Marital Status</div><div class="font-medium">{{ $record->marital_status ?? '—' }}</div></div>
                <div><div class="text-xs text-zinc-400">Employment Status</div><div class="font-medium">{{ $record->employment_status ?? '—' }}</div></div>
                <div><div class="text-xs text-zinc-400">Education Level</div><div class="font-medium">{{ $record->education_level ?? '—' }}</div></div>
                <div><div class="text-xs text-zinc-400">Living Situation</div><div class="font-medium">{{ $record->living_situation ?? '—' }}</div></div>
                <div><div class="text-xs text-zinc-400">Veteran Status</div><div class="font-medium">{{ $record->veteran_status ? 'U.S. Veteran' : 'No' }}</div></div>
            </div>
        </flux:card>

        {{-- Section 3: Presenting Problem --}}
        @if ($record->chief_complaint || $record->presenting_problem || $record->previous_treatments || $record->goals_for_treatment)
            <flux:card class="space-y-3">
                <div class="flex items-center gap-2"><flux:icon name="chat-bubble-left-right" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Presenting Problem') }}</flux:heading></div>
                <flux:separator />
                @if ($record->chief_complaint)
                    <div><div class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Chief Complaint</div><p class="mt-1 text-sm whitespace-pre-wrap text-zinc-700 dark:text-zinc-300">{{ $record->chief_complaint }}</p></div>
                @endif
                @if ($record->presenting_problem)
                    <div><div class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Description</div><p class="mt-1 text-sm whitespace-pre-wrap text-zinc-700 dark:text-zinc-300">{{ $record->presenting_problem }}</p></div>
                @endif
                @if ($record->duration_of_problem)
                    <div><div class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Duration</div><p class="mt-1 text-sm text-zinc-700 dark:text-zinc-300">{{ $record->duration_of_problem }}</p></div>
                @endif
                @if ($record->previous_treatments)
                    <div><div class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Previous Treatments</div><p class="mt-1 text-sm whitespace-pre-wrap text-zinc-700 dark:text-zinc-300">{{ $record->previous_treatments }}</p></div>
                @endif
                @if ($record->goals_for_treatment)
                    <div><div class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Goals for Treatment</div><p class="mt-1 text-sm whitespace-pre-wrap text-zinc-700 dark:text-zinc-300">{{ $record->goals_for_treatment }}</p></div>
                @endif
            </flux:card>
        @endif

        {{-- Section 4: Mental Status --}}
        <flux:card class="space-y-4">
            <div class="flex items-center gap-2"><flux:icon name="face-smile" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Mental Status') }}</flux:heading></div>
            <flux:separator />
            <div class="space-y-4">
                @foreach ($this->mentalStatusCategories as $key => $cat)
                    @php $entry = $record->mental_status[$key] ?? []; $selected = $entry['selected'] ?? []; $other = $entry['other'] ?? ''; @endphp
                    @if (!empty($selected))
                        <div>
                            <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ $cat['label'] }}</div>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($selected as $item)
                                    <flux:badge color="blue">{{ $item }}</flux:badge>
                                @endforeach
                                @if ($other)
                                    <flux:badge color="zinc">Other: {{ $other }}</flux:badge>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </flux:card>

        {{-- Section 5: Substance Use History --}}
        @php
            $substanceRows = array_filter($record->substance_use ?? [], fn ($r) =>
                ($r['primary'] ?? false) || ($r['current_use'] ?? false)
                || !empty($r['age_first_use']) || !empty($r['frequency']) || !empty($r['route']) || !empty($r['last_use_date'])
            );
        @endphp
        @if (!empty($substanceRows))
            <flux:card class="space-y-3">
                <div class="flex items-center gap-2"><flux:icon name="beaker" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Substance Use History') }}</flux:heading></div>
                <flux:separator />
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                <th class="pb-2 text-left font-semibold text-zinc-500">Substance</th>
                                <th class="pb-2 text-center font-semibold text-zinc-500">Primary</th>
                                <th class="pb-2 text-center font-semibold text-zinc-500">Age First Use</th>
                                <th class="pb-2 text-center font-semibold text-zinc-500">Current Use</th>
                                <th class="pb-2 text-left font-semibold text-zinc-500">Last Use</th>
                                <th class="pb-2 text-left font-semibold text-zinc-500">Frequency</th>
                                <th class="pb-2 text-left font-semibold text-zinc-500">Route</th>
                                <th class="pb-2 text-left font-semibold text-zinc-500">Days Abstinent</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach ($substanceRows as $row)
                                <tr>
                                    <td class="py-1.5 pr-3 font-medium text-zinc-700 dark:text-zinc-300">{{ $row['substance'] }}</td>
                                    <td class="py-1.5 text-center">{{ ($row['primary'] ?? false) ? '✓' : '—' }}</td>
                                    <td class="py-1.5 text-center">{{ $row['age_first_use'] ?: '—' }}</td>
                                    <td class="py-1.5 text-center">{{ ($row['current_use'] ?? false) ? '✓' : '—' }}</td>
                                    <td class="py-1.5">{{ $row['last_use_date'] ?: '—' }}</td>
                                    <td class="py-1.5">{{ $row['frequency'] ?: '—' }}</td>
                                    <td class="py-1.5">{{ $row['route'] ?: '—' }}</td>
                                    <td class="py-1.5">{{ $row['days_abstinent'] ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </flux:card>
        @endif

        {{-- Section 6: Medical History --}}
        @if ($record->current_medications || $record->medical_conditions || $record->medication_allergies || $record->other_allergies || $record->hospitalizations)
            <flux:card class="space-y-3">
                <div class="flex items-center gap-2"><flux:icon name="heart" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Medical History') }}</flux:heading></div>
                <flux:separator />
                @foreach (['current_medications' => 'Current Medications', 'medical_conditions' => 'Medical Conditions', 'medication_allergies' => 'Medication Allergies', 'other_allergies' => 'Other Allergies', 'hospitalizations' => 'Hospitalizations / Surgeries'] as $field => $label)
                    @if ($record->$field)
                        <div><div class="text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ $label }}</div><p class="mt-1 text-sm whitespace-pre-wrap text-zinc-700 dark:text-zinc-300">{{ $record->$field }}</p></div>
                    @endif
                @endforeach
            </flux:card>
        @endif

        {{-- Section 7: Psychiatric History --}}
        @if ($record->psychiatric_diagnoses || $record->psychiatric_hospitalizations || $record->psychiatric_medications || $record->psych_provider_name)
            <flux:card class="space-y-3">
                <div class="flex items-center gap-2"><flux:icon name="sparkles" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Psychiatric History') }}</flux:heading></div>
                <flux:separator />
                @foreach (['psychiatric_diagnoses' => 'Psychiatric Diagnoses', 'psychiatric_hospitalizations' => 'Psychiatric Hospitalizations', 'psychiatric_medications' => 'Psychiatric Medications'] as $field => $label)
                    @if ($record->$field)
                        <div><div class="text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ $label }}</div><p class="mt-1 text-sm whitespace-pre-wrap text-zinc-700 dark:text-zinc-300">{{ $record->$field }}</p></div>
                    @endif
                @endforeach
                @if ($record->psych_provider_name || $record->psych_provider_phone)
                    <div><div class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Psychiatric Provider</div>
                        <p class="mt-1 text-sm text-zinc-700 dark:text-zinc-300">{{ $record->psych_provider_name ?? '—' }}{{ $record->psych_provider_phone ? ' · ' . $record->psych_provider_phone : '' }}</p>
                    </div>
                @endif
            </flux:card>
        @endif

        {{-- Section 8: Psychosocial / Legal History --}}
        @if ($record->legal_status || $record->legal_history || $record->employment_history || $record->family_history || $record->trauma_history || $record->social_support || $record->cultural_considerations)
            <flux:card class="space-y-3">
                <div class="flex items-center gap-2"><flux:icon name="scale" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Psychosocial / Legal History') }}</flux:heading></div>
                <flux:separator />
                @if ($record->legal_status)
                    <div><div class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Legal Status</div><p class="mt-1 text-sm text-zinc-700 dark:text-zinc-300">{{ $record->legal_status }}</p></div>
                @endif
                @foreach (['legal_history' => 'Legal History', 'employment_history' => 'Employment History', 'family_history' => 'Family History', 'trauma_history' => 'Trauma / Abuse History', 'social_support' => 'Social Support System', 'cultural_considerations' => 'Cultural / Spiritual Considerations'] as $field => $label)
                    @if ($record->$field)
                        <div><div class="text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ $label }}</div><p class="mt-1 text-sm whitespace-pre-wrap text-zinc-700 dark:text-zinc-300">{{ $record->$field }}</p></div>
                    @endif
                @endforeach
            </flux:card>
        @endif

        {{-- Section 9: Risk Assessment --}}
        <flux:card class="space-y-3">
            <div class="flex items-center gap-2"><flux:icon name="shield-exclamation" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Risk Assessment') }}</flux:heading></div>
            <flux:separator />
            <div class="grid grid-cols-2 gap-x-8 gap-y-3 text-sm sm:grid-cols-3">
                <div>
                    <div class="text-xs text-zinc-400">Suicidal Ideation</div>
                    @if ($record->suicidal_ideation)
                        <flux:badge size="sm" :color="$record->suicidal_ideation === 'active' ? 'red' : ($record->suicidal_ideation === 'passive' ? 'yellow' : 'green')">{{ ucfirst($record->suicidal_ideation) }}</flux:badge>
                    @else
                        <div class="font-medium">—</div>
                    @endif
                </div>
                <div>
                    <div class="text-xs text-zinc-400">Suicide Plan</div>
                    @if ($record->suicide_plan)
                        <flux:badge size="sm" color="red">Yes</flux:badge>
                    @else
                        <flux:badge size="sm" color="green">No</flux:badge>
                    @endif
                </div>
                <div>
                    <div class="text-xs text-zinc-400">Homicidal Ideation</div>
                    @if ($record->homicidal_ideation)
                        <flux:badge size="sm" :color="$record->homicidal_ideation === 'active' ? 'red' : ($record->homicidal_ideation === 'passive' ? 'yellow' : 'green')">{{ ucfirst($record->homicidal_ideation) }}</flux:badge>
                    @else
                        <div class="font-medium">—</div>
                    @endif
                </div>
                <div>
                    <div class="text-xs text-zinc-400">Overall Risk Level</div>
                    @if ($record->risk_level)
                        <flux:badge size="sm" :color="match($record->risk_level) {
                            'low'      => 'green',
                            'moderate' => 'yellow',
                            'high'     => 'amber',
                            'imminent' => 'red',
                            default    => 'zinc',
                        }">{{ ucfirst($record->risk_level) }}</flux:badge>
                    @else
                        <div class="font-medium">—</div>
                    @endif
                </div>
            </div>
            @if ($record->suicide_history)
                <div><div class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Suicide / Self-Harm History</div><p class="mt-1 text-sm whitespace-pre-wrap text-zinc-700 dark:text-zinc-300">{{ $record->suicide_history }}</p></div>
            @endif
            @if ($record->self_harm_history)
                <div><div class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Self-Harm History</div><p class="mt-1 text-sm whitespace-pre-wrap text-zinc-700 dark:text-zinc-300">{{ $record->self_harm_history }}</p></div>
            @endif
        </flux:card>

        {{-- Section 10: Diagnostic Summary --}}
        @if ($record->clinical_summary || $record->primary_diagnosis || $record->secondary_diagnosis || $record->asam_level || $record->level_of_care || $record->treatment_goals || $record->recommendations)
            <flux:card class="space-y-3">
                <div class="flex items-center gap-2"><flux:icon name="document-magnifying-glass" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Diagnostic Summary & Recommendations') }}</flux:heading></div>
                <flux:separator />
                @if ($record->primary_diagnosis || $record->secondary_diagnosis || $record->asam_level || $record->level_of_care)
                    <div class="grid grid-cols-2 gap-x-8 gap-y-2 text-sm">
                        @if ($record->primary_diagnosis)
                            <div><div class="text-xs text-zinc-400">Primary Diagnosis</div><div class="font-medium">{{ $record->primary_diagnosis }}</div></div>
                        @endif
                        @if ($record->secondary_diagnosis)
                            <div><div class="text-xs text-zinc-400">Secondary Diagnosis</div><div class="font-medium">{{ $record->secondary_diagnosis }}</div></div>
                        @endif
                        @if ($record->asam_level)
                            <div><div class="text-xs text-zinc-400">ASAM Level</div><div class="font-medium">{{ $record->asam_level }}</div></div>
                        @endif
                        @if ($record->level_of_care)
                            <div><div class="text-xs text-zinc-400">Level of Care</div><div class="font-medium">{{ $record->level_of_care }}</div></div>
                        @endif
                    </div>
                @endif
                @foreach (['clinical_summary' => 'Clinical Summary', 'treatment_goals' => 'Treatment Goals', 'recommendations' => 'Recommendations & Referrals'] as $field => $label)
                    @if ($record->$field)
                        <div><div class="text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ $label }}</div><p class="mt-1 text-sm whitespace-pre-wrap text-zinc-700 dark:text-zinc-300">{{ $record->$field }}</p></div>
                    @endif
                @endforeach
            </flux:card>
        @endif

        {{-- Signers --}}
        <flux:card class="space-y-3">
            <div class="flex items-center gap-2"><flux:icon name="users" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Signers') }}</flux:heading></div>
            <flux:separator />
            @if (count($this->signerNames) > 0)
                <div class="flex flex-wrap gap-2">@foreach ($this->signerNames as $name)<flux:badge color="blue">{{ $name }}</flux:badge>@endforeach</div>
            @else
                <flux:text class="text-sm text-zinc-400">{{ __('No signers.') }}</flux:text>
            @endif
        </flux:card>

        {{-- Employee Signature --}}
        <flux:card class="space-y-3">
            <div class="flex items-center gap-2"><flux:icon name="pencil" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Employee Signature') }}</flux:heading></div>
            <flux:separator />
            @php $sigUri = $record->signature?->getDataUri() ?? $record->raw_signature_data; @endphp
            @if ($sigUri)
                <div class="flex items-start gap-5">
                    <div class="rounded-md bg-white p-3 dark:bg-zinc-900"><img src="{{ $sigUri }}" alt="Signature" class="max-h-20 max-w-52 object-contain" /></div>
                    <div><p class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $record->recorder?->name ?? '—' }}</p><p class="text-xs text-zinc-400">{{ $record->created_at->format('M d, Y g:i A') }}</p></div>
                </div>
            @else
                <flux:badge color="zinc">{{ __('Not signed') }}</flux:badge>
            @endif
        </flux:card>

    </div>
</flux:main>
