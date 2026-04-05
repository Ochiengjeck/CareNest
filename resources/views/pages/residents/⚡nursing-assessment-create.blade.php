<?php

use App\Concerns\NursingAssessmentValidationRules;
use App\Models\NursingAssessment;
use App\Models\Resident;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('New Nursing Assessment')]
class extends Component {
    use NursingAssessmentValidationRules;

    #[Locked]
    public int $residentId;

    public string $assessment_date = '';

    // Safety Screening
    public string $suicidal_ideation = 'none';
    public string $homicidal_ideation = 'none';
    public string $protective_factors = '';

    // Substance Use
    public array $substances_used = [];
    public string $substance_frequency = '';
    public string $substance_last_use = '';

    public string $physical_condition = '';
    public string $nursing_intake_note = '';
    public string $risk_level = 'low';
    public string $risk_assessment_notes = '';

    public function mount(Resident $resident): void
    {
        abort_unless(auth()->user()->can('manage-residents'), 403);
        $this->residentId = $resident->id;
        $this->assessment_date = now()->format('Y-m-d\TH:i');
    }

    #[Computed]
    public function resident(): Resident
    {
        return Resident::findOrFail($this->residentId);
    }

    public function save(): void
    {
        $v = $this->validate($this->nursingAssessmentRules());

        NursingAssessment::create([
            'resident_id'      => $this->residentId,
            'assessment_date'  => $v['assessment_date'],
            'safety_screening' => [
                'suicidal_ideation'   => $this->suicidal_ideation,
                'homicidal_ideation'  => $this->homicidal_ideation,
                'protective_factors'  => $this->protective_factors,
            ],
            'substance_use_check' => [
                'substances' => $this->substances_used,
                'frequency'  => $this->substance_frequency,
                'last_use'   => $this->substance_last_use,
            ],
            'physical_condition'    => $v['physical_condition'] ?? null,
            'nursing_intake_note'   => $v['nursing_intake_note'] ?? null,
            'risk_level'            => $v['risk_level'],
            'risk_assessment_notes' => $v['risk_assessment_notes'] ?? null,
            'recorded_by'           => auth()->id(),
        ]);

        session()->flash('status', 'Nursing assessment saved successfully.');
        $this->redirect(route('residents.nursing-assessments.index', $this->residentId), navigate: true);
    }
}; ?>

