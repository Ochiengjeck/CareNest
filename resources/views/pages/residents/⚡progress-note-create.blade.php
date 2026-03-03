<?php

use App\Concerns\ShiftProgressNoteValidationRules;
use App\Models\Resident;
use App\Models\ShiftProgressNote;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('New Shift Progress Note')]
class extends Component {
    use ShiftProgressNoteValidationRules;

    #[Locked]
    public int $residentId;

    // Shift timing
    public string $shift_date = '';
    public string $shift_start_time = '';
    public string $shift_end_time = '';

    // Multi-select arrays
    public array $appointment = [];
    public string $appointment_other = '';
    public array $mood = [];
    public string $mood_other = '';
    public array $speech = [];
    public string $speech_other = '';
    public array $behaviors = [];
    public string $behaviors_other = '';
    public array $meals = [];
    public array $snacks = [];
    public array $activities = [];
    public string $activities_other = '';

    // Single-select strings (Yes/No/Refused or Yes/No via radio)
    public string $resident_redirected = '';
    public string $outing_in_community = '';
    public string $therapy_participation = '';
    public string $awol = '';
    public string $welfare_checks = '';
    public string $medication_administered = '';
    public string $meal_preparation = '';
    public string $adls_completed = '';
    public string $prompted_medications = '';
    public string $prompted_adls = '';
    public string $water_temperature_adjusted = '';
    public string $clothing_assistance = '';

    // Summary & signature
    public string $note_summary = '';
    public ?int $signature_id = null;
    public string $rawSignatureData = '';

    public function mount(Resident $resident): void
    {
        abort_unless(auth()->user()->can('manage-residents'), 403);
        $this->residentId = $resident->id;
        $this->shift_date = now()->format('Y-m-d');
    }

    #[Computed]
    public function resident(): Resident
    {
        return Resident::findOrFail($this->residentId);
    }

    #[Computed]
    public function mySignatures()
    {
        return auth()->user()->signatures()->orderByDesc('is_active')->orderBy('name')->get();
    }

    #[Computed]
    public function isSigned(): bool
    {
        return $this->signature_id !== null || $this->rawSignatureData !== '';
    }

    public function useSignatureOnly(string $dataUrl, string $penColor): void
    {
        if (! str_starts_with($dataUrl, 'data:image/')) {
            return;
        }

        $this->rawSignatureData = $dataUrl;
    }

    public function useAndSaveSignature(string $dataUrl, string $penColor): void
    {
        if (! str_starts_with($dataUrl, 'data:image/')) {
            return;
        }

        $user = auth()->user();
        $isFirst = ! $user->signatures()->exists();

        $sig = $user->signatures()->create([
            'name'           => 'Shift Note — ' . now()->format('M d, Y'),
            'pen_color'      => $penColor,
            'signature_data' => $dataUrl,
            'is_active'      => $isFirst,
        ]);

        $this->signature_id = $sig->id;
        $this->rawSignatureData = '';
        unset($this->mySignatures);
    }

    public function clearInlineSignature(): void
    {
        $this->rawSignatureData = '';
    }

    public function save(): void
    {
        if (! $this->isSigned) {
            $this->addError('signature', 'A signature is required to save this note.');
            return;
        }

        $validated = $this->validate($this->shiftProgressNoteRules());

        // Convert string radio values to booleans/null for boolean DB columns
        $boolFields = [
            'resident_redirected', 'outing_in_community', 'awol', 'welfare_checks',
            'adls_completed', 'prompted_medications', 'prompted_adls',
            'water_temperature_adjusted', 'clothing_assistance',
        ];

        foreach ($boolFields as $field) {
            if (isset($validated[$field]) && $validated[$field] !== '' && $validated[$field] !== null) {
                $validated[$field] = (bool) $validated[$field];
            } else {
                $validated[$field] = null;
            }
        }

        ShiftProgressNote::create(array_merge($validated, [
            'resident_id'        => $this->residentId,
            'recorded_by'        => auth()->id(),
            'raw_signature_data' => ($this->signature_id === null && $this->rawSignatureData !== '')
                ? $this->rawSignatureData
                : null,
        ]));

        session()->flash('status', 'Shift progress note saved successfully.');
        $this->redirect(route('residents.progress-notes', $this->residentId), navigate: true);
    }
}; ?>

