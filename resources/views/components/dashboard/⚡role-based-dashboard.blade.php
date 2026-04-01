<?php

use App\Models\AuditLog;
use App\Models\CarePlan;
use App\Models\Incident;
use App\Models\Medication;
use App\Models\MedicationLog;
use App\Models\Resident;
use App\Models\Shift;
use App\Models\TherapistAssignment;
use App\Models\TherapySession;
use App\Models\User;
use App\Models\Vital;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    #[Computed]
    public function user(): User
    {
        return Auth::user();
    }

    #[Computed]
    public function roles(): array
    {
        return $this->user->roles->pluck('name')->toArray();
    }

    #[Computed]
    public function isAdmin(): bool { return in_array('system_admin', $this->roles); }

    #[Computed]
    public function isManager(): bool { return in_array('care_home_manager', $this->roles); }

    #[Computed]
    public function isNurse(): bool { return in_array('nurse', $this->roles); }

    #[Computed]
    public function isCaregiver(): bool { return in_array('caregiver', $this->roles); }

    #[Computed]
    public function isTherapist(): bool { return in_array('therapist', $this->roles); }

    #[Computed]
    public function hasNoRole(): bool { return empty($this->roles); }

    #[Computed]
    public function greeting(): string
    {
        $hour = now()->hour;
        if ($hour < 12) return 'Good morning';
        if ($hour < 17) return 'Good afternoon';
        return 'Good evening';
    }

    // ── Routing helpers ──

    #[Computed]
    public function currentStats(): array
    {
        if ($this->isAdmin)     return $this->adminStats;
        if ($this->isManager)   return $this->managerStats;
        if ($this->isNurse)     return $this->nurseStats;
        if ($this->isCaregiver) return $this->caregiverStats;
        if ($this->isTherapist) return $this->therapistStats;
        return [];
    }

    #[Computed]
    public function currentActions(): array
    {
        if ($this->isAdmin)     return $this->adminActions;
        if ($this->isManager)   return $this->managerActions;
        if ($this->isNurse)     return $this->nurseActions;
        if ($this->isCaregiver) return $this->caregiverActions;
        if ($this->isTherapist) return $this->therapistActions;
        return [];
    }

    // ── New: shared chart / calendar data ──

    #[Computed]
    public function weeklyActivity(): array
    {
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date  = now()->subDays($i);
            $count = 0;
            try {
                if ($this->isTherapist) {
                    $count = TherapySession::forTherapist($this->user->id)->whereDate('session_date', $date)->count();
                } elseif ($this->isNurse) {
                    $count = MedicationLog::whereDate('administered_at', $date)->count();
                } else {
                    $count = AuditLog::whereDate('created_at', $date)->count();
                }
            } catch (\Throwable) {}
            $days[] = ['label' => $date->format('D'), 'count' => $count, 'isToday' => $i === 0];
        }
        $max = max(array_column($days, 'count')) ?: 1;
        foreach ($days as &$day) {
            $day['percentage'] = max(6, (int) round(($day['count'] / $max) * 88));
        }
        return $days;
    }

    #[Computed]
    public function overviewData(): array
    {
        try {
            $active     = Resident::active()->count();
            $total      = Resident::withTrashed()->count();
            $discharged = max(0, $total - $active);
            $pct        = $total > 0 ? (int) round(($active / $total) * 100) : 0;
            // r=50 → circumference = 2π×50 ≈ 314.16
            // r=35 → circumference = 2π×35 ≈ 219.91
            return [
                'active'     => $active,
                'discharged' => $discharged,
                'total'      => $total,
                'percentage' => $pct,
                'outer_dash' => (int) round($pct * 3.1416),       // out of 314.16
                'inner_dash' => $total > 0 ? (int) round(($discharged / $total) * 219.9) : 0,
            ];
        } catch (\Throwable) {
            return ['active'=>0,'discharged'=>0,'total'=>0,'percentage'=>0,'outer_dash'=>0,'inner_dash'=>0];
        }
    }

    #[Computed]
    public function upcomingEvents(): array
    {
        $events = [];
        try {
            TherapySession::where('session_date', '>=', today())
                ->where('session_date', '<=', now()->addDays(30))
                ->with('resident')
                ->orderBy('session_date')
                ->limit(8)
                ->get()
                ->each(function ($s) use (&$events) {
                    $events[] = [
                        'title' => $s->resident?->full_name ?? 'Session',
                        'day'   => (int) $s->session_date->format('j'),
                        'month' => (int) $s->session_date->format('n'),
                        'year'  => (int) $s->session_date->format('Y'),
                        'label' => $s->session_date->format('M d'),
                        'color' => 'violet',
                    ];
                });
        } catch (\Throwable) {}
        try {
            Resident::where('admission_date', '>=', today())
                ->orderBy('admission_date')
                ->limit(5)
                ->get()
                ->each(function ($r) use (&$events) {
                    if ($r->admission_date) {
                        $events[] = [
                            'title' => $r->full_name,
                            'day'   => (int) $r->admission_date->format('j'),
                            'month' => (int) $r->admission_date->format('n'),
                            'year'  => (int) $r->admission_date->format('Y'),
                            'label' => $r->admission_date->format('M d'),
                            'color' => 'sky',
                        ];
                    }
                });
        } catch (\Throwable) {}
        usort($events, fn ($a, $b) =>
            mktime(0,0,0,$a['month'],$a['day'],$a['year']) - mktime(0,0,0,$b['month'],$b['day'],$b['year'])
        );
        return array_slice($events, 0, 8);
    }

    // ── Admin ──

    #[Computed]
    public function adminStats(): array
    {
        $totalUsers       = User::count();
        $newUsersThisMonth= User::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $openIncidents    = Incident::open()->count();
        $criticalIncidents= Incident::open()->where('severity', 'critical')->count();
        $actionsToday     = AuditLog::whereDate('created_at', today())->count();
        return [
            ['title'=>'Total Users',      'value'=>$totalUsers,             'icon'=>'users',              'trend'=>$newUsersThisMonth>0?"+{$newUsersThisMonth} this month":null,'trendUp'=>true],
            ['title'=>'Active Residents', 'value'=>Resident::active()->count(),'icon'=>'user-group'],
            ['title'=>'Open Incidents',   'value'=>$openIncidents,           'icon'=>'exclamation-triangle','description'=>$criticalIncidents>0?"{$criticalIncidents} critical":'No critical incidents'],
            ['title'=>'System Activity',  'value'=>$actionsToday,            'icon'=>'document-text',      'description'=>'Actions today'],
        ];
    }

    #[Computed]
    public function adminActions(): array
    {
        return [
            ['label'=>'Manage Users', 'href'=>route('admin.users.index'),    'icon'=>'users'],
            ['label'=>'Audit Logs',   'href'=>route('admin.logs.index'),     'icon'=>'document-text'],
            ['label'=>'Settings',     'href'=>route('admin.settings.general'),'icon'=>'cog-6-tooth'],
        ];
    }

    #[Computed]
    public function adminRecentActivity(): \Illuminate\Database\Eloquent\Collection
    {
        return AuditLog::with('user')->latest()->take(5)->get();
    }

    #[Computed]
    public function recentAdmissions(): \Illuminate\Database\Eloquent\Collection
    {
        return Resident::where('admission_date', '>=', now()->subDays(30))
            ->orderByDesc('admission_date')->take(5)->get();
    }

    #[Computed]
    public function admissionStats(): array
    {
        $thisMonth = Resident::whereMonth('admission_date', now()->month)->whereYear('admission_date', now()->year)->count();
        $lastMonth = Resident::whereMonth('admission_date', now()->subMonth()->month)->whereYear('admission_date', now()->subMonth()->year)->count();
        $thisWeek  = Resident::where('admission_date', '>=', now()->startOfWeek())->where('admission_date', '<=', now()->endOfWeek())->count();
        $pending   = Resident::active()->whereNotNull('discharge_date')->where('discharge_date', '>=', today())->count();
        return ['this_month'=>$thisMonth,'last_month'=>$lastMonth,'this_week'=>$thisWeek,'pending_discharge'=>$pending,'trend'=>$thisMonth-$lastMonth];
    }

    // ── Manager ──

    #[Computed]
    public function managerStats(): array
    {
        $activeResidents = Resident::active()->count();
        $newThisMonth    = Resident::active()->whereMonth('admission_date', now()->month)->whereYear('admission_date', now()->year)->count();
        $staffOnDuty     = Shift::today()->whereIn('status', ['scheduled','in_progress'])->distinct('user_id')->count('user_id');
        $activeCarePlans = CarePlan::active()->count();
        $reviewDue       = CarePlan::active()->whereNotNull('review_date')->where('review_date', '<=', now()->addDays(7))->count();
        $openIncidents   = Incident::open()->count();
        $severeIncidents = Incident::open()->whereIn('severity', ['critical','major'])->count();
        return [
            ['title'=>'Active Residents','value'=>$activeResidents,'icon'=>'user-group','trend'=>$newThisMonth>0?"+{$newThisMonth} this month":null,'trendUp'=>true],
            ['title'=>'Staff on Duty',   'value'=>$staffOnDuty,   'icon'=>'identification','description'=>$staffOnDuty>0?'On shift today':'No staff scheduled'],
            ['title'=>'Care Plans',      'value'=>$activeCarePlans,'icon'=>'clipboard-document-list','description'=>$reviewDue>0?"{$reviewDue} due for review":'All up to date'],
            ['title'=>'Open Incidents',  'value'=>$openIncidents, 'icon'=>'exclamation-triangle','description'=>$severeIncidents>0?"{$severeIncidents} critical/major":'No severe incidents'],
        ];
    }

    #[Computed]
    public function managerActions(): array
    {
        return [
            ['label'=>'Add Resident', 'href'=>route('residents.create'),'icon'=>'user-plus'],
            ['label'=>'Create Shift', 'href'=>route('shifts.create'),   'icon'=>'calendar'],
            ['label'=>'View Reports', 'href'=>route('reports.index'),   'icon'=>'chart-bar'],
        ];
    }

    #[Computed]
    public function managerCarePlansDueReview(): \Illuminate\Database\Eloquent\Collection
    {
        return CarePlan::active()->whereNotNull('review_date')->where('review_date', '<=', now()->addDays(7))
            ->with('resident')->orderBy('review_date')->take(5)->get();
    }

    #[Computed]
    public function managerRecentIncidents(): \Illuminate\Database\Eloquent\Collection
    {
        return Incident::with('resident')->latest('occurred_at')->take(5)->get();
    }

    #[Computed]
    public function managerMedicationOverview(): array
    {
        $today = MedicationLog::whereDate('administered_at', today());
        return [
            'given'    => (clone $today)->where('status','given')->count(),
            'refused'  => (clone $today)->where('status','refused')->count(),
            'missed'   => (clone $today)->where('status','missed')->count(),
            'withheld' => (clone $today)->where('status','withheld')->count(),
        ];
    }

    // ── Nurse ──

    #[Computed]
    public function nurseStats(): array
    {
        $activeResidents = Resident::active()->count();
        $medsToday       = MedicationLog::whereDate('administered_at', today())->count();
        $medsGiven       = MedicationLog::whereDate('administered_at', today())->where('status','given')->count();
        $compliance      = $medsToday > 0 ? round(($medsGiven / $medsToday) * 100) : 0;
        $abnormalVitals  = Vital::where('recorded_at', '>=', now()->subDay())->with('resident')->get()->filter(fn($v) => $v->hasAbnormalValues())->count();
        $openIncidents   = Incident::open()->count();
        return [
            ['title'=>'Active Residents', 'value'=>$activeResidents,'icon'=>'heart',               'description'=>'Under care'],
            ['title'=>'Medications Today','value'=>$medsToday,      'icon'=>'beaker',              'description'=>$medsToday>0?"{$compliance}% compliance":'No administrations yet'],
            ['title'=>'Abnormal Vitals',  'value'=>$abnormalVitals, 'icon'=>'exclamation-circle',  'description'=>'In last 24 hours'],
            ['title'=>'Open Incidents',   'value'=>$openIncidents,  'icon'=>'exclamation-triangle','description'=>$openIncidents>0?'Require attention':'No open incidents'],
        ];
    }

    #[Computed]
    public function nurseActions(): array
    {
        return [
            ['label'=>'Record Vitals',    'href'=>route('vitals.create'),      'icon'=>'heart'],
            ['label'=>'Medication Round', 'href'=>route('medications.index'),  'icon'=>'beaker'],
            ['label'=>'Report Incident',  'href'=>route('incidents.create'),   'icon'=>'exclamation-circle'],
        ];
    }

    #[Computed]
    public function nurseAbnormalVitals(): \Illuminate\Support\Collection
    {
        return Vital::where('recorded_at', '>=', now()->subDay())->with('resident')->latest('recorded_at')
            ->get()->filter(fn($v) => $v->hasAbnormalValues())->take(5)->values();
    }

    #[Computed]
    public function nurseRecentMedications(): \Illuminate\Database\Eloquent\Collection
    {
        return MedicationLog::with(['medication','resident'])->latest('administered_at')->take(5)->get();
    }

    // ── Caregiver ──

    #[Computed]
    public function caregiverStats(): array
    {
        $activeResidents = Resident::active()->count();
        $todayShift      = Shift::where('user_id', $this->user->id)->today()->first();
        $shiftValue      = '-';
        $shiftDesc       = 'No shift scheduled';
        if ($todayShift) {
            $shiftValue = $todayShift->type_label;
            $shiftDesc  = \Carbon\Carbon::parse($todayShift->start_time)->format('H:i').' - '.\Carbon\Carbon::parse($todayShift->end_time)->format('H:i');
        }
        $openIncidents = Incident::open()->count();
        return [
            ['title'=>'Active Residents','value'=>$activeResidents,'icon'=>'users',               'description'=>'Under care'],
            ['title'=>'My Shift',        'value'=>$shiftValue,     'icon'=>'clock',               'description'=>$shiftDesc],
            ['title'=>'Open Incidents',  'value'=>$openIncidents,  'icon'=>'exclamation-triangle','description'=>$openIncidents>0?'Be aware':'All clear'],
        ];
    }

    #[Computed]
    public function caregiverActions(): array
    {
        return [
            ['label'=>'View Residents',  'href'=>route('residents.index'),   'icon'=>'users'],
            ['label'=>'Report Incident', 'href'=>route('incidents.create'),  'icon'=>'exclamation-circle'],
            ['label'=>'View Care Plans', 'href'=>route('care-plans.index'),  'icon'=>'document-text'],
        ];
    }

    #[Computed]
    public function caregiverRecentCarePlans(): \Illuminate\Database\Eloquent\Collection
    {
        return CarePlan::active()->with('resident')->latest()->take(5)->get();
    }

    // ── Therapist ──

    #[Computed]
    public function therapistStats(): array
    {
        $userId          = $this->user->id;
        $myResidents     = TherapistAssignment::forTherapist($userId)->active()->count();
        $todaySessions   = TherapySession::forTherapist($userId)->today()->count();
        $completedThisWeek = TherapySession::forTherapist($userId)->completed()
            ->whereBetween('session_date', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $pendingDocs     = TherapySession::forTherapist($userId)->where('status','completed')->whereNull('progress_notes')->count();
        return [
            ['title'=>"Today's Sessions",     'value'=>$todaySessions,      'icon'=>'calendar',       'color'=>'accent'],
            ['title'=>'Assigned Residents',    'value'=>$myResidents,        'icon'=>'users',          'color'=>'sky'],
            ['title'=>'Completed This Week',   'value'=>$completedThisWeek, 'icon'=>'check-circle',   'color'=>'emerald'],
            ['title'=>'Pending Documentation', 'value'=>$pendingDocs,        'icon'=>'document-text',  'color'=>'amber'],
        ];
    }

    #[Computed]
    public function therapistActions(): array
    {
        return [
            ['label'=>'New Session',     'href'=>route('therapy.sessions.create'),  'icon'=>'plus'],
            ['label'=>'My Residents',    'href'=>route('therapy.my-residents'),      'icon'=>'users'],
            ['label'=>'Generate Report', 'href'=>route('therapy.reports.generate'), 'icon'=>'document-chart-bar'],
        ];
    }

    #[Computed]
    public function therapistTodaySessions(): \Illuminate\Database\Eloquent\Collection
    {
        return TherapySession::forTherapist($this->user->id)->today()->with('resident')->orderBy('start_time')->get();
    }

    #[Computed]
    public function therapistUpcomingSessions(): \Illuminate\Database\Eloquent\Collection
    {
        return TherapySession::forTherapist($this->user->id)->upcoming()
            ->where('session_date', '>', today())->with('resident')->take(5)->get();
    }

    #[Computed]
    public function therapistPendingDocumentation(): \Illuminate\Database\Eloquent\Collection
    {
        return TherapySession::forTherapist($this->user->id)->where('status','completed')
            ->whereNull('progress_notes')->with('resident')->orderBy('session_date','desc')->take(5)->get();
    }

    // ── Right panel ──

    #[Computed]
    public function recentResidents(): \Illuminate\Database\Eloquent\Collection
    {
        try {
            return Resident::active()->orderByDesc('admission_date')->limit(5)->get();
        } catch (\Throwable) {
            return collect();
        }
    }
}; ?>