<flux:main>
    <div class="max-w-4xl space-y-1">

        <div class="mb-6 flex items-center gap-3">
            <flux:button variant="ghost" :href="route('residents.nursing-assessments.index', $this->residentId)" wire:navigate icon="arrow-left" />
            <div>
                <flux:heading size="xl">{{ __('New Nursing Assessment') }}</flux:heading>
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
                    <span class="text-zinc-500 dark:text-zinc-400">Admitted:</span>
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $this->resident->admission_date->format('M d, Y') }}</span>
                </div>
            </div>
        </div>

        <form wire:submit="save" class="space-y-4">

            {{-- Assessment Date/Time --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="clock" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Assessment Date & Time') }}</flux:heading>
                </div>
                <flux:separator />
                <flux:input type="datetime-local" wire:model="assessment_date" :label="__('Assessment Date / Time')" required />
                @error('assessment_date') <flux:text class="text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text> @enderror
            </flux:card>

            {{-- Safety Screening --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="shield-check" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Safety Screening') }}</flux:heading>
                </div>
                <flux:separator />

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <flux:label>{{ __('Suicidal Ideation') }}</flux:label>
                        <div class="mt-2 flex gap-2">
                            @foreach (['none' => 'None', 'passive' => 'Passive', 'active' => 'Active'] as $val => $label)
                                <label class="cursor-pointer select-none">
                                    <input type="radio" wire:model.live="suicidal_ideation" value="{{ $val }}" class="sr-only" />
                                    <span @class([
                                        'inline-block rounded-full border px-3 py-1.5 text-sm font-medium transition',
                                        'border-accent bg-accent/10 text-accent ring-1 ring-accent/30' => $suicidal_ideation === $val,
                                        'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400' => $suicidal_ideation !== $val,
                                    ])>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <flux:label>{{ __('Homicidal Ideation') }}</flux:label>
                        <div class="mt-2 flex gap-2">
                            @foreach (['none' => 'None', 'passive' => 'Passive', 'active' => 'Active'] as $val => $label)
                                <label class="cursor-pointer select-none">
                                    <input type="radio" wire:model.live="homicidal_ideation" value="{{ $val }}" class="sr-only" />
                                    <span @class([
                                        'inline-block rounded-full border px-3 py-1.5 text-sm font-medium transition',
                                        'border-accent bg-accent/10 text-accent ring-1 ring-accent/30' => $homicidal_ideation === $val,
                                        'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400' => $homicidal_ideation !== $val,
                                    ])>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <flux:textarea wire:model="protective_factors" :label="__('Protective Factors')" rows="3"
                    :placeholder="__('e.g. family support, no prior history, willing to engage in treatment...')" />
            </flux:card>

            {{-- Substance Use Check --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="beaker" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Substance Use Check') }}</flux:heading>
                </div>
                <flux:separator />

                <div>
                    <flux:label>{{ __('Substances Used') }}</flux:label>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach (['Alcohol', 'Cannabis', 'Cocaine/Crack', 'Heroin', 'Methamphetamine', 'Opioids (Rx)', 'Benzodiazepines', 'Other'] as $option)
                            <label class="cursor-pointer select-none">
                                <input type="checkbox" wire:model="substances_used" value="{{ $option }}" class="sr-only" />
                                <span class="inline-block rounded-full border px-3 py-1.5 text-sm font-medium transition"
                                    :class="$wire.substances_used.includes('{{ $option }}') ? 'border-accent bg-accent/10 text-accent ring-1 ring-accent/30' : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400'">
                                    {{ $option }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="substance_frequency" :label="__('Frequency of Use')" :placeholder="__('e.g. daily, weekly...')" />
                    <flux:input wire:model="substance_last_use" :label="__('Last Use')" :placeholder="__('e.g. yesterday, 2 weeks ago...')" />
                </div>
            </flux:card>

            {{-- Physical Condition --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="heart" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Physical Condition') }}</flux:heading>
                </div>
                <flux:separator />
                <flux:textarea wire:model="physical_condition" :label="__('Physical Condition Assessment')" rows="4"
                    :placeholder="__('Describe current physical condition, appearance, level of distress...')" />
            </flux:card>

            {{-- Nursing Intake Note --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="document-text" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Nursing Intake Note') }}</flux:heading>
                </div>
                <flux:separator />
                <flux:textarea wire:model="nursing_intake_note" :label="__('Intake Note')" rows="5"
                    :placeholder="__('Summary of nursing triage findings, immediate needs, observations...')" />
            </flux:card>

            {{-- Risk Assessment --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="exclamation-triangle" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Risk Assessment') }}</flux:heading>
                </div>
                <flux:separator />

                <div>
                    <flux:label>{{ __('Overall Risk Level') }}</flux:label>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach (['low' => ['Low', 'green'], 'moderate' => ['Moderate', 'amber'], 'high' => ['High', 'red'], 'imminent' => ['Imminent', 'red']] as $val => [$label, $color])
                            <label class="cursor-pointer select-none">
                                <input type="radio" wire:model.live="risk_level" value="{{ $val }}" class="sr-only" />
                                <span @class([
                                    'inline-block rounded-full border px-4 py-1.5 text-sm font-medium transition',
                                    'border-accent bg-accent/10 text-accent ring-1 ring-accent/30' => $risk_level === $val,
                                    'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400' => $risk_level !== $val,
                                ])>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('risk_level') <flux:text class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text> @enderror
                </div>

                <flux:textarea wire:model="risk_assessment_notes" :label="__('Risk Assessment Notes')" rows="4"
                    :placeholder="__('Describe risk factors, context, and recommended precautions...')" />
            </flux:card>

            <div class="flex justify-end gap-3 pb-4">
                <flux:button variant="ghost" :href="route('residents.nursing-assessments.index', $this->residentId)" wire:navigate>{{ __('Cancel') }}</flux:button>
                <flux:button variant="primary" type="submit" icon="check">{{ __('Save Assessment') }}</flux:button>
            </div>

        </form>
    </div>
</flux:main>
