<?php

use App\Models\Incident;
use App\Models\Medication;
use App\Models\NursingAssessment;
use App\Models\InitialAssessment;
use App\Models\ArtMeeting;
use App\Models\ObservationNote;
use App\Models\Resident;
use App\Models\TherapySession;
use App\Models\Vital;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Resident Details')]
class extends Component {
    #[Locked]
    public int $residentId;

    #[Url]
    public string $activeTab = 'overview';

    public function mount(Resident $resident): void
    {
        $this->residentId = $resident->id;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    #[Computed]
    public function resident(): Resident
    {
        return Resident::with(['carePlans' => fn ($q) => $q->latest(), 'creator', 'updater', 'discharge'])->findOrFail($this->residentId);
    }

    #[Computed]
    public function carePlansCount(): int
    {
        return $this->resident->carePlans->count();
    }

    #[Computed]
    public function activeMedications()
    {
        return Medication::where('resident_id', $this->residentId)->active()->latest()->get();
    }

    #[Computed]
    public function activeMedicationsCount(): int
    {
        return $this->activeMedications->count();
    }

    #[Computed]
    public function recentVitals()
    {
        return Vital::where('resident_id', $this->residentId)->with('recordedBy')->latest('recorded_at')->take(5)->get();
    }

    #[Computed]
    public function recentVitalsCount(): int
    {
        return Vital::where('resident_id', $this->residentId)
            ->where('recorded_at', '>=', now()->subDays(30))
            ->count();
    }

    #[Computed]
    public function incidents()
    {
        return Incident::where('resident_id', $this->residentId)->with('reporter')->latest('occurred_at')->get();
    }

    #[Computed]
    public function incidentsCount(): int
    {
        return $this->incidents->count();
    }

    #[Computed]
    public function workflowSteps(): array
    {
        $id = $this->residentId;
        return [
            ['step' => 1, 'label' => 'Admission', 'description' => 'Resident registered with consent and legal status', 'done' => true, 'route' => null, 'icon' => 'user-plus'],
            ['step' => 2, 'label' => 'Nursing Assessment & Triage', 'description' => 'Safety screening, substance use, physical condition', 'done' => NursingAssessment::where('resident_id', $id)->exists(), 'route' => route('residents.nursing-assessments.index', $id), 'icon' => 'clipboard-document-check'],
            ['step' => 3, 'label' => 'Vital Signs', 'description' => 'Baseline BP, HR, RR, Temp, O2', 'done' => Vital::where('resident_id', $id)->exists(), 'route' => route('vitals.create', ['resident_id' => $id]), 'icon' => 'heart'],
            ['step' => 4, 'label' => 'Psychiatric Evaluation', 'description' => 'MSE, psychiatric history, risk assessment within 24h', 'done' => InitialAssessment::where('resident_id', $id)->exists(), 'route' => route('residents.initial-assessments.index', $id), 'icon' => 'academic-cap'],
            ['step' => 5, 'label' => 'Medications & MAR', 'description' => 'Prescriptions, pharmacy verification, administration record', 'done' => Medication::where('resident_id', $id)->where('status', 'active')->exists(), 'route' => route('residents.mar', $id), 'icon' => 'beaker'],
            ['step' => 6, 'label' => 'Treatment Plan', 'description' => 'Diagnosis, measurable goals, interventions, discharge criteria', 'done' => $this->resident->carePlans()->exists(), 'route' => route('care-plans.create', $id), 'icon' => 'clipboard-document-list'],
            ['step' => 7, 'label' => 'ART Meetings', 'description' => 'Active Treatment Review team meetings (monthly)', 'done' => ArtMeeting::where('resident_id', $id)->exists(), 'route' => route('residents.art-meetings.index', $id), 'icon' => 'users'],
            ['step' => 8, 'label' => 'Observation Notes (RON)', 'description' => 'Safety monitoring, behavioral observation log', 'done' => ObservationNote::where('resident_id', $id)->exists(), 'route' => route('residents.observation-notes.index', $id), 'icon' => 'eye'],
            ['step' => 10, 'label' => 'Counseling / Therapy', 'description' => 'Individual/group therapy sessions (CBT, DBT, etc.)', 'done' => TherapySession::where('resident_id', $id)->exists(), 'route' => route('therapy.sessions.create'), 'icon' => 'chat-bubble-left-right'],
            ['step' => 12, 'label' => 'Discharge Planning', 'description' => 'Housing, follow-up care, medication access, support systems', 'done' => $this->resident->discharge !== null, 'route' => $this->resident->isActive() ? route('residents.discharge', $id) : null, 'icon' => 'arrow-right-start-on-rectangle'],
        ];
    }
}; ?>

<flux:main>
    @php
        $statusColor = match($this->resident->status) {
            'active'     => 'green',
            'discharged' => 'amber',
            'deceased'   => 'red',
            'on_leave'   => 'blue',
            default      => 'zinc',
        };
        $daysAdmitted = $this->resident->admission_date->diffInDays(now());
    @endphp