<flux:main>
    <div class="max-w-3xl space-y-1">

        {{-- Header --}}
        <div class="mb-6 flex items-center gap-3">
            <flux:button variant="ghost" :href="route('residents.progress-notes', $this->residentId)" wire:navigate icon="arrow-left" />
            <div>
                <flux:heading size="xl">{{ __('New Shift Progress Note') }}</flux:heading>
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
                @if ($this->resident->room_number)
                    <div class="flex items-center gap-1.5">
                        <flux:icon name="home" class="size-4 text-blue-400" />
                        <span class="text-zinc-500 dark:text-zinc-400">Room:</span>
                        <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $this->resident->room_number }}</span>
                    </div>
                @endif
                <div class="flex items-center gap-1.5">
                    <flux:icon name="clock" class="size-4 text-blue-400" />
                    <span class="text-zinc-500 dark:text-zinc-400">Today:</span>
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ now()->format('m/d/Y') }}</span>
                </div>
            </div>
        </div>

        <form wire:submit="save" class="space-y-4">

            {{-- 1. Shift Details --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="clock" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Shift Details') }}</flux:heading>
                </div>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-3">
                    <flux:input wire:model="shift_date" :label="__('Shift Date')" type="date" required />
                    <flux:input wire:model="shift_start_time" :label="__('Start Time')" type="time" />
                    <flux:input wire:model="shift_end_time" :label="__('End Time')" type="time" />
                </div>
            </flux:card>

            {{-- 2. Appointment --}}
            @php
                $appointmentOptions = ['no'=>'NO','pc'=>'PC','pcp'=>'PCP','psych'=>'Psych','specialist'=>'Specialist Visit','dental'=>'Dental','er'=>'Emergency Room','urgent_care'=>'Urgent Care','other'=>'Other'];
            @endphp
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="building-office-2" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Appointment') }}</flux:heading>
                </div>
                <flux:separator />
                <div class="flex flex-wrap gap-2">
                    @foreach ($appointmentOptions as $key => $label)
                        <label wire:key="appt-{{ $key }}" class="cursor-pointer select-none">
                            <input type="checkbox" wire:model="appointment" value="{{ $key }}" class="sr-only" />
                            <span
                                class="inline-block rounded-full border px-3 py-1.5 text-sm font-medium transition"
                                :class="$wire.appointment.includes('{{ $key }}')
                                    ? 'border-accent bg-accent/10 text-accent ring-1 ring-accent/30'
                                    : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600'"
                            >{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @if (in_array('other', $appointment))
                    <flux:input wire:model="appointment_other" :label="__('Specify other')" placeholder="Describe appointment..." />
                @endif
            </flux:card>

            {{-- 3. Mood --}}
            @php
                $moodOptions = ['appropriate'=>'Appropriate','anxious'=>'Anxious','worry'=>'Worry','sad'=>'Sad','depressed'=>'Depressed','irritable'=>'Irritable','angry'=>'Angry','fearful'=>'Fearful','other'=>'Other'];
            @endphp
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="face-smile" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Mood') }}</flux:heading>
                </div>
                <flux:separator />
                <div class="flex flex-wrap gap-2">
                    @foreach ($moodOptions as $key => $label)
                        <label wire:key="mood-{{ $key }}" class="cursor-pointer select-none">
                            <input type="checkbox" wire:model="mood" value="{{ $key }}" class="sr-only" />
                            <span
                                class="inline-block rounded-full border px-3 py-1.5 text-sm font-medium transition"
                                :class="$wire.mood.includes('{{ $key }}')
                                    ? 'border-accent bg-accent/10 text-accent ring-1 ring-accent/30'
                                    : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600'"
                            >{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @if (in_array('other', $mood))
                    <flux:input wire:model="mood_other" :label="__('Specify other')" placeholder="Describe mood..." />
                @endif
            </flux:card>

            {{-- 4. Speech --}}
            @php
                $speechOptions = ['appropriate'=>'Appropriate','selective_mute'=>'Selective Mute','quiet'=>'Quiet','nonverbal'=>'Nonverbal','hyperverbal'=>'Hyperverbal','other'=>'Other'];
            @endphp
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="chat-bubble-left-right" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Speech') }}</flux:heading>
                </div>
                <flux:separator />
                <div class="flex flex-wrap gap-2">
                    @foreach ($speechOptions as $key => $label)
                        <label wire:key="speech-{{ $key }}" class="cursor-pointer select-none">
                            <input type="checkbox" wire:model="speech" value="{{ $key }}" class="sr-only" />
                            <span
                                class="inline-block rounded-full border px-3 py-1.5 text-sm font-medium transition"
                                :class="$wire.speech.includes('{{ $key }}')
                                    ? 'border-accent bg-accent/10 text-accent ring-1 ring-accent/30'
                                    : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600'"
                            >{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @if (in_array('other', $speech))
                    <flux:input wire:model="speech_other" :label="__('Specify other')" placeholder="Describe speech pattern..." />
                @endif
            </flux:card>

            {{-- 5. Behaviors --}}
            @php
                $behaviorOptions = ['appropriate'=>'Appropriate','verbal_aggression'=>'Verbal Aggression','physical_aggression'=>'Physical Aggression','internal_stimuli'=>'Responding to Internal Stimuli','isolation'=>'Isolation','obsession'=>'Obsession','manipulative'=>'Manipulative','impulsive'=>'Impulsive','poor_boundaries'=>'Poor Boundaries','sexual_maladaptive'=>'Sexual Maladaptive Behaviors','other'=>'Other'];
            @endphp
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="exclamation-triangle" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Behaviors') }}</flux:heading>
                </div>
                <flux:separator />
                <div class="flex flex-wrap gap-2">
                    @foreach ($behaviorOptions as $key => $label)
                        <label wire:key="behav-{{ $key }}" class="cursor-pointer select-none">
                            <input type="checkbox" wire:model="behaviors" value="{{ $key }}" class="sr-only" />
                            <span
                                class="inline-block rounded-full border px-3 py-1.5 text-sm font-medium transition"
                                :class="$wire.behaviors.includes('{{ $key }}')
                                    ? 'border-accent bg-accent/10 text-accent ring-1 ring-accent/30'
                                    : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600'"
                            >{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @if (in_array('other', $behaviors))
                    <flux:input wire:model="behaviors_other" :label="__('Specify other')" placeholder="Describe behavior..." />
                @endif
            </flux:card>

            {{-- 6. Quick Checks --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="clipboard-document-check" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Quick Checks') }}</flux:heading>
                </div>
                <flux:separator />
                <div class="grid gap-5 sm:grid-cols-2">
                    @php
                        $quickChecks = [
                            ['field' => 'resident_redirected',  'label' => 'Resident redirected on behaviors?'],
                            ['field' => 'outing_in_community',  'label' => 'Outing in community?'],
                            ['field' => 'awol',                 'label' => 'AWOL?'],
                            ['field' => 'welfare_checks',       'label' => 'Health & welfare checks (30–60 min)?'],
                        ];
                    @endphp
                    @foreach ($quickChecks as $check)
                        <div class="space-y-2">
                            <flux:label>{{ __($check['label']) }}</flux:label>
                            <div class="flex gap-2">
                                @foreach (['1' => 'Yes', '0' => 'No'] as $val => $lbl)
                                    <label wire:key="{{ $check['field'] }}-{{ $val }}" class="cursor-pointer select-none">
                                        <input type="radio" wire:model="{{ $check['field'] }}" value="{{ $val }}" class="sr-only" />
                                        <span
                                            class="inline-block rounded-full border px-4 py-1.5 text-sm font-medium transition"
                                            :class="$wire.{{ $check['field'] }} === '{{ $val }}'
                                                ? 'border-accent bg-accent/10 text-accent ring-1 ring-accent/30'
                                                : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600'"
                                        >{{ $lbl }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </flux:card>

            {{-- 7. Therapy & Medication --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="beaker" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Therapy & Medication') }}</flux:heading>
                </div>
                <flux:separator />
                <div class="grid gap-5 sm:grid-cols-2">
                    @php
                        $triFields = [
                            ['field' => 'therapy_participation',   'label' => 'Participated in therapy session(s)?'],
                            ['field' => 'medication_administered', 'label' => 'Medication administered?'],
                        ];
                    @endphp
                    @foreach ($triFields as $f)
                        <div class="space-y-2">
                            <flux:label>{{ __($f['label']) }}</flux:label>
                            <div class="flex gap-2">
                                @foreach (['yes' => 'Yes', 'no' => 'No', 'refused' => 'Refused'] as $val => $lbl)
                                    <label wire:key="{{ $f['field'] }}-{{ $val }}" class="cursor-pointer select-none">
                                        <input type="radio" wire:model="{{ $f['field'] }}" value="{{ $val }}" class="sr-only" />
                                        <span
                                            class="inline-block rounded-full border px-3 py-1.5 text-sm font-medium transition"
                                            :class="$wire.{{ $f['field'] }} === '{{ $val }}'
                                                ? 'border-accent bg-accent/10 text-accent ring-1 ring-accent/30'
                                                : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600'"
                                        >{{ $lbl }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </flux:card>

            {{-- 8. Meals --}}
            <flux:card class="space-y-5">
                <div class="flex items-center gap-2">
                    <flux:icon name="cake" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Meals') }}</flux:heading>
                </div>
                <flux:separator />

                {{-- Meal preparation --}}
                <div class="space-y-2">
                    <flux:label>{{ __('Meal Preparation') }}</flux:label>
                    <div class="flex flex-wrap gap-2">
                        @foreach (['I'=>'I','HP'=>'HP','R'=>'R','PA'=>'PA','TA'=>'TA','VP'=>'VP','NP'=>'NP'] as $val => $lbl)
                            <label wire:key="meal-prep-{{ $val }}" class="cursor-pointer select-none">
                                <input type="radio" wire:model="meal_preparation" value="{{ $val }}" class="sr-only" />
                                <span
                                    class="inline-flex size-11 items-center justify-center rounded-lg border text-sm font-bold transition"
                                    :class="$wire.meal_preparation === '{{ $val }}'
                                        ? 'border-accent bg-accent/10 text-accent ring-1 ring-accent/30'
                                        : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600'"
                                >{{ $lbl }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="flex flex-wrap gap-x-4 gap-y-0.5 text-xs text-zinc-400 dark:text-zinc-500">
                        <span><strong>I</strong> = Independent</span>
                        <span><strong>HP</strong> = Home Pass</span>
                        <span><strong>R</strong> = Refused</span>
                        <span><strong>PA</strong> = Partial Assist</span>
                        <span><strong>TA</strong> = Total Assist</span>
                        <span><strong>VP</strong> = Verbal Prompt</span>
                        <span><strong>NP</strong> = No Prompt</span>
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    {{-- Meals --}}
                    <div class="space-y-2">
                        <flux:label>{{ __('Meals Offered & Taken') }}</flux:label>
                        <div class="flex flex-wrap gap-2">
                            @foreach (['breakfast_eaten'=>'Breakfast Eaten','lunch_eaten'=>'Lunch Eaten','dinner_eaten'=>'Dinner Eaten','meal_refused'=>'Meal Refused'] as $key => $label)
                                <label wire:key="meal-{{ $key }}" class="cursor-pointer select-none">
                                    <input type="checkbox" wire:model="meals" value="{{ $key }}" class="sr-only" />
                                    <span
                                        class="inline-block rounded-full border px-3 py-1.5 text-sm font-medium transition"
                                        :class="$wire.meals.includes('{{ $key }}')
                                            ? 'border-accent bg-accent/10 text-accent ring-1 ring-accent/30'
                                            : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600'"
                                    >{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Snacks --}}
                    <div class="space-y-2">
                        <flux:label>{{ __('Snacks Offered & Taken') }}</flux:label>
                        <div class="flex flex-wrap gap-2">
                            @foreach (['taken'=>'Taken','refused'=>'Refused'] as $key => $label)
                                <label wire:key="snack-{{ $key }}" class="cursor-pointer select-none">
                                    <input type="checkbox" wire:model="snacks" value="{{ $key }}" class="sr-only" />
                                    <span
                                        class="inline-block rounded-full border px-3 py-1.5 text-sm font-medium transition"
                                        :class="$wire.snacks.includes('{{ $key }}')
                                            ? 'border-accent bg-accent/10 text-accent ring-1 ring-accent/30'
                                            : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600'"
                                    >{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </flux:card>

            {{-- 9. ADLs & Prompts --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="user-circle" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('ADLs & Prompts') }}</flux:heading>
                </div>
                <flux:separator />
                <div class="grid gap-5 sm:grid-cols-2">
                    @php
                        $adlChecks = [
                            ['field' => 'adls_completed',             'label' => 'ADLs completed?'],
                            ['field' => 'prompted_medications',       'label' => 'Prompted to take medications?'],
                            ['field' => 'prompted_adls',              'label' => 'Prompted to complete ADLs?'],
                            ['field' => 'water_temperature_adjusted', 'label' => 'Water temperature adjusted?'],
                            ['field' => 'clothing_assistance',        'label' => 'Assisted selecting clothing?'],
                        ];
                    @endphp
                    @foreach ($adlChecks as $check)
                        <div class="space-y-2">
                            <flux:label>{{ __($check['label']) }}</flux:label>
                            <div class="flex gap-2">
                                @foreach (['1' => 'Yes', '0' => 'No'] as $val => $lbl)
                                    <label wire:key="{{ $check['field'] }}-{{ $val }}" class="cursor-pointer select-none">
                                        <input type="radio" wire:model="{{ $check['field'] }}" value="{{ $val }}" class="sr-only" />
                                        <span
                                            class="inline-block rounded-full border px-4 py-1.5 text-sm font-medium transition"
                                            :class="$wire.{{ $check['field'] }} === '{{ $val }}'
                                                ? 'border-accent bg-accent/10 text-accent ring-1 ring-accent/30'
                                                : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600'"
                                        >{{ $lbl }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </flux:card>

            {{-- 10. Activities --}}
            @php
                $activityOptions = ['journaling'=>'Journaling','coloring'=>'Coloring','socializing'=>'Socializing','board_games'=>'Board Games','park'=>'Park','arts_crafts'=>'Arts & Crafts','other'=>'Other'];
            @endphp
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="sparkles" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Activities') }}</flux:heading>
                </div>
                <flux:separator />
                <div class="flex flex-wrap gap-2">
                    @foreach ($activityOptions as $key => $label)
                        <label wire:key="act-{{ $key }}" class="cursor-pointer select-none">
                            <input type="checkbox" wire:model="activities" value="{{ $key }}" class="sr-only" />
                            <span
                                class="inline-block rounded-full border px-3 py-1.5 text-sm font-medium transition"
                                :class="$wire.activities.includes('{{ $key }}')
                                    ? 'border-accent bg-accent/10 text-accent ring-1 ring-accent/30'
                                    : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600'"
                            >{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @if (in_array('other', $activities))
                    <flux:input wire:model="activities_other" :label="__('Specify other')" placeholder="Describe activity..." />
                @endif
            </flux:card>

            {{-- 11. Shift Note Summary --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="document-text" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Shift Note Summary') }}</flux:heading>
                </div>
                <flux:separator />
                <flux:textarea
                    wire:model="note_summary"
                    rows="7"
                    placeholder="{{ __('Summarize the shift — resident\'s condition, engagement, any concerns or follow-up needed...') }}"
                />
            </flux:card>

            {{-- 12. Signature --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="pencil" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Signature') }}</flux:heading>
                </div>
                <flux:separator />

                @if ($this->mySignatures->isEmpty())
                    {{-- No saved signatures: show inline signature pad --}}
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Draw your signature below. You can apply it to this note only, or save it to your profile for future use.') }}
                    </flux:text>

                    <div
                        x-data="{
                            drawnUri: @js($rawSignatureData),
                            pad: null,
                            isEmpty: true,
                            penColor: '#000000',
                            get hasDrawing() { return this.drawnUri !== ''; },
                            initPad(canvas) {
                                if (this.pad) return;
                                canvas.width = canvas.offsetWidth;
                                canvas.height = 180;
                                this.pad = new SignaturePad(canvas, {
                                    backgroundColor: 'rgb(255, 255, 255)',
                                    penColor: this.penColor,
                                    minWidth: 1,
                                    maxWidth: 3,
                                });
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
                        {{-- Preview: shown after applying --}}
                        <div x-show="hasDrawing" class="mb-3 rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-700/40 dark:bg-green-900/20">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-2">
                                    <flux:icon name="check-circle" class="size-5 shrink-0 text-green-600 dark:text-green-400" />
                                    <div>
                                        <p class="text-sm font-semibold text-green-800 dark:text-green-300">{{ __('Signature applied') }}</p>
                                        <p class="text-xs text-green-700 dark:text-green-400">{{ __('This signature will be used for this note only.') }}</p>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    class="shrink-0 text-xs text-green-700 underline hover:no-underline dark:text-green-400"
                                    x-on:click="drawnUri = ''; clear(); $wire.call('clearInlineSignature')"
                                >{{ __('Redo') }}</button>
                            </div>
                            <div class="mt-3 rounded-md bg-white p-3 dark:bg-zinc-900">
                                <img :src="drawnUri" class="max-h-16 object-contain" alt="{{ __('Signature preview') }}" />
                            </div>
                        </div>

                        {{-- Pad: shown when no drawing applied yet --}}
                        <div x-show="!hasDrawing" class="space-y-3">
                            {{-- Pen color --}}
                            <div class="flex flex-wrap items-center gap-3">
                                <flux:label>{{ __('Pen Color') }}</flux:label>
                                <input
                                    type="color"
                                    x-model="penColor"
                                    class="h-8 w-10 cursor-pointer rounded border border-zinc-300 bg-white p-0.5 dark:border-zinc-600 dark:bg-zinc-800"
                                    title="{{ __('Pick pen color') }}"
                                />
                                <div class="flex gap-1.5">
                                    @foreach (['#000000', '#1e40af', '#15803d', '#7c3aed', '#b91c1c'] as $preset)
                                        <button
                                            type="button"
                                            class="size-6 rounded-full border-2 border-white ring-1 ring-zinc-300 transition-shadow hover:ring-zinc-500"
                                            style="background-color: {{ $preset }}"
                                            x-on:click="penColor = '{{ $preset }}'"
                                            title="{{ $preset }}"
                                        ></button>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Canvas --}}
                            <div class="overflow-hidden rounded-lg border-2 border-zinc-300 bg-white dark:border-zinc-600">
                                <canvas
                                    x-ref="padCanvas"
                                    style="width: 100%; height: 180px; cursor: crosshair; touch-action: none; display: block;"
                                ></canvas>
                            </div>
                            <flux:text class="text-xs text-zinc-400">{{ __('Draw your signature in the box above.') }}</flux:text>

                            {{-- Actions --}}
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <flux:button type="button" size="sm" variant="ghost" icon="arrow-path" x-on:click="clear()">
                                    {{ __('Clear') }}
                                </flux:button>
                                <div class="flex gap-2">
                                    <flux:button
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        x-on:click="applyOnly()"
                                        x-bind:disabled="isEmpty"
                                    >
                                        {{ __('Use for This Note') }}
                                    </flux:button>
                                    <flux:button
                                        type="button"
                                        size="sm"
                                        variant="primary"
                                        x-on:click="applyAndSave()"
                                        x-bind:disabled="isEmpty"
                                    >
                                        {{ __('Save to Profile & Use') }}
                                    </flux:button>
                                </div>
                            </div>
                            <flux:text class="text-xs text-zinc-400">
                                {{ __('"Use for This Note" applies only to this record. "Save to Profile & Use" also adds it to your signature library.') }}
                            </flux:text>
                        </div>
                    </div>
                @else
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Select your signature for this note. You can manage signatures in Profile Settings.') }}
                    </flux:text>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach ($this->mySignatures as $sig)
                            <label class="cursor-pointer" wire:key="sig-{{ $sig->id }}">
                                <input type="radio" wire:model.number="signature_id" value="{{ $sig->id }}" class="sr-only" />
                                <div
                                    class="rounded-xl border-2 p-3 transition"
                                    :class="$wire.signature_id === {{ $sig->id }}
                                        ? 'border-accent bg-accent/5 ring-2 ring-accent/20'
                                        : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600'"
                                >
                                    <div class="flex h-16 items-center justify-center rounded-lg bg-white p-2 dark:bg-zinc-900">
                                        <img
                                            src="{{ $sig->getDataUri() }}"
                                            alt="{{ $sig->name }}"
                                            class="max-h-full max-w-full object-contain"
                                        />
                                    </div>
                                    <div class="mt-2 flex items-center gap-1.5">
                                        <span
                                            class="size-3 shrink-0 rounded-full border border-zinc-200 dark:border-zinc-600"
                                            style="background-color: {{ $sig->pen_color }}"
                                        ></span>
                                        <span class="flex-1 truncate text-xs font-medium text-zinc-700 dark:text-zinc-300">{{ $sig->name }}</span>
                                    </div>
                                    @if ($sig->is_active)
                                        <div class="mt-1.5">
                                            <flux:badge size="sm" color="blue">{{ __('Default') }}</flux:badge>
                                        </div>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('signature_id')
                        <flux:text class="text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                    @enderror
                @endif

                @error('signature')
                    <flux:text class="text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                @enderror
            </flux:card>

            {{-- Actions --}}
            <div class="flex justify-end gap-3 pb-4">
                <flux:button variant="ghost" :href="route('residents.progress-notes', $this->residentId)" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="primary" type="submit" icon="check">
                    {{ __('Save Note') }}
                </flux:button>
            </div>

        </form>
    </div>
</flux:main>
