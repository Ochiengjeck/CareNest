<?php

use App\Models\NursingAssessment;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Nursing Assessment')]
class extends Component {
    #[Locked]
    public int $recordId;

    public function mount(NursingAssessment $nursingAssessment): void
    {
        abort_unless(auth()->user()->can('view-residents'), 403);
        $this->recordId = $nursingAssessment->id;
    }

    #[Computed]
    public function record(): NursingAssessment
    {
        return NursingAssessment::with(['resident', 'recorder'])->findOrFail($this->recordId);
    }
}; ?>

<flux:main>
    @php $record = $this->record; @endphp
    <div class="max-w-4xl space-y-5">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                <flux:button variant="ghost" :href="route('residents.nursing-assessments.index', $record->resident_id)" wire:navigate icon="arrow-left" />
                <div>
                    <flux:heading size="xl">{{ __('Nursing Assessment') }}</flux:heading>
                    <flux:subheading>{{ $record->resident->full_name }}</flux:subheading>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-zinc-200 bg-zinc-50 px-5 py-3 dark:border-zinc-700 dark:bg-zinc-800/40">
            <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $record->assessment_date->format('M d, Y g:i A') }}</span>
            <span class="ml-auto flex items-center gap-1.5 text-sm text-zinc-500">
                <flux:icon name="user" class="size-4" />{{ $record->recorder?->name ?? '—' }}
                <span class="text-zinc-400">&bull;</span>{{ $record->created_at->diffForHumans() }}
            </span>
        </div>

        <div class="rounded-lg border border-blue-100 bg-blue-50/60 px-5 py-2.5 dark:border-blue-900/40 dark:bg-blue-950/20">
            <div class="flex flex-wrap gap-x-8 gap-y-1 text-sm">
                <div><span class="text-zinc-400">AHCCCS ID:</span> <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $record->resident->ahcccs_id ?? '—' }}</span></div>
                <div><span class="text-zinc-400">DOB:</span> <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $record->resident->date_of_birth->format('M d, Y') }}</span></div>
                <div><span class="text-zinc-400">Admitted:</span> <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $record->resident->admission_date->format('M d, Y') }}</span></div>
            </div>
        </div>

        {{-- Risk Level --}}
        <flux:card class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2"><flux:icon name="exclamation-triangle" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Risk Level') }}</flux:heading></div>
                <flux:badge :color="$record->risk_level_color" size="lg">{{ $record->risk_level_label }}</flux:badge>
            </div>
            @if ($record->risk_assessment_notes)
                <flux:separator />
                <flux:text class="text-sm text-zinc-700 dark:text-zinc-300" style="white-space: pre-wrap;">{{ $record->risk_assessment_notes }}</flux:text>
            @endif
        </flux:card>

        {{-- Safety Screening --}}
        @php $safety = $record->safety_screening ?? []; @endphp
        <flux:card class="space-y-3">
            <div class="flex items-center gap-2"><flux:icon name="shield-check" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Safety Screening') }}</flux:heading></div>
            <flux:separator />
            <div class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
                <div>
                    <div class="text-xs text-zinc-400">{{ __('Suicidal Ideation') }}</div>
                    <flux:badge size="sm" :color="match($safety['suicidal_ideation'] ?? 'none') { 'none' => 'green', 'passive' => 'amber', 'active' => 'red', default => 'zinc' }">
                        {{ ucfirst($safety['suicidal_ideation'] ?? 'None') }}
                    </flux:badge>
                </div>
                <div>
                    <div class="text-xs text-zinc-400">{{ __('Homicidal Ideation') }}</div>
                    <flux:badge size="sm" :color="match($safety['homicidal_ideation'] ?? 'none') { 'none' => 'green', 'passive' => 'amber', 'active' => 'red', default => 'zinc' }">
                        {{ ucfirst($safety['homicidal_ideation'] ?? 'None') }}
                    </flux:badge>
                </div>
            </div>
            @if (!empty($safety['protective_factors']))
                <div>
                    <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ __('Protective Factors') }}</div>
                    <flux:text class="text-sm text-zinc-700 dark:text-zinc-300">{{ $safety['protective_factors'] }}</flux:text>
                </div>
            @endif
        </flux:card>

        {{-- Substance Use --}}
        @php $substance = $record->substance_use_check ?? []; @endphp
        <flux:card class="space-y-3">
            <div class="flex items-center gap-2"><flux:icon name="beaker" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Substance Use Check') }}</flux:heading></div>
            <flux:separator />
            @if (!empty($substance['substances']))
                <div>
                    <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ __('Substances') }}</div>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($substance['substances'] as $s)
                            <flux:badge color="violet">{{ $s }}</flux:badge>
                        @endforeach
                    </div>
                </div>
            @endif
            <div class="grid grid-cols-2 gap-4 text-sm">
                @if (!empty($substance['frequency']))
                    <div><div class="text-xs text-zinc-400">{{ __('Frequency') }}</div><div class="font-medium text-zinc-700 dark:text-zinc-300">{{ $substance['frequency'] }}</div></div>
                @endif
                @if (!empty($substance['last_use']))
                    <div><div class="text-xs text-zinc-400">{{ __('Last Use') }}</div><div class="font-medium text-zinc-700 dark:text-zinc-300">{{ $substance['last_use'] }}</div></div>
                @endif
            </div>
            @if (empty($substance['substances']) && empty($substance['frequency']) && empty($substance['last_use']))
                <flux:text class="text-sm text-zinc-400">{{ __('No substance use recorded.') }}</flux:text>
            @endif
        </flux:card>

        {{-- Physical Condition --}}
        @if ($record->physical_condition)
            <flux:card class="space-y-3">
                <div class="flex items-center gap-2"><flux:icon name="heart" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Physical Condition') }}</flux:heading></div>
                <flux:separator />
                <flux:text class="text-sm text-zinc-700 dark:text-zinc-300" style="white-space: pre-wrap;">{{ $record->physical_condition }}</flux:text>
            </flux:card>
        @endif

        {{-- Nursing Intake Note --}}
        @if ($record->nursing_intake_note)
            <flux:card class="space-y-3">
                <div class="flex items-center gap-2"><flux:icon name="document-text" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Nursing Intake Note') }}</flux:heading></div>
                <flux:separator />
                <flux:text class="text-sm text-zinc-700 dark:text-zinc-300" style="white-space: pre-wrap;">{{ $record->nursing_intake_note }}</flux:text>
            </flux:card>
        @endif

    </div>
</flux:main>