    <div class="space-y-5">

        {{-- ── HERO ── --}}
        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

            {{-- Coloured accent strip --}}
            <div @class([
                'h-1.5 w-full',
                'bg-green-500' => $this->resident->status === 'active',
                'bg-amber-500' => $this->resident->status === 'discharged',
                'bg-red-500'   => $this->resident->status === 'deceased',
                'bg-blue-500'  => $this->resident->status === 'on_leave',
                'bg-zinc-400'  => !in_array($this->resident->status, ['active','discharged','deceased','on_leave']),
            ])></div>

            <div class="flex flex-col gap-5 p-6 sm:flex-row sm:items-start sm:justify-between">
                {{-- Avatar + identity --}}
                <div class="flex items-start gap-5">
                    <flux:button variant="ghost" :href="route('residents.index')" wire:navigate icon="arrow-left" class="mt-1 shrink-0" />

                    @if($this->resident->photo_path)
                        <img src="{{ Storage::url($this->resident->photo_path) }}" alt=""
                             class="size-20 shrink-0 rounded-2xl object-cover ring-2 ring-zinc-200 dark:ring-zinc-700" />
                    @else
                        <div class="flex size-20 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-zinc-100 to-zinc-200 ring-2 ring-zinc-200 dark:from-zinc-700 dark:to-zinc-800 dark:ring-zinc-700">
                            <span class="text-2xl font-bold text-zinc-500 dark:text-zinc-300">{{ $this->resident->initials() }}</span>
                        </div>
                    @endif

