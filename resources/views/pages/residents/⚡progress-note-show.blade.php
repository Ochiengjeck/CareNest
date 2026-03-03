<?php

use App\Models\ShiftProgressNote;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Shift Progress Note')]
class extends Component {
    #[Locked]
    public int $noteId;

    public function mount(ShiftProgressNote $shiftProgressNote): void
    {
        abort_unless(auth()->user()->can('view-residents'), 403);
        $this->noteId = $shiftProgressNote->id;
    }

    #[Computed]
    public function note(): ShiftProgressNote
    {
        return ShiftProgressNote::with(['resident', 'recorder', 'signature'])->findOrFail($this->noteId);
    }
}; ?>

<flux:main>
    @php
        $note = $this->note;
        $moodLabels     = ['appropriate'=>'Appropriate','anxious'=>'Anxious','worry'=>'Worry','sad'=>'Sad','depressed'=>'Depressed','irritable'=>'Irritable','angry'=>'Angry','fearful'=>'Fearful','other'=>'Other'];
        $speechLabels   = ['appropriate'=>'Appropriate','selective_mute'=>'Selective Mute','quiet'=>'Quiet','nonverbal'=>'Nonverbal','hyperverbal'=>'Hyperverbal','other'=>'Other'];
        $behaviorLabels = ['appropriate'=>'Appropriate','verbal_aggression'=>'Verbal Aggression','physical_aggression'=>'Physical Aggression','internal_stimuli'=>'Responding to Internal Stimuli','isolation'=>'Isolation','obsession'=>'Obsession','manipulative'=>'Manipulative','impulsive'=>'Impulsive','poor_boundaries'=>'Poor Boundaries','sexual_maladaptive'=>'Sexual Maladaptive Behaviors','other'=>'Other'];
        $apptLabels     = ['no'=>'NO','pc'=>'PC','pcp'=>'PCP','psych'=>'Psych','specialist'=>'Specialist Visit','dental'=>'Dental','er'=>'Emergency Room','urgent_care'=>'Urgent Care','other'=>'Other'];
        $mealLabels     = ['breakfast_eaten'=>'Breakfast Eaten','lunch_eaten'=>'Lunch Eaten','dinner_eaten'=>'Dinner Eaten','meal_refused'=>'Meal Refused'];
        $snackLabels    = ['taken'=>'Taken','refused'=>'Refused'];
        $activityLabels = ['journaling'=>'Journaling','coloring'=>'Coloring','socializing'=>'Socializing','board_games'=>'Board Games','park'=>'Park','arts_crafts'=>'Arts & Crafts','other'=>'Other'];
        $mealPrepLabels = ['I'=>'Independent','HP'=>'Home Pass','R'=>'Refused','PA'=>'Partial Assist','TA'=>'Total Assist','VP'=>'Verbal Prompt','NP'=>'No Prompt'];
        $yn = fn($v) => match($v) { true=>'Yes', false=>'No', default=>'—' };
        $ts = fn($v) => match($v) { 'yes'=>'Yes','no'=>'No','refused'=>'Refused',default=>'—' };
        $shiftColor = match(true) {
            str_contains($note->shift_type_label,'Day')     => 'amber',
            str_contains($note->shift_type_label,'Evening') => 'blue',
            str_contains($note->shift_type_label,'Night')   => 'purple',
            default                                          => 'zinc',
        };
    @endphp

    <div class="max-w-3xl space-y-5">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                <flux:button variant="ghost" :href="route('residents.progress-notes', $note->resident_id)" wire:navigate icon="arrow-left" />
                <div>
                    <flux:heading size="xl">{{ __('Shift Progress Note') }}</flux:heading>
                    <flux:subheading>{{ $note->resident->full_name }}</flux:subheading>
                </div>
            </div>

            {{-- Export button --}}
            <div class="flex items-center gap-2">
                <a href="{{ route('progress-notes.export.pdf', $note->id) }}" target="_blank">
                    <flux:button variant="outline" icon="arrow-down-tray">
                        {{ __('Download PDF') }}
                    </flux:button>
                </a>
            </div>
        </div>

        {{-- Shift summary ribbon --}}
        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-zinc-200 bg-zinc-50 px-5 py-3 dark:border-zinc-700 dark:bg-zinc-800/40">
            <flux:badge :color="$shiftColor">{{ $note->shift_type_label }}</flux:badge>
            <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $note->shift_date->format('M d, Y') }}</span>
            @if ($note->shift_start_time && $note->shift_end_time)
                <span class="text-zinc-500 dark:text-zinc-400">
                    {{ \Carbon\Carbon::parse($note->shift_start_time)->format('g:i A') }}
                    – {{ \Carbon\Carbon::parse($note->shift_end_time)->format('g:i A') }}
                </span>
            @endif
            <span class="ml-auto flex items-center gap-1.5 text-sm text-zinc-500 dark:text-zinc-400">
                <flux:icon name="user" class="size-4" />
                {{ $note->recorder?->name ?? '—' }}
                <span class="text-zinc-400">&bull;</span>
                {{ $note->created_at->diffForHumans() }}
            </span>
        </div>

        {{-- Resident bar --}}
        <div class="rounded-lg border border-blue-100 bg-blue-50/60 px-5 py-2.5 dark:border-blue-900/40 dark:bg-blue-950/20">
            <div class="flex flex-wrap gap-x-8 gap-y-1 text-sm">
                <div><span class="text-zinc-400">DOB:</span> <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $note->resident->date_of_birth->format('M d, Y') }}</span></div>
                <div><span class="text-zinc-400">Admitted:</span> <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $note->resident->admission_date->format('M d, Y') }}</span></div>
                @if ($note->resident->room_number)
                    <div><span class="text-zinc-400">Room:</span> <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $note->resident->room_number }}</span></div>
                @endif
            </div>
        </div>

        {{-- Macro: pill display helper --}}
        @php
            $pills = fn(array $items, array $labelMap) => collect($items)
                ->map(fn($k) => $labelMap[$k] ?? $k)
                ->all();
        @endphp

        {{-- Appointment --}}
        <flux:card class="space-y-3">
            <div class="flex items-center gap-2">
                <flux:icon name="building-office-2" class="size-5 text-zinc-400" />
                <flux:heading size="sm">{{ __('Appointment') }}</flux:heading>
            </div>
            <flux:separator />
            @if (!empty($note->appointment))
                <div class="flex flex-wrap gap-2">
                    @foreach ($note->appointment as $k)
                        <flux:badge color="blue">{{ $apptLabels[$k] ?? $k }}</flux:badge>
                    @endforeach
                </div>
                @if ($note->appointment_other)
                    <flux:text class="text-sm text-zinc-600 dark:text-zinc-300">{{ $note->appointment_other }}</flux:text>
                @endif
            @else
                <flux:text class="text-sm text-zinc-400">{{ __('None recorded') }}</flux:text>
            @endif
        </flux:card>

        {{-- Mood / Speech / Behaviors (3-col on large) --}}
        <div class="grid gap-4 sm:grid-cols-3">
            <flux:card class="space-y-3">
                <div class="flex items-center gap-2">
                    <flux:icon name="face-smile" class="size-4 text-zinc-400" />
                    <flux:heading size="sm">{{ __('Mood') }}</flux:heading>
                </div>
                <flux:separator />
                @if (!empty($note->mood))
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($note->mood as $k)
                            <flux:badge size="sm" color="purple">{{ $moodLabels[$k] ?? $k }}</flux:badge>
                        @endforeach
                    </div>
                    @if ($note->mood_other)<flux:text class="mt-1 text-xs text-zinc-500">{{ $note->mood_other }}</flux:text>@endif
                @else
                    <flux:text class="text-sm text-zinc-400">—</flux:text>
                @endif
            </flux:card>

            <flux:card class="space-y-3">
                <div class="flex items-center gap-2">
                    <flux:icon name="chat-bubble-left-right" class="size-4 text-zinc-400" />
                    <flux:heading size="sm">{{ __('Speech') }}</flux:heading>
                </div>
                <flux:separator />
                @if (!empty($note->speech))
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($note->speech as $k)
                            <flux:badge size="sm" color="sky">{{ $speechLabels[$k] ?? $k }}</flux:badge>
                        @endforeach
                    </div>
                    @if ($note->speech_other)<flux:text class="mt-1 text-xs text-zinc-500">{{ $note->speech_other }}</flux:text>@endif
                @else
                    <flux:text class="text-sm text-zinc-400">—</flux:text>
                @endif
            </flux:card>

            <flux:card class="space-y-3">
                <div class="flex items-center gap-2">
                    <flux:icon name="exclamation-triangle" class="size-4 text-zinc-400" />
                    <flux:heading size="sm">{{ __('Behaviors') }}</flux:heading>
                </div>
                <flux:separator />
                @if (!empty($note->behaviors))
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($note->behaviors as $k)
                            <flux:badge size="sm" color="red">{{ $behaviorLabels[$k] ?? $k }}</flux:badge>
                        @endforeach
                    </div>
                    @if ($note->behaviors_other)<flux:text class="mt-1 text-xs text-zinc-500">{{ $note->behaviors_other }}</flux:text>@endif
                @else
                    <flux:text class="text-sm text-zinc-400">—</flux:text>
                @endif
            </flux:card>
        </div>

        {{-- Quick Checks + Therapy/Meds --}}
        <flux:card class="space-y-3">
            <div class="flex items-center gap-2">
                <flux:icon name="clipboard-document-check" class="size-5 text-zinc-400" />
                <flux:heading size="sm">{{ __('Clinical Checks') }}</flux:heading>
            </div>
            <flux:separator />
            <div class="grid gap-2 sm:grid-cols-3">
                @php
                    $clinicalChecks = [
                        'Resident redirected?'    => $yn($note->resident_redirected),
                        'Outing in community?'    => $yn($note->outing_in_community),
                        'AWOL?'                   => $yn($note->awol),
                        'Welfare checks?'         => $yn($note->welfare_checks),
                        'Therapy participation'   => $ts($note->therapy_participation),
                        'Medication administered' => $ts($note->medication_administered),
                    ];
                @endphp
                @foreach ($clinicalChecks as $label => $value)
                    <div class="flex items-center justify-between rounded-lg border border-zinc-100 px-3 py-2 dark:border-zinc-800">
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __($label) }}</flux:text>
                        <flux:badge size="sm" :color="$value === 'Yes' ? 'green' : ($value === 'No' ? 'zinc' : ($value === 'Refused' ? 'red' : 'zinc'))">
                            {{ $value }}
                        </flux:badge>
                    </div>
                @endforeach
            </div>
        </flux:card>

        {{-- Meals --}}
        <flux:card class="space-y-4">
            <div class="flex items-center gap-2">
                <flux:icon name="cake" class="size-5 text-zinc-400" />
                <flux:heading size="sm">{{ __('Meals') }}</flux:heading>
            </div>
            <flux:separator />
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="space-y-1">
                    <flux:label>{{ __('Preparation') }}</flux:label>
                    @if ($note->meal_preparation)
                        <div class="flex items-baseline gap-2">
                            <span class="inline-flex size-9 items-center justify-center rounded-lg bg-accent/10 text-sm font-bold text-accent">{{ $note->meal_preparation }}</span>
                            <flux:text class="text-xs text-zinc-500">{{ $mealPrepLabels[$note->meal_preparation] ?? '' }}</flux:text>
                        </div>
                    @else
                        <flux:text class="text-sm text-zinc-400">—</flux:text>
                    @endif
                </div>
                <div class="space-y-1">
                    <flux:label>{{ __('Meals') }}</flux:label>
                    @if (!empty($note->meals))
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($note->meals as $k)
                                <flux:badge size="sm" color="green">{{ $mealLabels[$k] ?? $k }}</flux:badge>
                            @endforeach
                        </div>
                    @else
                        <flux:text class="text-sm text-zinc-400">—</flux:text>
                    @endif
                </div>
                <div class="space-y-1">
                    <flux:label>{{ __('Snacks') }}</flux:label>
                    @if (!empty($note->snacks))
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($note->snacks as $k)
                                <flux:badge size="sm" color="green">{{ $snackLabels[$k] ?? $k }}</flux:badge>
                            @endforeach
                        </div>
                    @else
                        <flux:text class="text-sm text-zinc-400">—</flux:text>
                    @endif
                </div>
            </div>
        </flux:card>

        {{-- ADLs --}}
        <flux:card class="space-y-3">
            <div class="flex items-center gap-2">
                <flux:icon name="user-circle" class="size-5 text-zinc-400" />
                <flux:heading size="sm">{{ __('ADLs & Prompts') }}</flux:heading>
            </div>
            <flux:separator />
            <div class="grid gap-2 sm:grid-cols-3">
                @foreach ([
                    'ADLs completed'          => $yn($note->adls_completed),
                    'Prompted medications'    => $yn($note->prompted_medications),
                    'Prompted ADLs'           => $yn($note->prompted_adls),
                    'Water temp adjusted'     => $yn($note->water_temperature_adjusted),
                    'Clothing assistance'     => $yn($note->clothing_assistance),
                ] as $label => $value)
                    <div class="flex items-center justify-between rounded-lg border border-zinc-100 px-3 py-2 dark:border-zinc-800">
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __($label) }}</flux:text>
                        <flux:badge size="sm" :color="$value === 'Yes' ? 'green' : ($value === 'No' ? 'zinc' : 'zinc')">{{ $value }}</flux:badge>
                    </div>
                @endforeach
            </div>
        </flux:card>

        {{-- Activities --}}
        <flux:card class="space-y-3">
            <div class="flex items-center gap-2">
                <flux:icon name="sparkles" class="size-5 text-zinc-400" />
                <flux:heading size="sm">{{ __('Activities') }}</flux:heading>
            </div>
            <flux:separator />
            @if (!empty($note->activities))
                <div class="flex flex-wrap gap-2">
                    @foreach ($note->activities as $k)
                        <flux:badge color="amber">{{ $activityLabels[$k] ?? $k }}</flux:badge>
                    @endforeach
                </div>
                @if ($note->activities_other)
                    <flux:text class="text-sm text-zinc-600 dark:text-zinc-300">{{ $note->activities_other }}</flux:text>
                @endif
            @else
                <flux:text class="text-sm text-zinc-400">{{ __('None recorded') }}</flux:text>
            @endif
        </flux:card>

        {{-- Shift Note Summary --}}
        <flux:card class="space-y-3">
            <div class="flex items-center gap-2">
                <flux:icon name="document-text" class="size-5 text-zinc-400" />
                <flux:heading size="sm">{{ __('Shift Note Summary') }}</flux:heading>
            </div>
            <flux:separator />
            @if ($note->note_summary)
                <p class="whitespace-pre-wrap text-sm leading-relaxed text-zinc-700 dark:text-zinc-300">{{ $note->note_summary }}</p>
            @else
                <flux:text class="text-sm text-zinc-400">{{ __('No summary recorded.') }}</flux:text>
            @endif
        </flux:card>

        {{-- Signature --}}
        <flux:card class="space-y-3">
            <div class="flex items-center gap-2">
                <flux:icon name="pencil" class="size-5 text-zinc-400" />
                <flux:heading size="sm">{{ __('Signature') }}</flux:heading>
            </div>
            <flux:separator />
            @php
                $sigUri = $note->signature?->getDataUri() ?? $note->raw_signature_data;
            @endphp
            @if ($sigUri)
                <div class="flex items-start gap-5">
                    <div>
                        <img
                            src="{{ $sigUri }}"
                            alt="Signature"
                            class="max-h-20 max-w-52 object-contain"
                        />
                    </div>
                    <div class="space-y-0.5">
                        <p class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $note->recorder?->name ?? '—' }}</p>
                        <p class="text-xs text-zinc-400 dark:text-zinc-500">{{ $note->created_at->format('M d, Y g:i A') }}</p>
                    </div>
                </div>
            @else
                <div class="flex items-center gap-2">
                    <flux:badge color="zinc">{{ __('Not signed') }}</flux:badge>
                    <flux:text class="text-sm text-zinc-400">{{ __('No digital signature was attached to this note.') }}</flux:text>
                </div>
            @endif
        </flux:card>

    </div>
</flux:main>