<div class="flex h-full w-full flex-1 items-start gap-5">

    {{-- ══════════════════════════════════════════════════════
         MIDDLE COLUMN — Main content
    ══════════════════════════════════════════════════════ --}}
    <div class="flex min-w-0 flex-1 flex-col gap-5">

    @if($this->hasNoRole)
        {{-- No role state --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-10 text-center shadow-sm dark:border-white/[0.06] dark:bg-zinc-800/60">
            <div class="mx-auto flex size-14 items-center justify-center rounded-full bg-amber-50 dark:bg-amber-900/20">
                <flux:icon name="shield-exclamation" variant="outline" class="size-7 text-amber-500" />
            </div>
            <h3 class="mt-4 text-base font-semibold text-slate-800 dark:text-zinc-100">No role assigned</h3>
            <p class="mt-1.5 text-sm text-slate-500 dark:text-zinc-400">Your account doesn't have any roles yet. Contact a system administrator to get started.</p>
        </div>
    @else

    {{-- ═══════════════════════════════════════════════════
         ROW 1 — Welcome Banner
    ═══════════════════════════════════════════════════ --}}
        {{-- ── Welcome Card ── --}}
        <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-white/[0.06] dark:bg-zinc-800/60">
            {{-- Accent gradient wash --}}
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-[color-mix(in_oklch,var(--color-accent)_8%,transparent)] via-transparent to-transparent"></div>
            <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-accent/40 to-transparent"></div>

            <div class="relative flex items-center gap-4 p-6">
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 dark:text-zinc-500">
                        {{ now()->format('l, F j, Y') }}
                    </p>
                    <h1 class="mt-1.5 text-2xl font-bold leading-tight text-slate-900 dark:text-white sm:text-3xl">
                        Welcome Back,<br class="sm:hidden">
                        <span class="cn-gradient-text">{{ $this->user->name }}</span>!
                    </h1>
                    <p class="mt-1.5 text-sm text-slate-500 dark:text-zinc-400">
                        Here's what's happening at <strong class="font-medium text-slate-700 dark:text-zinc-200">{{ system_setting('facility_name', config('app.name')) }}</strong> today.
                    </p>
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @foreach($this->roles as $role)
                            <flux:badge color="blue" size="sm">{{ str_replace('_', ' ', ucwords($role, '_')) }}</flux:badge>
                        @endforeach
                    </div>
                </div>
                <div class="hidden shrink-0 sm:block">
                    <img src="{{ asset('illustration/doctors.png') }}" alt="Medical Staff"
                        class="h-32 w-auto object-contain drop-shadow-md lg:h-40" />
                </div>
            </div>
        </div>


    {{-- ═══════════════════════════════════════════════════
         ROW 2 — Stat Cards
    ═══════════════════════════════════════════════════ --}}
    @if(count($this->currentStats) > 0)
    <div class="grid gap-4 grid-cols-2 {{ count($this->currentStats) === 3 ? 'lg:grid-cols-3' : 'lg:grid-cols-4' }}">
        @foreach($this->currentStats as $stat)
            <x-dashboard.stat-card
                :title="$stat['title']"
                :value="$stat['value']"
                :icon="$stat['icon'] ?? null"
                :description="$stat['description'] ?? null"
                :trend="$stat['trend'] ?? null"
                :trendUp="$stat['trendUp'] ?? true"
                :color="$stat['color'] ?? 'accent'"
            />
        @endforeach
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════
         ROW 3 — Activity Bar Chart + Resident Overview
    ═══════════════════════════════════════════════════ --}}
    <div class="grid gap-4 lg:grid-cols-[1fr_250px]">

        {{-- Activity Bar Chart --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-white/[0.06] dark:bg-zinc-800/60">
            <div class="mb-5 flex items-start justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-zinc-100">Activity Statistics</h3>
                    <p class="text-xs text-slate-400 dark:text-zinc-500">
                        @if($this->isTherapist) Sessions · @elseif($this->isNurse) Medications · @else System actions · @endif
                        last 7 days
                    </p>
                </div>
                <flux:badge color="blue" size="sm">Weekly</flux:badge>
            </div>
            <div class="flex h-36 items-end gap-2 sm:gap-3">
                @foreach($this->weeklyActivity as $day)
                    <div class="flex flex-1 flex-col items-center gap-1.5">
                        <span class="text-[10px] font-medium tabular-nums text-slate-400 dark:text-zinc-500">{{ $day['count'] }}</span>
                        <div
                            class="w-full min-h-[6px] rounded-t-lg transition-all duration-700"
                            style="height:{{ $day['percentage'] }}%; background: {{ $day['isToday'] ? 'var(--color-accent)' : 'color-mix(in oklch, var(--color-accent) 35%, transparent)' }}"
                        ></div>
                        <span class="text-[10px] font-medium text-slate-400 dark:text-zinc-500">{{ $day['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Resident Overview (Donut) --}}
        @php $ov = $this->overviewData; @endphp
        <div class="flex flex-col rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-white/[0.06] dark:bg-zinc-800/60">
            <h3 class="mb-1 text-sm font-semibold text-slate-800 dark:text-zinc-100">Resident Overview</h3>
            <p class="mb-4 text-xs text-slate-400 dark:text-zinc-500">Status breakdown</p>

            <div class="flex flex-1 flex-col items-center justify-center">
                {{-- SVG donut --}}
                <div class="relative">
                    <svg width="130" height="130" viewBox="0 0 130 130" class="-rotate-90 overflow-visible">
                        {{-- outer track --}}
                        <circle cx="65" cy="65" r="50" fill="none" stroke="currentColor" stroke-width="13" class="text-slate-100 dark:text-zinc-700" />
                        {{-- outer progress --}}
                        <circle cx="65" cy="65" r="50" fill="none"
                            stroke="var(--color-accent)"
                            stroke-width="13"
                            stroke-linecap="round"
                            stroke-dasharray="{{ $ov['outer_dash'] }} 314"
                        />
                        {{-- inner track --}}
                        <circle cx="65" cy="65" r="35" fill="none" stroke="currentColor" stroke-width="10" class="text-slate-100 dark:text-zinc-700" />
                        {{-- inner progress --}}
                        <circle cx="65" cy="65" r="35" fill="none"
                            stroke="var(--theme-primary-300, #93c5fd)"
                            stroke-width="10"
                            stroke-linecap="round"
                            stroke-dasharray="{{ $ov['inner_dash'] }} 220"
                        />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                        <span class="text-2xl font-bold text-slate-900 dark:text-white">{{ $ov['percentage'] }}<span class="text-sm font-normal">%</span></span>
                        <span class="text-[10px] text-slate-400 dark:text-zinc-500">Active</span>
                    </div>
                </div>

                {{-- Legend --}}
                <div class="mt-4 w-full space-y-2.5">
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <span class="size-2.5 rounded-full bg-accent shrink-0"></span>
                            <span class="text-slate-600 dark:text-zinc-300">Active</span>
                        </div>
                        <span class="font-semibold text-slate-800 dark:text-zinc-200">{{ $ov['active'] }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <span class="size-2.5 shrink-0 rounded-full" style="background:var(--theme-primary-300,#93c5fd)"></span>
                            <span class="text-slate-600 dark:text-zinc-300">Discharged</span>
                        </div>
                        <span class="font-semibold text-slate-800 dark:text-zinc-200">{{ $ov['discharged'] }}</span>
                    </div>
                    <div class="mt-1 border-t border-slate-100 pt-2 dark:border-white/[0.06]">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-400 dark:text-zinc-500">Total</span>
                            <span class="font-bold text-slate-800 dark:text-zinc-100">{{ $ov['total'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- ═══════════════════════════════════════════════════
         ROW 5 — Role-specific detail sections
    ═══════════════════════════════════════════════════ --}}

    {{-- ── Admin Detail ── --}}
    @if($this->isAdmin)
    <div class="grid gap-4 md:grid-cols-2">
        {{-- Recent Activity --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-white/[0.06] dark:bg-zinc-800/60">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-800 dark:text-zinc-100">Recent Activity</h3>
                <div class="flex size-7 items-center justify-center rounded-lg bg-slate-100 dark:bg-white/[0.05]">
                    <flux:icon name="clock" variant="outline" class="size-4 text-slate-500 dark:text-zinc-400" />
                </div>
            </div>
            <div class="h-px bg-gradient-to-r from-slate-200 via-slate-100 to-transparent dark:from-white/[0.07] dark:via-white/[0.04] mb-4"></div>
            @if($this->adminRecentActivity->isEmpty())
                <p class="text-sm text-slate-400 dark:text-zinc-500">No recent activity.</p>
            @else
                <div class="divide-y divide-slate-100 dark:divide-zinc-800">
                    @foreach($this->adminRecentActivity as $log)
                        <div class="flex items-center justify-between py-2.5">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-slate-700 dark:text-zinc-200">{{ $log->description ?: $log->action_label }}</p>
                                <p class="text-xs text-slate-400 dark:text-zinc-500">{{ $log->user?->name ?? 'System' }} · {{ $log->created_at->diffForHumans() }}</p>
                            </div>
                            <flux:badge size="sm" :color="$log->action_color">{{ $log->action_label }}</flux:badge>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3 border-t border-slate-100 pt-3 dark:border-zinc-800">
                    <flux:button variant="ghost" size="sm" :href="route('admin.logs.index')" icon="arrow-right" icon-trailing wire:navigate>View all logs</flux:button>
                </div>
            @endif
        </div>

        {{-- Recent Admissions --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-white/[0.06] dark:bg-zinc-800/60">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-800 dark:text-zinc-100">Recent Admissions</h3>
                <div class="flex size-7 items-center justify-center rounded-lg bg-slate-100 dark:bg-white/[0.05]">
                    <flux:icon name="user-plus" variant="outline" class="size-4 text-slate-500 dark:text-zinc-400" />
                </div>
            </div>
            <div class="h-px bg-gradient-to-r from-slate-200 via-slate-100 to-transparent dark:from-white/[0.07] dark:via-white/[0.04] mb-4"></div>
            @if($this->recentAdmissions->isEmpty())
                <p class="text-sm text-slate-400 dark:text-zinc-500">No admissions in the last 30 days.</p>
            @else
                <div class="divide-y divide-slate-100 dark:divide-zinc-800">
                    @foreach($this->recentAdmissions as $resident)
                        <a href="{{ route('residents.show', $resident) }}" wire:navigate class="flex items-center justify-between py-2.5 -mx-1 px-1 rounded-lg transition hover:bg-slate-50 dark:hover:bg-zinc-800/50">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-slate-700 dark:text-zinc-200">{{ $resident->full_name }}</p>
                                <p class="text-xs text-slate-400 dark:text-zinc-500">Room {{ $resident->room_number ?? 'N/A' }} · {{ $resident->admission_date->diffForHumans() }}</p>
                            </div>
                            <flux:badge size="sm" color="green">New</flux:badge>
                        </a>
                    @endforeach
                </div>
                <div class="mt-3 border-t border-slate-100 pt-3 dark:border-zinc-800">
                    <flux:button variant="ghost" size="sm" :href="route('residents.index')" icon="arrow-right" icon-trailing wire:navigate>View all residents</flux:button>
                </div>
            @endif
        </div>
    </div>

    {{-- Admissions Overview --}}
    @php $admStats = $this->admissionStats; @endphp
    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-white/[0.06] dark:bg-zinc-800/60">
        <h3 class="mb-4 text-sm font-semibold text-slate-800 dark:text-zinc-100">Admissions Overview</h3>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            @php
            $admBoxes = [
                ['val'=>$admStats['this_month'],     'label'=>'This Month',       'cls'=>'theme-accent-text'],
                ['val'=>$admStats['last_month'],     'label'=>'Last Month',       'cls'=>'text-slate-600 dark:text-zinc-400'],
                ['val'=>$admStats['this_week'],      'label'=>'This Week',        'cls'=>'text-green-600 dark:text-green-400'],
                ['val'=>$admStats['pending_discharge'],'label'=>'Pending Discharge','cls'=>'text-amber-600 dark:text-amber-400'],
            ];
            @endphp
            @foreach($admBoxes as $box)
                <div class="rounded-xl bg-slate-50 p-3 text-center dark:bg-zinc-800/50">
                    <div class="text-2xl font-bold {{ $box['cls'] }}">{{ $box['val'] }}</div>
                    <p class="mt-0.5 text-xs text-slate-400 dark:text-zinc-500">{{ $box['label'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Manager Detail ── --}}
    @if($this->isManager)
    <div class="grid gap-4 md:grid-cols-2">
        {{-- Medication Overview --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-white/[0.06] dark:bg-zinc-800/60">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-800 dark:text-zinc-100">Medication Overview</h3>
                <div class="flex size-7 items-center justify-center rounded-lg bg-slate-100 dark:bg-white/[0.05]">
                    <flux:icon name="beaker" variant="outline" class="size-4 text-slate-500 dark:text-zinc-400" />
                </div>
            </div>
            <div class="h-px bg-gradient-to-r from-slate-200 via-slate-100 to-transparent dark:from-white/[0.07] dark:via-white/[0.04] mb-4"></div>
            @php $medOv = $this->managerMedicationOverview; $medTotal = array_sum($medOv); @endphp
            @if($medTotal === 0)
                <p class="text-sm text-slate-400 dark:text-zinc-500">No medications administered today.</p>
            @else
                <div class="grid grid-cols-4 gap-3 text-center">
                    <div><div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $medOv['given'] }}</div><p class="text-xs text-slate-400 dark:text-zinc-500">Given</p></div>
                    <div><div class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $medOv['refused'] }}</div><p class="text-xs text-slate-400 dark:text-zinc-500">Refused</p></div>
                    <div><div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $medOv['missed'] }}</div><p class="text-xs text-slate-400 dark:text-zinc-500">Missed</p></div>
                    <div><div class="text-2xl font-bold theme-accent-text">{{ $medOv['withheld'] }}</div><p class="text-xs text-slate-400 dark:text-zinc-500">Withheld</p></div>
                </div>
            @endif
        </div>

        {{-- Care Plans Due --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-white/[0.06] dark:bg-zinc-800/60">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-800 dark:text-zinc-100">Care Plans Due for Review</h3>
                <div class="flex size-7 items-center justify-center rounded-lg bg-slate-100 dark:bg-white/[0.05]">
                    <flux:icon name="clipboard-document-list" variant="outline" class="size-4 text-slate-500 dark:text-zinc-400" />
                </div>
            </div>
            <div class="h-px bg-gradient-to-r from-slate-200 via-slate-100 to-transparent dark:from-white/[0.07] dark:via-white/[0.04] mb-4"></div>
            @if($this->managerCarePlansDueReview->isEmpty())
                <p class="text-sm text-slate-400 dark:text-zinc-500">No care plans due for review.</p>
            @else
                <div class="divide-y divide-slate-100 dark:divide-zinc-800">
                    @foreach($this->managerCarePlansDueReview as $plan)
                        <a href="{{ route('care-plans.show', $plan) }}" wire:navigate class="flex items-center justify-between py-2.5 -mx-1 px-1 rounded-lg transition hover:bg-slate-50 dark:hover:bg-zinc-800/50">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-slate-700 dark:text-zinc-200">{{ $plan->title }}</p>
                                <p class="text-xs text-slate-400 dark:text-zinc-500">{{ $plan->resident?->full_name }} · Review {{ $plan->review_date->diffForHumans() }}</p>
                            </div>
                            <flux:badge size="sm" :color="$plan->review_date->isPast() ? 'red' : 'amber'">
                                {{ $plan->review_date->isPast() ? 'Overdue' : 'Due soon' }}
                            </flux:badge>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Recent Incidents --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-white/[0.06] dark:bg-zinc-800/60">
        <div class="mb-3 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-zinc-100">Recent Incidents</h3>
            <flux:button variant="ghost" size="sm" :href="route('incidents.index')" wire:navigate icon="arrow-right" icon-trailing>View all</flux:button>
        </div>
        <div class="h-px bg-gradient-to-r from-slate-200 via-slate-100 to-transparent dark:from-white/[0.07] dark:via-white/[0.04] mb-4"></div>
        @if($this->managerRecentIncidents->isEmpty())
            <p class="text-sm text-slate-400 dark:text-zinc-500">No incidents recorded.</p>
        @else
            <div class="divide-y divide-slate-100 dark:divide-zinc-800">
                @foreach($this->managerRecentIncidents as $incident)
                    <a href="{{ route('incidents.show', $incident) }}" wire:navigate class="flex items-center justify-between py-2.5 -mx-1 px-1 rounded-lg transition hover:bg-slate-50 dark:hover:bg-zinc-800/50">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-700 dark:text-zinc-200">{{ $incident->title }}</p>
                            <p class="text-xs text-slate-400 dark:text-zinc-500">{{ $incident->resident?->full_name }} · {{ $incident->occurred_at->diffForHumans() }}</p>
                        </div>
                        <flux:badge size="sm" :color="$incident->severity_color">{{ ucfirst($incident->severity) }}</flux:badge>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
    @endif

    {{-- ── Nurse Detail ── --}}
    @if($this->isNurse)
    <div class="grid gap-4 md:grid-cols-2">
        {{-- Vitals Alerts --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-white/[0.06] dark:bg-zinc-800/60">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-800 dark:text-zinc-100">Vitals Alerts (24h)</h3>
                <div class="flex size-7 items-center justify-center rounded-lg bg-slate-100 dark:bg-white/[0.05]">
                    <flux:icon name="exclamation-circle" variant="outline" class="size-4 text-slate-500 dark:text-zinc-400" />
                </div>
            </div>
            <div class="h-px bg-gradient-to-r from-slate-200 via-slate-100 to-transparent dark:from-white/[0.07] dark:via-white/[0.04] mb-4"></div>
            @if($this->nurseAbnormalVitals->isEmpty())
                <p class="text-sm text-slate-400 dark:text-zinc-500">No abnormal vitals in the last 24 hours.</p>
            @else
                <div class="divide-y divide-slate-100 dark:divide-zinc-800">
                    @foreach($this->nurseAbnormalVitals as $vital)
                        <a href="{{ route('vitals.show', $vital) }}" wire:navigate class="flex items-center justify-between py-2.5 -mx-1 px-1 rounded-lg transition hover:bg-slate-50 dark:hover:bg-zinc-800/50">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-slate-700 dark:text-zinc-200">{{ $vital->resident?->full_name }}</p>
                                <p class="text-xs text-slate-400 dark:text-zinc-500">{{ $vital->recorded_at->diffForHumans() }}</p>
                            </div>
                            <flux:badge size="sm" color="red">Abnormal</flux:badge>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Recent Medications --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-white/[0.06] dark:bg-zinc-800/60">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-800 dark:text-zinc-100">Recent Medications</h3>
                <div class="flex size-7 items-center justify-center rounded-lg bg-slate-100 dark:bg-white/[0.05]">
                    <flux:icon name="beaker" variant="outline" class="size-4 text-slate-500 dark:text-zinc-400" />
                </div>
            </div>
            <div class="h-px bg-gradient-to-r from-slate-200 via-slate-100 to-transparent dark:from-white/[0.07] dark:via-white/[0.04] mb-4"></div>
            @if($this->nurseRecentMedications->isEmpty())
                <p class="text-sm text-slate-400 dark:text-zinc-500">No recent medication administrations.</p>
            @else
                <div class="divide-y divide-slate-100 dark:divide-zinc-800">
                    @foreach($this->nurseRecentMedications as $log)
                        <div class="flex items-center justify-between py-2.5">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-slate-700 dark:text-zinc-200">{{ $log->medication?->name ?? 'Unknown' }} — {{ $log->medication?->dosage ?? '' }}</p>
                                <p class="text-xs text-slate-400 dark:text-zinc-500">{{ $log->resident?->full_name }} · {{ $log->administered_at->diffForHumans() }}</p>
                            </div>
                            <flux:badge size="sm" :color="$log->status_color">{{ ucfirst($log->status) }}</flux:badge>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ── Caregiver Detail ── --}}
    @if($this->isCaregiver)
    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-white/[0.06] dark:bg-zinc-800/60">
        <div class="mb-3 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-zinc-100">Active Care Plans</h3>
            <div class="flex size-7 items-center justify-center rounded-lg bg-slate-100 dark:bg-white/[0.05]">
                <flux:icon name="clipboard-document-list" variant="outline" class="size-4 text-slate-500 dark:text-zinc-400" />
            </div>
        </div>
        <div class="h-px bg-gradient-to-r from-slate-200 via-slate-100 to-transparent dark:from-white/[0.07] dark:via-white/[0.04] mb-4"></div>
        @if($this->caregiverRecentCarePlans->isEmpty())
            <p class="text-sm text-slate-400 dark:text-zinc-500">No active care plans.</p>
        @else
            <div class="divide-y divide-slate-100 dark:divide-zinc-800">
                @foreach($this->caregiverRecentCarePlans as $plan)
                    <a href="{{ route('care-plans.show', $plan) }}" wire:navigate class="flex items-center justify-between py-2.5 -mx-1 px-1 rounded-lg transition hover:bg-slate-50 dark:hover:bg-zinc-800/50">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-700 dark:text-zinc-200">{{ $plan->title }}</p>
                            <p class="text-xs text-slate-400 dark:text-zinc-500">{{ $plan->resident?->full_name }}</p>
                        </div>
                        <flux:badge size="sm" :color="$plan->status_color">{{ $plan->type_label }}</flux:badge>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
    @endif

    {{-- ── Therapist Detail ── --}}
    @if($this->isTherapist)
    <div class="grid gap-4 lg:grid-cols-2">
        {{-- Today's Sessions --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-white/[0.06] dark:bg-zinc-800/60">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-800 dark:text-zinc-100">Today's Sessions</h3>
                <flux:button variant="ghost" size="sm" :href="route('therapy.sessions.create')" wire:navigate icon="plus">New</flux:button>
            </div>
            <div class="h-px bg-gradient-to-r from-slate-200 via-slate-100 to-transparent dark:from-white/[0.07] dark:via-white/[0.04] mb-4"></div>
            @if($this->therapistTodaySessions->isEmpty())
                <div class="py-8 text-center">
                    <flux:icon name="calendar" class="mx-auto size-10 text-slate-200 dark:text-zinc-700" />
                    <p class="mt-2 text-sm text-slate-400 dark:text-zinc-500">No sessions today</p>
                </div>
            @else
                <div class="space-y-2">
                    @foreach($this->therapistTodaySessions as $session)
                        <div class="flex items-center justify-between rounded-xl border border-slate-100 p-3 dark:border-zinc-700">
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-medium text-slate-500 dark:text-zinc-400 tabular-nums">{{ \Carbon\Carbon::parse($session->start_time)->format('g:i A') }}</span>
                                <div>
                                    <p class="text-sm font-medium text-slate-800 dark:text-zinc-100">{{ $session->resident->full_name }}</p>
                                    <p class="text-xs text-slate-400 dark:text-zinc-500">{{ $session->session_topic }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <flux:badge size="sm" :color="$session->status_color">{{ $session->status_label }}</flux:badge>
                                @if($session->status === 'completed' && !$session->progress_notes)
                                    <flux:button variant="primary" size="sm" :href="route('therapy.sessions.document', $session)" wire:navigate>Document</flux:button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Upcoming Sessions --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-white/[0.06] dark:bg-zinc-800/60">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-800 dark:text-zinc-100">Upcoming Sessions</h3>
                <flux:button variant="ghost" size="sm" :href="route('therapy.sessions.index')" wire:navigate>View all</flux:button>
            </div>
            <div class="h-px bg-gradient-to-r from-slate-200 via-slate-100 to-transparent dark:from-white/[0.07] dark:via-white/[0.04] mb-4"></div>
            @if($this->therapistUpcomingSessions->isEmpty())
                <div class="py-8 text-center">
                    <flux:icon name="calendar" class="mx-auto size-10 text-slate-200 dark:text-zinc-700" />
                    <p class="mt-2 text-sm text-slate-400 dark:text-zinc-500">No upcoming sessions</p>
                </div>
            @else
                <div class="space-y-2">
                    @foreach($this->therapistUpcomingSessions as $session)
                        <div class="flex items-center justify-between rounded-xl border border-slate-100 p-3 dark:border-zinc-700">
                            <div>
                                <p class="text-sm font-medium text-slate-800 dark:text-zinc-100">{{ $session->resident->full_name }}</p>
                                <p class="text-xs text-slate-400 dark:text-zinc-500">{{ $session->session_date->format('M d, Y') }} at {{ \Carbon\Carbon::parse($session->start_time)->format('g:i A') }}</p>
                            </div>
                            <flux:badge size="sm" :color="$session->service_type_color">{{ $session->service_type_label }}</flux:badge>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Pending Documentation --}}
    @if($this->therapistPendingDocumentation->isNotEmpty())
    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-white/[0.06] dark:bg-zinc-800/60">
        <h3 class="mb-4 text-sm font-semibold text-slate-800 dark:text-zinc-100">Pending Documentation</h3>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Resident</flux:table.column>
                <flux:table.column>Session Date</flux:table.column>
                <flux:table.column>Topic</flux:table.column>
                <flux:table.column class="w-24"></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach($this->therapistPendingDocumentation as $session)
                    <flux:table.row :key="$session->id">
                        <flux:table.cell class="font-medium">{{ $session->resident->full_name }}</flux:table.cell>
                        <flux:table.cell>{{ $session->session_date->format('M d, Y') }}</flux:table.cell>
                        <flux:table.cell>{{ Str::limit($session->session_topic, 40) }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:button variant="primary" size="sm" :href="route('therapy.sessions.document', $session)" wire:navigate>Document</flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>
    @endif
    @endif

    @endif {{-- end hasNoRole check --}}
    </div>{{-- end middle column --}}

    {{-- ══════════════════════════════════════════════════════
         RIGHT PANEL — Calendar, Residents, Quick Actions
    ══════════════════════════════════════════════════════ --}}
    @if(!$this->hasNoRole)
    @php
        $rpEvents     = $this->upcomingEvents;
        $rpEventsJson = collect($rpEvents)
            ->map(fn ($e) => ['d' => $e['day'], 'm' => $e['month'], 'y' => $e['year'], 'title' => $e['title']])
            ->values();
    @endphp
    <div class="hidden w-[17rem] shrink-0 flex-col gap-4 lg:flex xl:w-72">

        {{-- ── Calendar + Upcoming Check-list ── --}}
        <div
            x-data="{
                today:   new Date(),
                viewing: new Date(),
                events:  @json($rpEventsJson),
                get monthName()   { return this.viewing.toLocaleString('default',{month:'long'}); },
                get yr()          { return this.viewing.getFullYear(); },
                get mo()          { return this.viewing.getMonth(); },
                get daysInMonth() { return new Date(this.yr, this.mo+1, 0).getDate(); },
                get firstDay()    { let d=new Date(this.yr,this.mo,1).getDay(); return d===0?6:d-1; },
                prev()  { this.viewing=new Date(this.yr,this.mo-1,1); },
                next()  { this.viewing=new Date(this.yr,this.mo+1,1); },
                isToday(day) {
                    return day===this.today.getDate() && this.mo===this.today.getMonth() && this.yr===this.today.getFullYear();
                },
                hasEvent(day) {
                    return this.events.some(e=>e.d===day && e.m===(this.mo+1) && e.y===this.yr);
                },
                get monthEvents() {
                    return this.events.filter(e=>e.m===(this.mo+1)&&e.y===this.yr);
                }
            }"
            class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-white/[0.06] dark:bg-zinc-800/60"
        >
            {{-- Panel heading --}}
            <h3 class="mb-3 text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-zinc-500">Upcoming Check-list</h3>

            {{-- Month nav --}}
            <div class="mb-2 flex items-center justify-between">
                <button @click="prev()" class="flex size-6 items-center justify-center rounded-md text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-white/[0.07] dark:hover:text-zinc-200">
                    <flux:icon name="chevron-left" variant="micro" class="size-3.5" />
                </button>
                <span class="text-xs font-semibold text-slate-700 dark:text-zinc-200" x-text="monthName + ' ' + yr"></span>
                <button @click="next()" class="flex size-6 items-center justify-center rounded-md text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-white/[0.07] dark:hover:text-zinc-200">
                    <flux:icon name="chevron-right" variant="micro" class="size-3.5" />
                </button>
            </div>

            {{-- Day-name row --}}
            <div class="mb-1 grid grid-cols-7">
                @foreach(['M','T','W','T','F','S','S'] as $d)
                    <div class="py-0.5 text-center text-[10px] font-bold uppercase tracking-wide text-slate-300 dark:text-zinc-600">{{ $d }}</div>
                @endforeach
            </div>

            {{-- Day grid --}}
            <div class="grid auto-rows-fr grid-cols-7 gap-y-0.5">
                <template x-for="i in firstDay" :key="'pad'+i"><div></div></template>
                <template x-for="day in daysInMonth" :key="day">
                    <div class="flex items-center justify-center">
                        <button
                            class="relative flex size-6 items-center justify-center rounded-full text-[11px] transition"
                            :class="{
                                'bg-accent font-bold text-white shadow':       isToday(day),
                                'font-semibold text-accent':                   !isToday(day) && hasEvent(day),
                                'text-slate-500 hover:bg-slate-100 dark:text-zinc-400 dark:hover:bg-white/[0.06]': !isToday(day) && !hasEvent(day)
                            }"
                            x-text="day"
                        >
                            <span x-show="hasEvent(day) && !isToday(day)"
                                class="absolute bottom-0 left-1/2 size-1 -translate-x-1/2 rounded-full bg-accent"></span>
                        </button>
                    </div>
                </template>
            </div>

            {{-- Events list --}}
            <div class="mt-3 border-t border-slate-100 pt-3 dark:border-white/[0.06]">
                <template x-if="monthEvents.length === 0">
                    <p class="py-2 text-center text-[11px] text-slate-400 dark:text-zinc-500">No events this month</p>
                </template>
                <template x-for="(ev, idx) in monthEvents.slice(0,5)" :key="idx">
                    <div class="flex items-center gap-2 rounded-lg py-1.5">
                        <div class="flex size-6 shrink-0 items-center justify-center rounded-md bg-accent/10 text-[10px] font-bold text-accent"
                            x-text="ev.d"></div>
                        <span class="truncate text-[11px] text-slate-600 dark:text-zinc-300" x-text="ev.title"></span>
                    </div>
                </template>
            </div>
        </div>

        {{-- ── Residents Quick List ── --}}
        @can('view-residents')
        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-white/[0.06] dark:bg-zinc-800/60">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-zinc-500">Residents</h3>
                <flux:button size="xs" variant="ghost" :href="route('residents.index')" wire:navigate
                    class="!h-5 !px-1.5 !text-[10px]">View all</flux:button>
            </div>
            @forelse($this->recentResidents as $r)
                <a href="{{ route('residents.show', $r) }}" wire:navigate
                    class="flex items-center gap-2 rounded-lg px-1 py-1.5 transition hover:bg-slate-50 dark:hover:bg-zinc-800/50">
                    <div class="flex size-7 shrink-0 items-center justify-center rounded-full bg-accent/10 text-xs font-bold text-accent">
                        {{ strtoupper(substr($r->first_name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-medium text-slate-700 dark:text-zinc-200">{{ $r->full_name }}</p>
                        <p class="text-[10px] text-slate-400 dark:text-zinc-500">Room {{ $r->room_number ?? '—' }}</p>
                    </div>
                </a>
            @empty
                <p class="text-[11px] text-slate-400 dark:text-zinc-500">No active residents.</p>
            @endforelse
        </div>
        @endcan

        {{-- ── Quick Actions ── --}}
        @if(count($this->currentActions) > 0)
        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-white/[0.06] dark:bg-zinc-800/60">
            <h3 class="mb-3 text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-zinc-500">Quick Actions</h3>
            <div class="flex flex-col gap-1.5">
                @foreach($this->currentActions as $action)
                    <flux:button
                        :href="$action['href'] ?? '#'"
                        :icon="$action['icon'] ?? null"
                        size="sm"
                        variant="ghost"
                        wire:navigate
                        class="!justify-start !border-slate-200 text-slate-700 hover:!bg-slate-50 dark:!border-white/[0.08] dark:text-zinc-300 dark:hover:!bg-white/[0.06]"
                    >
                        {{ $action['label'] }}
                    </flux:button>
                @endforeach
            </div>
        </div>
        @endif

    </div>
    @endif {{-- end !hasNoRole right panel --}}

</div>