                    <div class="space-y-2">
                        <div>
                            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-50">{{ $this->resident->full_name }}</h1>
                            <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $this->resident->age }} yrs &middot; {{ ucfirst($this->resident->gender) }}
                                @if($this->resident->date_of_birth) &middot; DOB {{ $this->resident->date_of_birth->format('M d, Y') }} @endif
                                @if($this->resident->ahcccs_id) &middot; AHCCCS&nbsp;{{ $this->resident->ahcccs_id }} @endif
                            </p>
                        </div>

                        {{-- Status + clinical flags --}}
                        <div class="flex flex-wrap items-center gap-1.5">
                            <flux:badge :color="$statusColor">
                                {{ str_replace('_', ' ', ucfirst($this->resident->status)) }}
                            </flux:badge>
                            @if($this->resident->room_number)
                                <flux:badge color="zinc" icon="home">
                                    Room {{ $this->resident->room_number }}{{ $this->resident->bed_number ? ' / Bed '.$this->resident->bed_number : '' }}
                                </flux:badge>
                            @endif
                            <flux:badge size="sm" :color="match($this->resident->fall_risk_level) { 'low'=>'green','medium'=>'amber','high'=>'red',default=>'zinc' }">
                                {{ ucfirst($this->resident->fall_risk_level) }} Fall Risk
                            </flux:badge>
                            <flux:badge size="sm" :color="match($this->resident->mobility_status) { 'independent'=>'green','assisted'=>'amber','wheelchair'=>'blue','bedridden'=>'red',default=>'zinc' }">
                                {{ ucfirst($this->resident->mobility_status) }}
                            </flux:badge>
                            @if($this->resident->dnr_status)
                                <flux:badge size="sm" color="red" icon="no-symbol">DNR</flux:badge>
                            @endif
                            @if($this->resident->blood_type)
                                <flux:badge size="sm" color="zinc">{{ $this->resident->blood_type }}</flux:badge>
                            @endif
                        </div>

                        {{-- Quick numbers --}}
                        <div class="flex flex-wrap gap-x-5 gap-y-1 text-sm">
                            <span class="text-zinc-500">Admitted <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $this->resident->admission_date->format('M d, Y') }}</span> <span class="text-zinc-400">({{ $daysAdmitted }}d)</span></span>
                            @if($this->resident->discharge_date)
                                <span class="text-zinc-500">Discharged <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $this->resident->discharge_date->format('M d, Y') }}</span></span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Action buttons --}}
                <div class="flex shrink-0 flex-wrap gap-2">
                    @can('manage-residents')
                        @if($this->resident->status === 'active')
                            <flux:button variant="outline" size="sm" :href="route('residents.discharge', $this->resident)" wire:navigate icon="arrow-right-start-on-rectangle">
                                Discharge
                            </flux:button>
                        @elseif($this->resident->status === 'discharged')
                            @if($this->resident->discharge)
                                <a href="{{ route('residents.discharge.export.pdf', $this->resident->discharge) }}" target="_blank">
                                    <flux:button variant="outline" size="sm" icon="document-arrow-down">Summary PDF</flux:button>
                                </a>
                            @endif
                            <flux:button variant="primary" size="sm" :href="route('residents.readmit', $this->resident)" wire:navigate icon="arrow-left-end-on-rectangle">
                                Readmit
                            </flux:button>
                        @endif
                        <flux:button variant="primary" size="sm" :href="route('residents.edit', $this->resident)" wire:navigate icon="pencil">
                            Edit
                        </flux:button>
                    @endcan
                </div>
            </div>

            {{-- Stats strip --}}
            <div class="grid grid-cols-2 divide-x divide-y divide-zinc-100 border-t border-zinc-100 sm:grid-cols-4 sm:divide-y-0 dark:divide-zinc-800 dark:border-zinc-800">
                @foreach ([
                    ['label' => 'Care Plans',        'value' => $this->carePlansCount,        'icon' => 'clipboard-document-list', 'tab' => 'care-plans',  'color' => 'text-blue-500'],
                    ['label' => 'Active Medications', 'value' => $this->activeMedicationsCount,'icon' => 'beaker',                  'tab' => 'medications', 'color' => 'text-violet-500'],
                    ['label' => 'Vitals (30d)',       'value' => $this->recentVitalsCount,     'icon' => 'heart',                   'tab' => 'vitals',      'color' => 'text-rose-500'],
                    ['label' => 'Incidents',          'value' => $this->incidentsCount,        'icon' => 'exclamation-triangle',    'tab' => 'incidents',   'color' => 'text-amber-500'],
                ] as $stat)
                    <button type="button" wire:click="setTab('{{ $stat['tab'] }}')"
                        class="group flex items-center gap-3 px-5 py-3.5 text-left transition hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <flux:icon :name="$stat['icon']" class="size-5 shrink-0 {{ $stat['color'] }}" />
                        <div>
                            <div class="text-xl font-bold text-zinc-800 dark:text-zinc-100">{{ $stat['value'] }}</div>
                            <div class="text-xs text-zinc-500">{{ $stat['label'] }}</div>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- ── TABS ── --}}
        <flux:navbar>
            <flux:navbar.item wire:click="setTab('overview')" :current="$activeTab==='overview'" icon="information-circle" class="cursor-pointer">Overview</flux:navbar.item>
            <flux:navbar.item wire:click="setTab('care-plans')" :current="$activeTab==='care-plans'" icon="clipboard-document-list" class="cursor-pointer">Care Plans ({{ $this->carePlansCount }})</flux:navbar.item>
            @can('manage-medications')
                <flux:navbar.item wire:click="setTab('medications')" :current="$activeTab==='medications'" icon="beaker" class="cursor-pointer">Medications ({{ $this->activeMedicationsCount }})</flux:navbar.item>
                <flux:navbar.item wire:click="setTab('vitals')" :current="$activeTab==='vitals'" icon="heart" class="cursor-pointer">Vitals</flux:navbar.item>
            @endcan
            @can('manage-incidents')
                <flux:navbar.item wire:click="setTab('incidents')" :current="$activeTab==='incidents'" icon="exclamation-triangle" class="cursor-pointer">Incidents ({{ $this->incidentsCount }})</flux:navbar.item>
            @endcan
        </flux:navbar>

        {{-- ── OVERVIEW TAB ── --}}
        @if($activeTab === 'overview')
        @php
            $completedCount = collect($this->workflowSteps)->where('done', true)->count();
            $totalCount     = count($this->workflowSteps);
        @endphp
        <div class="grid gap-5 lg:grid-cols-5">

            {{-- LEFT: workflow (3/5) --}}
            <div class="space-y-3 lg:col-span-3">

                {{-- Header card --}}
                <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between px-5 pt-5 pb-3">
                        <div>
                            <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-100">BH Workflow</h3>
                            <p class="text-xs text-zinc-400">{{ $completedCount }} of {{ $totalCount }} steps complete</p>
                        </div>
                        <div class="flex size-10 items-center justify-center rounded-xl bg-gradient-to-br from-green-400 to-emerald-500 shadow-sm">
                            <flux:icon name="clipboard-document-check" class="size-5 text-white" />
                        </div>
                    </div>
                    {{-- Segmented progress --}}
                    <div class="flex gap-0.5 px-5 pb-5">
                        @foreach ($this->workflowSteps as $s)
                            <div @class([
                                'h-1.5 flex-1 rounded-full transition-all duration-500',
                                'bg-green-500' => $s['done'],
                                'bg-zinc-200 dark:bg-zinc-700' => ! $s['done'],
                            ])></div>
                        @endforeach
                    </div>
                </div>

                {{-- Step cards --}}
                <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($this->workflowSteps as $step)
                    @php $tag = $step['route'] ? 'a' : 'div'; @endphp
                    <{{ $tag }}
                        @if($step['route']) href="{{ $step['route'] }}" wire:navigate @endif
                        @class([
                            'group relative overflow-hidden rounded-xl border transition-all duration-150',
                            'border-green-200 bg-green-50 dark:border-green-800/40 dark:bg-green-900/10' => $step['done'],
                            'border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900' => ! $step['done'],
                            'hover:shadow-sm hover:border-zinc-300 dark:hover:border-zinc-600 cursor-pointer' => (bool)$step['route'],
                        ])>
                        {{-- Left accent bar --}}
                        <div @class([
                            'absolute left-0 top-0 h-full w-1 rounded-l-xl',
                            'bg-green-500' => $step['done'],
                            'bg-zinc-200 dark:bg-zinc-700' => ! $step['done'],
                        ])></div>

                        <div class="flex items-center gap-3 py-3 pr-3 pl-4">
                            {{-- Step badge --}}
                            <div @class([
                                'flex size-8 shrink-0 items-center justify-center rounded-lg text-xs font-bold shadow-sm',
                                'bg-green-500 text-white' => $step['done'],
                                'bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500' => ! $step['done'],
                            ])>
                                @if ($step['done'])
                                    <flux:icon name="check" class="size-4" />
                                @else
                                    {{ $step['step'] }}
                                @endif
                            </div>

                            {{-- Label + description --}}
                            <div class="min-w-0 flex-1">
                                <p @class([
                                    'truncate text-sm font-semibold',
                                    'text-green-800 dark:text-green-300' => $step['done'],
                                    'text-zinc-600 dark:text-zinc-400' => ! $step['done'],
                                ])>{{ $step['label'] }}</p>
                                @if(isset($step['description']))
                                    <p class="truncate text-xs text-zinc-400 dark:text-zinc-500">{{ $step['description'] }}</p>
                                @endif
                            </div>

                            @if ($step['route'])
                                <flux:icon name="chevron-right" class="size-4 shrink-0 text-zinc-300 transition group-hover:text-zinc-500 dark:group-hover:text-zinc-400" />
                            @endif
                        </div>
                    </{{ $tag }}>
                @endforeach
                </div>

                {{-- Clinical Forms subsection --}}
                <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                        <div class="flex items-center gap-2">
                            <div class="flex size-7 items-center justify-center rounded-lg bg-gradient-to-br from-violet-500 to-indigo-500">
                                <flux:icon name="document-text" class="size-4 text-white" />
                            </div>
                            <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-100">Clinical Forms</h3>
                        </div>
                        <span class="text-xs text-zinc-400">15 forms</span>
                    </div>
                    <div class="grid grid-cols-3 divide-x divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach([
                            ['label' => 'Shift Progress Note',  'icon' => 'clipboard-document-list',          'route' => route('residents.progress-notes', $this->residentId)],
                            ['label' => 'ADL Assessment',       'icon' => 'list-bullet',                       'route' => route('residents.adl.index', $this->residentId)],
                            ['label' => 'Resident Financial Record', 'icon' => 'banknotes',                         'route' => route('residents.financial-transactions.index', $this->residentId)],
                            ['label' => 'Staff Report',              'icon' => 'chat-bubble-bottom-center-text',    'route' => route('residents.staffing-notes.index', $this->residentId)],
                            ['label' => 'Authorization (ROI)',  'icon' => 'document-check',                    'route' => route('residents.authorizations.index', $this->residentId)],
                            ['label' => 'Incident Report',      'icon' => 'exclamation-triangle',              'route' => route('incidents.create', ['resident_id' => $this->residentId])],
                            ['label' => 'Contact Report',       'icon' => 'phone-arrow-up-right',              'route' => route('residents.contact-notes.index', $this->residentId)],
                            ['label' => 'BHP Progress Report',  'icon' => 'sparkles',                          'route' => route('residents.bhp-progress-notes.index', $this->residentId)],
                            ['label' => 'ASAM Checklist',       'icon' => 'clipboard-document-check',          'route' => route('residents.asam-checklists.index', $this->residentId)],
                            ['label' => 'Face Sheet',           'icon' => 'identification',                    'route' => route('residents.face-sheets.index', $this->residentId)],
                            ['label' => 'Crisis Plan',          'icon' => 'shield-check',                      'route' => route('residents.safety-plans.index', $this->residentId)],
                            ['label' => 'Mental Status',        'icon' => 'face-smile',                        'route' => route('residents.mental-status.index', $this->residentId)],
                            ['label' => 'Treatment Refusal',    'icon' => 'no-symbol',                         'route' => route('residents.treatment-refusals.index', $this->residentId)],
                            ['label' => 'Appointment Log',      'icon' => 'calendar-days',                     'route' => route('residents.appointment-logs.index', $this->residentId)],
                            ['label' => 'Resident Intakes',     'icon' => 'user-plus',                         'route' => route('residents.edit', $this->residentId)],
                        ] as $form)
                            <a href="{{ $form['route'] }}" wire:navigate
                               class="group flex flex-col items-center gap-1.5 px-2 py-3 text-center transition hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <flux:icon :name="$form['icon']" class="size-5 text-violet-500 transition group-hover:scale-110" />
                                <span class="text-xs leading-tight text-zinc-500 group-hover:text-zinc-700 dark:group-hover:text-zinc-300">{{ $form['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

            </div>

            {{-- RIGHT: details (2/5) --}}
            <div class="space-y-5 lg:col-span-2">

                {{-- Clinical alerts banner --}}
                @if($this->resident->dnr_status || $this->resident->fall_risk_level === 'high' || $this->resident->allergies)
                    <div class="flex flex-wrap gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 dark:border-red-800/40 dark:bg-red-900/10">
                        <div class="flex items-center gap-2 text-sm font-semibold text-red-700 dark:text-red-400">
                            <flux:icon name="exclamation-triangle" class="size-4 shrink-0" />
                            Clinical Alerts
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @if($this->resident->dnr_status)
                                <flux:badge color="red">DNR — Do Not Resuscitate</flux:badge>
                            @endif
                            @if($this->resident->fall_risk_level === 'high')
                                <flux:badge color="red">High Fall Risk</flux:badge>
                            @endif
                            @if($this->resident->allergies)
                                <flux:badge color="red">Allergies: {{ Str::limit($this->resident->allergies, 60) }}</flux:badge>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Identity + Contact --}}
                <flux:card class="space-y-4 p-5">
                    <div class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-zinc-400">
                        <flux:icon name="user" class="size-4" />
                        Personal
                    </div>
                    <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm sm:grid-cols-3">
                        <div><p class="text-xs text-zinc-400">Date of Birth</p><p class="font-medium">{{ $this->resident->date_of_birth->format('M d, Y') }}</p></div>
                        <div><p class="text-xs text-zinc-400">Gender</p><p class="font-medium">{{ ucfirst($this->resident->gender) }}</p></div>
                        <div><p class="text-xs text-zinc-400">Blood Type</p><p class="font-medium">{{ $this->resident->blood_type ?? '—' }}</p></div>
                        @if($this->resident->phone)
                            <div><p class="text-xs text-zinc-400">Phone</p><p class="font-medium">{{ $this->resident->phone }}</p></div>
                        @endif
                        @if($this->resident->email)
                            <div class="sm:col-span-2"><p class="text-xs text-zinc-400">Email</p><p class="font-medium">{{ $this->resident->email }}</p></div>
                        @endif
                    </div>
                </flux:card>

                {{-- Medical --}}
                <flux:card class="space-y-4 p-5">
                    <div class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-zinc-400">
                        <flux:icon name="heart" class="size-4" />
                        Medical
                    </div>
                    <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm sm:grid-cols-3">
                        <div>
                            <p class="text-xs text-zinc-400">Mobility</p>
                            <flux:badge size="sm" :color="match($this->resident->mobility_status) { 'independent'=>'green','assisted'=>'amber','wheelchair'=>'blue','bedridden'=>'red',default=>'zinc' }">
                                {{ ucfirst($this->resident->mobility_status) }}
                            </flux:badge>
                        </div>
                        <div>
                            <p class="text-xs text-zinc-400">Fall Risk</p>
                            <flux:badge size="sm" :color="match($this->resident->fall_risk_level) { 'low'=>'green','medium'=>'amber','high'=>'red',default=>'zinc' }">
                                {{ ucfirst($this->resident->fall_risk_level) }}
                            </flux:badge>
                        </div>
                        <div>
                            <p class="text-xs text-zinc-400">DNR</p>
                            <flux:badge size="sm" :color="$this->resident->dnr_status ? 'red' : 'green'">
                                {{ $this->resident->dnr_status ? 'Yes' : 'No' }}
                            </flux:badge>
                        </div>
                    </div>
                    @if($this->resident->allergies || $this->resident->medical_conditions || $this->resident->dietary_requirements)
                        <div class="space-y-2 border-t border-zinc-100 pt-3 text-sm dark:border-zinc-800">
                            @if($this->resident->allergies)
                                <div><p class="text-xs text-zinc-400">Allergies</p><p class="whitespace-pre-wrap text-zinc-700 dark:text-zinc-300">{{ $this->resident->allergies }}</p></div>
                            @endif
                            @if($this->resident->medical_conditions)
                                <div><p class="text-xs text-zinc-400">Medical Conditions</p><p class="whitespace-pre-wrap text-zinc-700 dark:text-zinc-300">{{ $this->resident->medical_conditions }}</p></div>
                            @endif
                            @if($this->resident->dietary_requirements)
                                <div><p class="text-xs text-zinc-400">Dietary Requirements</p><p class="whitespace-pre-wrap text-zinc-700 dark:text-zinc-300">{{ $this->resident->dietary_requirements }}</p></div>
                            @endif
                        </div>
                    @endif
                </flux:card>

                {{-- Contacts --}}
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:card class="space-y-3 p-5">
                        <div class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-zinc-400">
                            <flux:icon name="phone" class="size-4" />
                            Emergency Contact
                        </div>
                        @if($this->resident->emergency_contact_name)
                            <div class="space-y-1.5 text-sm">
                                <p class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $this->resident->emergency_contact_name }}</p>
                                @if($this->resident->emergency_contact_relationship)
                                    <p class="text-zinc-500">{{ $this->resident->emergency_contact_relationship }}</p>
                                @endif
                                @if($this->resident->emergency_contact_phone)
                                    <p class="font-medium text-zinc-700 dark:text-zinc-300">{{ $this->resident->emergency_contact_phone }}</p>
                                @endif
                            </div>
                        @else
                            <p class="text-sm text-zinc-400">No emergency contact provided.</p>
                        @endif
                    </flux:card>

                    <flux:card class="space-y-3 p-5">
                        <div class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-zinc-400">
                            <flux:icon name="user-group" class="size-4" />
                            Next of Kin
                        </div>
                        @if($this->resident->nok_name)
                            <div class="space-y-1.5 text-sm">
                                <p class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $this->resident->nok_name }}</p>
                                @if($this->resident->nok_relationship)
                                    <p class="text-zinc-500">{{ $this->resident->nok_relationship }}</p>
                                @endif
                                @if($this->resident->nok_phone)
                                    <p class="font-medium text-zinc-700 dark:text-zinc-300">{{ $this->resident->nok_phone }}</p>
                                @endif
                                @if($this->resident->nok_email)
                                    <p class="text-zinc-500">{{ $this->resident->nok_email }}</p>
                                @endif
                            </div>
                        @else
                            <p class="text-sm text-zinc-400">No next of kin provided.</p>
                        @endif
                    </flux:card>
                </div>

                @if($this->resident->notes)
                    <flux:card class="space-y-3 p-5">
                        <div class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-zinc-400">
                            <flux:icon name="document-text" class="size-4" />
                            Notes
                        </div>
                        <p class="whitespace-pre-wrap text-sm text-zinc-700 dark:text-zinc-300">{{ $this->resident->notes }}</p>
                    </flux:card>
                @endif

                <p class="text-xs text-zinc-400">
                    Created {{ $this->resident->created_at->format('M d, Y H:i') }}
                    @if($this->resident->creator) by {{ $this->resident->creator->name }} @endif
                    @if($this->resident->updater) &middot; Last updated by {{ $this->resident->updater->name }} {{ $this->resident->updated_at->format('M d, Y H:i') }} @endif
                </p>
            </div>

        </div>

        </div>
        @endif

        {{-- Care Plans Tab --}}
        @if($activeTab === 'care-plans')
            <flux:card class="space-y-4">
                <div class="flex items-center justify-between">
                    <flux:heading size="sm">{{ __('Care Plans') }}</flux:heading>
                    @if($this->resident->isActive())
                        @can('manage-care-plans')
                            <flux:button variant="primary" size="sm" :href="route('care-plans.create', $this->resident)" wire:navigate icon="plus">
                                {{ __('Add Care Plan') }}
                            </flux:button>
                        @endcan
                    @endif
                </div>
                <flux:separator />

                @if($this->resident->carePlans->count() > 0)
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('Title') }}</flux:table.column>
                            <flux:table.column>{{ __('Type') }}</flux:table.column>
                            <flux:table.column>{{ __('Status') }}</flux:table.column>
                            <flux:table.column>{{ __('Start Date') }}</flux:table.column>
                            <flux:table.column>{{ __('Review Date') }}</flux:table.column>
                            <flux:table.column class="w-16"></flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach($this->resident->carePlans as $plan)
                                <flux:table.row :key="$plan->id">
                                    <flux:table.cell>
                                        <flux:link :href="route('care-plans.show', $plan)" wire:navigate class="font-medium">
                                            {{ $plan->title }}
                                        </flux:link>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:badge size="sm" color="zinc">{{ $plan->type_label }}</flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:badge size="sm" :color="$plan->status_color">
                                            {{ str_replace('_', ' ', ucfirst($plan->status)) }}
                                        </flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell>{{ $plan->start_date->format('M d, Y') }}</flux:table.cell>
                                    <flux:table.cell>{{ $plan->review_date?->format('M d, Y') ?? '-' }}</flux:table.cell>
                                    <flux:table.cell>
                                        <flux:button variant="ghost" size="sm" :href="route('care-plans.show', $plan)" wire:navigate icon="eye" />
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                @else
                    <x-dashboard.empty-state
                        title="No care plans"
                        description="No care plans have been created for this resident yet."
                        icon="clipboard-document-list"
                    />
                @endif
            </flux:card>
        @endif

        {{-- Medications Tab --}}
        @if($activeTab === 'medications')
            @can('manage-medications')
                <flux:card class="space-y-4">
                    <div class="flex items-center justify-between">
                        <flux:heading size="sm">{{ __('Active Medications') }}</flux:heading>
                        <div class="flex items-center gap-2">
                            <flux:button variant="primary" size="sm" :href="route('residents.mar', $this->resident)" wire:navigate icon="table-cells">
                                {{ __('Open MAR') }}
                            </flux:button>
                            @if($this->resident->isActive())
                                <flux:button variant="outline" size="sm" :href="route('medications.create', ['resident_id' => $this->residentId])" wire:navigate icon="plus">
                                    {{ __('Add Medication') }}
                                </flux:button>
                            @endif
                        </div>
                    </div>
                    <flux:separator />

                    @if($this->activeMedications->count() > 0)
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>{{ __('Medication') }}</flux:table.column>
                                <flux:table.column>{{ __('Dosage') }}</flux:table.column>
                                <flux:table.column>{{ __('Frequency') }}</flux:table.column>
                                <flux:table.column>{{ __('Route') }}</flux:table.column>
                                <flux:table.column class="w-16"></flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @foreach($this->activeMedications as $medication)
                                    <flux:table.row :key="$medication->id">
                                        <flux:table.cell>
                                            <flux:link :href="route('medications.show', $medication)" wire:navigate class="font-medium">
                                                {{ $medication->name }}
                                            </flux:link>
                                        </flux:table.cell>
                                        <flux:table.cell>{{ $medication->dosage }}</flux:table.cell>
                                        <flux:table.cell>{{ $medication->frequency }}</flux:table.cell>
                                        <flux:table.cell>
                                            <flux:badge size="sm" color="zinc">{{ $medication->route_label }}</flux:badge>
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            <flux:button variant="ghost" size="sm" :href="route('medications.show', $medication)" wire:navigate icon="eye" />
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    @else
                        <x-dashboard.empty-state
                            title="No active medications"
                            description="No medications are currently prescribed for this resident."
                            icon="beaker"
                        />
                    @endif
                </flux:card>
            @endcan
        @endif

        {{-- Vitals Tab --}}
        @if($activeTab === 'vitals')
            @can('manage-medications')
                <flux:card class="space-y-4">
                    <div class="flex items-center justify-between">
                        <flux:heading size="sm">{{ __('Recent Vitals') }}</flux:heading>
                        @if($this->resident->isActive())
                            <flux:button variant="primary" size="sm" :href="route('vitals.create', ['resident_id' => $this->residentId])" wire:navigate icon="plus">
                                {{ __('Record Vitals') }}
                            </flux:button>
                        @endif
                    </div>
                    <flux:separator />

                    @if($this->recentVitals->count() > 0)
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>{{ __('Recorded At') }}</flux:table.column>
                                <flux:table.column>{{ __('BP') }}</flux:table.column>
                                <flux:table.column>{{ __('HR') }}</flux:table.column>
                                <flux:table.column>{{ __('Temp') }}</flux:table.column>
                                <flux:table.column>{{ __('SpO2') }}</flux:table.column>
                                <flux:table.column class="w-16"></flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @foreach($this->recentVitals as $vital)
                                    <flux:table.row :key="$vital->id">
                                        <flux:table.cell>{{ $vital->recorded_at->format('M d, H:i') }}</flux:table.cell>
                                        <flux:table.cell>
                                            @if($vital->blood_pressure)
                                                <span @class(['text-red-500 font-medium' => $vital->blood_pressure_systolic > 140 || $vital->blood_pressure_systolic < 90])>
                                                    {{ $vital->blood_pressure }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </flux:table.cell>
                                        <flux:table.cell>{{ $vital->heart_rate ?? '-' }}</flux:table.cell>
                                        <flux:table.cell>{{ $vital->temperature ? $vital->temperature . '°C' : '-' }}</flux:table.cell>
                                        <flux:table.cell>{{ $vital->oxygen_saturation ? $vital->oxygen_saturation . '%' : '-' }}</flux:table.cell>
                                        <flux:table.cell>
                                            <flux:button variant="ghost" size="sm" :href="route('vitals.show', $vital)" wire:navigate icon="eye" />
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    @else
                        <x-dashboard.empty-state
                            title="No vitals recorded"
                            description="No vital signs have been recorded for this resident yet."
                            icon="heart"
                        />
                    @endif
                </flux:card>
            @endcan
        @endif

        {{-- Incidents Tab --}}
        @if($activeTab === 'incidents')
            @can('manage-incidents')
                <flux:card class="space-y-4">
                    <div class="flex items-center justify-between">
                        <flux:heading size="sm">{{ __('Incidents') }}</flux:heading>
                        @if($this->resident->isActive())
                            @can('report-incidents')
                                <flux:button variant="primary" size="sm" :href="route('incidents.create', ['resident_id' => $this->residentId])" wire:navigate icon="plus">
                                    {{ __('Report Incident') }}
                                </flux:button>
                            @endcan
                        @endif
                    </div>
                    <flux:separator />

                    @if($this->incidents->count() > 0)
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>{{ __('Title') }}</flux:table.column>
                                <flux:table.column>{{ __('Type') }}</flux:table.column>
                                <flux:table.column>{{ __('Severity') }}</flux:table.column>
                                <flux:table.column>{{ __('Status') }}</flux:table.column>
                                <flux:table.column>{{ __('Date') }}</flux:table.column>
                                <flux:table.column class="w-16"></flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @foreach($this->incidents as $incident)
                                    <flux:table.row :key="$incident->id">
                                        <flux:table.cell>
                                            <flux:link :href="route('incidents.show', $incident)" wire:navigate class="font-medium">
                                                {{ $incident->title }}
                                            </flux:link>
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            <flux:badge size="sm" color="zinc">{{ $incident->type_label }}</flux:badge>
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            <flux:badge size="sm" :color="$incident->severity_color">{{ ucfirst($incident->severity) }}</flux:badge>
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            <flux:badge size="sm" :color="$incident->status_color">
                                                {{ str_replace('_', ' ', ucfirst($incident->status)) }}
                                            </flux:badge>
                                        </flux:table.cell>
                                        <flux:table.cell>{{ $incident->occurred_at->format('M d, Y') }}</flux:table.cell>
                                        <flux:table.cell>
                                            <flux:button variant="ghost" size="sm" :href="route('incidents.show', $incident)" wire:navigate icon="eye" />
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    @else
                        <x-dashboard.empty-state
                            title="No incidents"
                            description="No incidents have been reported for this resident."
                            icon="exclamation-triangle"
                        />
                    @endif
                </flux:card>
            @endcan
        @endif
    </div>
</flux:main>
