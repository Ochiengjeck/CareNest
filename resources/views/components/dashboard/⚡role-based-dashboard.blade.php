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
    public function isAdmin(): bool
    {
        return in_array('system_admin', $this->roles);
    }

    #[Computed]
    public function isManager(): bool
    {
        return in_array('care_home_manager', $this->roles);
    }

    #[Computed]
    public function isNurse(): bool
    {
        return in_array('nurse', $this->roles);
    }

    #[Computed]
    public function isCaregiver(): bool
    {
        return in_array('caregiver', $this->roles);
    }

    #[Computed]
    public function isTherapist(): bool
    {
        return in_array('therapist', $this->roles);
    }

    #[Computed]
    public function hasNoRole(): bool
    {
        return empty($this->roles);
    }

    #[Computed]
    public function greeting(): string
    {
        $hour = now()->hour;
        if ($hour < 12) {
            return 'Good morning';
        } elseif ($hour < 17) {
            return 'Good afternoon';
        }
        return 'Good evening';
    }

    // ── Admin ──

    #[Computed]
    public function adminStats(): array
    {
        $totalUsers = User::count();
        $newUsersThisMonth = User::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $openIncidents = Incident::open()->count();
        $criticalIncidents = Incident::open()->where('severity', 'critical')->count();
        $actionsToday = AuditLog::whereDate('created_at', today())->count();

        return [
            ['title' => 'Total Users', 'value' => $totalUsers, 'icon' => 'users', 'trend' => $newUsersThisMonth > 0 ? "+{$newUsersThisMonth} this month" : null, 'trendUp' => true],
            ['title' => 'Active Residents', 'value' => Resident::active()->count(), 'icon' => 'user-group'],
            ['title' => 'Open Incidents', 'value' => $openIncidents, 'icon' => 'exclamation-triangle', 'description' => $criticalIncidents > 0 ? "{$criticalIncidents} critical" : 'No critical incidents'],
            ['title' => 'System Activity', 'value' => $actionsToday, 'icon' => 'document-text', 'description' => 'Actions today'],
        ];
    }

    #[Computed]
    public function adminActions(): array
    {
        return [
            ['label' => 'Manage Users', 'href' => route('admin.users.index'), 'icon' => 'users'],
            ['label' => 'Audit Logs', 'href' => route('admin.logs.index'), 'icon' => 'document-text'],
            ['label' => 'Settings', 'href' => route('admin.settings.general'), 'icon' => 'cog-6-tooth'],
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
            ->orderByDesc('admission_date')
            ->take(5)
            ->get();
    }

    #[Computed]
    public function admissionStats(): array
    {
        $thisMonth = Resident::whereMonth('admission_date', now()->month)
            ->whereYear('admission_date', now()->year)
            ->count();

        $lastMonth = Resident::whereMonth('admission_date', now()->subMonth()->month)
            ->whereYear('admission_date', now()->subMonth()->year)
            ->count();

        $thisWeek = Resident::where('admission_date', '>=', now()->startOfWeek())
            ->where('admission_date', '<=', now()->endOfWeek())
            ->count();

        $pendingDischarge = Resident::active()
            ->whereNotNull('discharge_date')
            ->where('discharge_date', '>=', today())
            ->count();

        return [
            'this_month' => $thisMonth,
            'last_month' => $lastMonth,
            'this_week' => $thisWeek,
            'pending_discharge' => $pendingDischarge,
            'trend' => $thisMonth - $lastMonth,
        ];
    }

    // ── Manager ──

    #[Computed]
    public function managerStats(): array
    {
        $activeResidents = Resident::active()->count();
        $newThisMonth = Resident::active()->whereMonth('admission_date', now()->month)->whereYear('admission_date', now()->year)->count();
        $staffOnDuty = Shift::today()->whereIn('status', ['scheduled', 'in_progress'])->distinct('user_id')->count('user_id');
        $activeCarePlans = CarePlan::active()->count();
        $reviewDue = CarePlan::active()->whereNotNull('review_date')->where('review_date', '<=', now()->addDays(7))->count();
        $openIncidents = Incident::open()->count();
        $severeIncidents = Incident::open()->whereIn('severity', ['critical', 'major'])->count();

        return [
            ['title' => 'Active Residents', 'value' => $activeResidents, 'icon' => 'user-group', 'trend' => $newThisMonth > 0 ? "+{$newThisMonth} this month" : null, 'trendUp' => true],
            ['title' => 'Staff on Duty', 'value' => $staffOnDuty, 'icon' => 'identification', 'description' => $staffOnDuty > 0 ? 'On shift today' : 'No staff scheduled'],
            ['title' => 'Care Plans', 'value' => $activeCarePlans, 'icon' => 'clipboard-document-list', 'description' => $reviewDue > 0 ? "{$reviewDue} due for review" : 'All up to date'],
            ['title' => 'Open Incidents', 'value' => $openIncidents, 'icon' => 'exclamation-triangle', 'description' => $severeIncidents > 0 ? "{$severeIncidents} critical/major" : 'No severe incidents'],
        ];
    }

    #[Computed]
    public function managerActions(): array
    {
        return [
            ['label' => 'Add Resident', 'href' => route('residents.create'), 'icon' => 'user-plus'],
            ['label' => 'Create Shift', 'href' => route('shifts.create'), 'icon' => 'calendar'],
            ['label' => 'View Reports', 'href' => route('reports.index'), 'icon' => 'chart-bar'],
        ];
    }

    #[Computed]
    public function managerCarePlansDueReview(): \Illuminate\Database\Eloquent\Collection
    {
        return CarePlan::active()
            ->whereNotNull('review_date')
            ->where('review_date', '<=', now()->addDays(7))
            ->with('resident')
            ->orderBy('review_date')
            ->take(5)
            ->get();
    }

    #[Computed]
    public function managerRecentIncidents(): \Illuminate\Database\Eloquent\Collection
    {
        return Incident::with('resident')
            ->latest('occurred_at')
            ->take(5)
            ->get();
    }

    #[Computed]
    public function managerMedicationOverview(): array
    {
        $today = MedicationLog::whereDate('administered_at', today());
        return [
            'given' => (clone $today)->where('status', 'given')->count(),
            'refused' => (clone $today)->where('status', 'refused')->count(),
            'missed' => (clone $today)->where('status', 'missed')->count(),
            'withheld' => (clone $today)->where('status', 'withheld')->count(),
        ];
    }

    // ── Nurse ──

    #[Computed]
    public function nurseStats(): array
    {
        $activeResidents = Resident::active()->count();
        $medsToday = MedicationLog::whereDate('administered_at', today())->count();
        $medsGiven = MedicationLog::whereDate('administered_at', today())->where('status', 'given')->count();
        $compliance = $medsToday > 0 ? round(($medsGiven / $medsToday) * 100) : 0;

        $abnormalVitals = Vital::where('recorded_at', '>=', now()->subDay())
            ->with('resident')
            ->get()
            ->filter(fn ($v) => $v->hasAbnormalValues())
            ->count();

        $openIncidents = Incident::open()->count();

        return [
            ['title' => 'Active Residents', 'value' => $activeResidents, 'icon' => 'heart', 'description' => 'Under care'],
            ['title' => 'Medications Today', 'value' => $medsToday, 'icon' => 'beaker', 'description' => $medsToday > 0 ? "{$compliance}% compliance" : 'No administrations yet'],
            ['title' => 'Abnormal Vitals', 'value' => $abnormalVitals, 'icon' => 'exclamation-circle', 'description' => 'In last 24 hours'],
            ['title' => 'Open Incidents', 'value' => $openIncidents, 'icon' => 'exclamation-triangle', 'description' => $openIncidents > 0 ? 'Require attention' : 'No open incidents'],
        ];
    }

    #[Computed]
    public function nurseActions(): array
    {
        return [
            ['label' => 'Record Vitals', 'href' => route('vitals.create'), 'icon' => 'heart'],
            ['label' => 'Medication Round', 'href' => route('medications.index'), 'icon' => 'beaker'],
            ['label' => 'Report Incident', 'href' => route('incidents.create'), 'icon' => 'exclamation-circle'],
        ];
    }

    #[Computed]
    public function nurseAbnormalVitals(): \Illuminate\Support\Collection
    {
        return Vital::where('recorded_at', '>=', now()->subDay())
            ->with('resident')
            ->latest('recorded_at')
            ->get()
            ->filter(fn ($v) => $v->hasAbnormalValues())
            ->take(5)
            ->values();
    }

    #[Computed]
    public function nurseRecentMedications(): \Illuminate\Database\Eloquent\Collection
    {
        return MedicationLog::with(['medication', 'resident'])
            ->latest('administered_at')
            ->take(5)
            ->get();
    }

    // ── Caregiver ──

    #[Computed]
    public function caregiverStats(): array
    {
        $activeResidents = Resident::active()->count();
        $todayShift = Shift::where('user_id', $this->user->id)->today()->first();
        $shiftValue = '-';
        $shiftDescription = 'No shift scheduled';

        if ($todayShift) {
            $shiftValue = $todayShift->type_label;
            $shiftDescription = \Carbon\Carbon::parse($todayShift->start_time)->format('H:i').' - '.\Carbon\Carbon::parse($todayShift->end_time)->format('H:i');
        }

        $openIncidents = Incident::open()->count();

        return [
            ['title' => 'Active Residents', 'value' => $activeResidents, 'icon' => 'users', 'description' => 'Under care'],
            ['title' => 'My Shift', 'value' => $shiftValue, 'icon' => 'clock', 'description' => $shiftDescription],
            ['title' => 'Open Incidents', 'value' => $openIncidents, 'icon' => 'exclamation-triangle', 'description' => $openIncidents > 0 ? 'Be aware' : 'All clear'],
        ];
    }

    #[Computed]
    public function caregiverActions(): array
    {
        return [
            ['label' => 'View Residents', 'href' => route('residents.index'), 'icon' => 'users'],
            ['label' => 'Report Incident', 'href' => route('incidents.create'), 'icon' => 'exclamation-circle'],
            ['label' => 'View Care Plans', 'href' => route('care-plans.index'), 'icon' => 'document-text'],
        ];
    }

    #[Computed]
    public function caregiverRecentCarePlans(): \Illuminate\Database\Eloquent\Collection
    {
        return CarePlan::active()
            ->with('resident')
            ->latest()
            ->take(5)
            ->get();
    }

    // ── Therapist ──

    #[Computed]
    public function therapistStats(): array
    {
        $userId = $this->user->id;
        $myResidents = TherapistAssignment::forTherapist($userId)->active()->count();
        $todaySessions = TherapySession::forTherapist($userId)->today()->count();

        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        $completedThisWeek = TherapySession::forTherapist($userId)
            ->completed()
            ->whereBetween('session_date', [$weekStart, $weekEnd])
            ->count();

        $pendingDocumentation = TherapySession::forTherapist($userId)
            ->where('status', 'completed')
            ->whereNull('progress_notes')
            ->count();

        return [
            ['title' => "Today's Sessions", 'value' => $todaySessions, 'icon' => 'calendar', 'color' => 'accent'],
            ['title' => 'Assigned Residents', 'value' => $myResidents, 'icon' => 'users', 'color' => 'green'],
            ['title' => 'Completed This Week', 'value' => $completedThisWeek, 'icon' => 'check-circle', 'color' => 'purple'],
            ['title' => 'Pending Documentation', 'value' => $pendingDocumentation, 'icon' => 'document-text', 'color' => 'amber'],
        ];
    }

    #[Computed]
    public function therapistActions(): array
    {
        return [
            ['label' => 'New Session', 'href' => route('therapy.sessions.create'), 'icon' => 'plus', 'variant' => 'primary'],
            ['label' => 'My Residents', 'href' => route('therapy.my-residents'), 'icon' => 'users'],
            ['label' => 'Generate Report', 'href' => route('therapy.reports.generate'), 'icon' => 'document-chart-bar', 'permission' => 'view-reports'],
        ];
    }

    #[Computed]
    public function therapistTodaySessions(): \Illuminate\Database\Eloquent\Collection
    {
        return TherapySession::forTherapist($this->user->id)
            ->today()
            ->with('resident')
            ->orderBy('start_time')
            ->get();
    }

    #[Computed]
    public function therapistUpcomingSessions(): \Illuminate\Database\Eloquent\Collection
    {
        return TherapySession::forTherapist($this->user->id)
            ->upcoming()
            ->where('session_date', '>', today())
            ->with('resident')
            ->take(5)
            ->get();
    }

    #[Computed]
    public function therapistPendingDocumentation(): \Illuminate\Database\Eloquent\Collection
    {
        return TherapySession::forTherapist($this->user->id)
            ->where('status', 'completed')
            ->whereNull('progress_notes')
            ->with('resident')
            ->orderBy('session_date', 'desc')
            ->take(5)
            ->get();
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Welcome Header --}}
    <div>
        <flux:heading size="xl">{{ $this->greeting }}, {{ $this->user->name }}</flux:heading>
        <flux:subheading class="mt-1">
            @if($this->hasNoRole)
                Welcome to CareNest. Please contact an administrator to assign your role.
            @else
                @foreach($this->roles as $index => $role)
                    <flux:badge size="sm" color="zinc" class="mr-1">
                        {{ str_replace('_', ' ', ucwords($role, '_')) }}
                    </flux:badge>
                @endforeach
            @endif
        </flux:subheading>
    </div>

    @if($this->hasNoRole)
        <x-dashboard.widget-card>
            <x-dashboard.empty-state
                title="No role assigned"
                description="Your account doesn't have any roles assigned yet. Please contact a system administrator to get started."
                icon="shield-exclamation"
            />
        </x-dashboard.widget-card>
    @else
        {{-- ══ Admin Dashboard ══ --}}
        @if($this->isAdmin)
            <div class="space-y-6">
                <flux:separator text="Administration" />
                <div class="grid gap-4 md:grid-cols-4">
                    @foreach($this->adminStats as $stat)
                        <x-dashboard.stat-card
                            :title="$stat['title']"
                            :value="$stat['value']"
                            :icon="$stat['icon']"
                            :description="$stat['description'] ?? null"
                            :trend="$stat['trend'] ?? null"
                            :trendUp="$stat['trendUp'] ?? true"
                        />
                    @endforeach
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <x-dashboard.quick-actions :actions="$this->adminActions" title="Admin Actions" />
                    <x-dashboard.widget-card title="Recent Activity" icon="clock">
                        @if($this->adminRecentActivity->isEmpty())
                            <flux:text class="text-sm text-zinc-500">No recent activity.</flux:text>
                        @else
                            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @foreach($this->adminRecentActivity as $log)
                                    <div class="flex items-center justify-between py-2.5">
                                        <div class="min-w-0 flex-1">
                                            <flux:text class="text-sm font-medium truncate">{{ $log->description ?: $log->action_label }}</flux:text>
                                            <flux:text class="text-xs text-zinc-500">{{ $log->user?->name ?? 'System' }} &middot; {{ $log->created_at->diffForHumans() }}</flux:text>
                                        </div>
                                        <flux:badge size="sm" :color="$log->action_color">{{ $log->action_label }}</flux:badge>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-800">
                                <flux:button variant="ghost" size="sm" :href="route('admin.logs.index')" icon="arrow-right" icon-trailing wire:navigate>
                                    {{ __('View all logs') }}
                                </flux:button>
                            </div>
                        @endif
                    </x-dashboard.widget-card>
                </div>

                {{-- Admissions Overview --}}
                <div class="grid gap-4 md:grid-cols-2">
                    <x-dashboard.widget-card title="Admissions Overview" icon="user-plus">
                        @php $admStats = $this->admissionStats; @endphp
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg">
                                <div class="text-2xl font-semibold theme-accent-text">{{ $admStats['this_month'] }}</div>
                                <flux:text class="text-xs text-zinc-500">This Month</flux:text>
                            </div>
                            <div class="text-center p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg">
                                <div class="text-2xl font-semibold text-zinc-600 dark:text-zinc-400">{{ $admStats['last_month'] }}</div>
                                <flux:text class="text-xs text-zinc-500">Last Month</flux:text>
                            </div>
                            <div class="text-center p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg">
                                <div class="text-2xl font-semibold text-green-600 dark:text-green-400">{{ $admStats['this_week'] }}</div>
                                <flux:text class="text-xs text-zinc-500">This Week</flux:text>
                            </div>
                            <div class="text-center p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg">
                                <div class="text-2xl font-semibold text-amber-600 dark:text-amber-400">{{ $admStats['pending_discharge'] }}</div>
                                <flux:text class="text-xs text-zinc-500">Pending Discharge</flux:text>
                            </div>
                        </div>
                        @if($admStats['trend'] != 0)
                            <div class="mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center gap-2">
                                <flux:icon name="{{ $admStats['trend'] > 0 ? 'arrow-trending-up' : 'arrow-trending-down' }}" variant="mini" class="size-4 {{ $admStats['trend'] > 0 ? 'text-green-600' : 'text-red-600' }}" />
                                <flux:text class="text-sm {{ $admStats['trend'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ abs($admStats['trend']) }} {{ $admStats['trend'] > 0 ? 'more' : 'fewer' }} than last month
                                </flux:text>
                            </div>
                        @endif
                    </x-dashboard.widget-card>

                    <x-dashboard.widget-card title="Recent Admissions" icon="users">
                        @if($this->recentAdmissions->isEmpty())
                            <flux:text class="text-sm text-zinc-500">No admissions in the last 30 days.</flux:text>
                        @else
                            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @foreach($this->recentAdmissions as $resident)
                                    <a href="{{ route('residents.show', $resident) }}" wire:navigate class="flex items-center justify-between py-2.5 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 -mx-1 px-1 rounded transition-colors">
                                        <div class="min-w-0 flex-1">
                                            <flux:text class="text-sm font-medium truncate">{{ $resident->full_name }}</flux:text>
                                            <flux:text class="text-xs text-zinc-500">Room {{ $resident->room_number ?? 'N/A' }} &middot; Admitted {{ $resident->admission_date->diffForHumans() }}</flux:text>
                                        </div>
                                        <flux:badge size="sm" color="green">New</flux:badge>
                                    </a>
                                @endforeach
                            </div>
                            <div class="mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-800">
                                <flux:button variant="ghost" size="sm" :href="route('residents.index')" icon="arrow-right" icon-trailing wire:navigate>
                                    {{ __('View all residents') }}
                                </flux:button>
                            </div>
                        @endif
                    </x-dashboard.widget-card>
                </div>
            </div>
        @endif

        {{-- ══ Manager Dashboard ══ --}}
        @if($this->isManager)
            <div class="space-y-6">
                <flux:separator text="Care Home Management" />
                <div class="grid gap-4 md:grid-cols-4">
                    @foreach($this->managerStats as $stat)
                        <x-dashboard.stat-card
                            :title="$stat['title']"
                            :value="$stat['value']"
                            :icon="$stat['icon']"
                            :description="$stat['description'] ?? null"
                            :trend="$stat['trend'] ?? null"
                            :trendUp="$stat['trendUp'] ?? true"
                        />
                    @endforeach
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <x-dashboard.quick-actions :actions="$this->managerActions" title="Manager Actions" />

                    {{-- Medication Overview --}}
                    <x-dashboard.widget-card title="Medication Overview" icon="beaker">
                        @php $medOverview = $this->managerMedicationOverview; $medTotal = array_sum($medOverview); @endphp
                        @if($medTotal === 0)
                            <flux:text class="text-sm text-zinc-500">No medications administered today.</flux:text>
                        @else
                            <div class="grid grid-cols-4 gap-3 text-center">
                                <div>
                                    <div class="text-2xl font-semibold text-green-600 dark:text-green-400">{{ $medOverview['given'] }}</div>
                                    <flux:text class="text-xs text-zinc-500">Given</flux:text>
                                </div>
                                <div>
                                    <div class="text-2xl font-semibold text-amber-600 dark:text-amber-400">{{ $medOverview['refused'] }}</div>
                                    <flux:text class="text-xs text-zinc-500">Refused</flux:text>
                                </div>
                                <div>
                                    <div class="text-2xl font-semibold text-red-600 dark:text-red-400">{{ $medOverview['missed'] }}</div>
                                    <flux:text class="text-xs text-zinc-500">Missed</flux:text>
                                </div>
                                <div>
                                    <div class="text-2xl font-semibold theme-accent-text">{{ $medOverview['withheld'] }}</div>
                                    <flux:text class="text-xs text-zinc-500">Withheld</flux:text>
                                </div>
                            </div>
                        @endif
                    </x-dashboard.widget-card>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    {{-- Care Plans Due for Review --}}
                    <x-dashboard.widget-card title="Care Plans Due for Review" icon="clipboard-document-list">
                        @if($this->managerCarePlansDueReview->isEmpty())
                            <flux:text class="text-sm text-zinc-500">No care plans due for review.</flux:text>
                        @else
                            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @foreach($this->managerCarePlansDueReview as $plan)
                                    <a href="{{ route('care-plans.show', $plan) }}" wire:navigate class="flex items-center justify-between py-2.5 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 -mx-1 px-1 rounded transition-colors">
                                        <div class="min-w-0 flex-1">
                                            <flux:text class="text-sm font-medium truncate">{{ $plan->title }}</flux:text>
                                            <flux:text class="text-xs text-zinc-500">{{ $plan->resident?->full_name }} &middot; Review {{ $plan->review_date->diffForHumans() }}</flux:text>
                                        </div>
                                        @if($plan->review_date->isPast())
                                            <flux:badge size="sm" color="red">Overdue</flux:badge>
                                        @else
                                            <flux:badge size="sm" color="amber">Due soon</flux:badge>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </x-dashboard.widget-card>

                    {{-- Recent Incidents --}}
                    <x-dashboard.widget-card title="Recent Incidents" icon="exclamation-triangle">
                        @if($this->managerRecentIncidents->isEmpty())
                            <flux:text class="text-sm text-zinc-500">No incidents recorded.</flux:text>
                        @else
                            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @foreach($this->managerRecentIncidents as $incident)
                                    <a href="{{ route('incidents.show', $incident) }}" wire:navigate class="flex items-center justify-between py-2.5 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 -mx-1 px-1 rounded transition-colors">
                                        <div class="min-w-0 flex-1">
                                            <flux:text class="text-sm font-medium truncate">{{ $incident->title }}</flux:text>
                                            <flux:text class="text-xs text-zinc-500">{{ $incident->resident?->full_name }} &middot; {{ $incident->occurred_at->diffForHumans() }}</flux:text>
                                        </div>
                                        <flux:badge size="sm" :color="$incident->severity_color">{{ ucfirst($incident->severity) }}</flux:badge>
                                    </a>
                                @endforeach
                            </div>
                            <div class="mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-800">
                                <flux:button variant="ghost" size="sm" :href="route('incidents.index')" icon="arrow-right" icon-trailing wire:navigate>
                                    {{ __('View all incidents') }}
                                </flux:button>
                            </div>
                        @endif
                    </x-dashboard.widget-card>
                </div>

                {{-- Recent Admissions --}}
                <x-dashboard.widget-card title="Recent Admissions (Last 30 Days)" icon="user-plus">
                    @if($this->recentAdmissions->isEmpty())
                        <flux:text class="text-sm text-zinc-500">No new admissions in the last 30 days.</flux:text>
                    @else
                        <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach($this->recentAdmissions as $resident)
                                <a href="{{ route('residents.show', $resident) }}" wire:navigate class="flex items-center justify-between py-2.5 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 -mx-1 px-1 rounded transition-colors">
                                    <div class="min-w-0 flex-1">
                                        <flux:text class="text-sm font-medium truncate">{{ $resident->full_name }}</flux:text>
                                        <flux:text class="text-xs text-zinc-500">
                                            Room {{ $resident->room_number ?? 'N/A' }} &middot;
                                            Admitted {{ $resident->admission_date->format('M d, Y') }}
                                            ({{ $resident->admission_date->diffForHumans() }})
                                        </flux:text>
                                    </div>
                                    <flux:badge size="sm" color="green">New</flux:badge>
                                </a>
                            @endforeach
                        </div>
                        <div class="mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-800">
                            <flux:button variant="ghost" size="sm" :href="route('residents.index')" icon="arrow-right" icon-trailing wire:navigate>
                                {{ __('View all residents') }}
                            </flux:button>
                        </div>
                    @endif
                </x-dashboard.widget-card>
            </div>
        @endif

        {{-- ══ Nurse Dashboard ══ --}}
        @if($this->isNurse)
            <div class="space-y-6">
                <flux:separator text="Clinical Overview" />
                <div class="grid gap-4 md:grid-cols-4">
                    @foreach($this->nurseStats as $stat)
                        <x-dashboard.stat-card
                            :title="$stat['title']"
                            :value="$stat['value']"
                            :icon="$stat['icon']"
                            :description="$stat['description'] ?? null"
                        />
                    @endforeach
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <x-dashboard.quick-actions :actions="$this->nurseActions" title="Clinical Actions" />

                    {{-- Abnormal Vitals --}}
                    <x-dashboard.widget-card title="Vitals Alerts (24h)" icon="exclamation-circle">
                        @if($this->nurseAbnormalVitals->isEmpty())
                            <flux:text class="text-sm text-zinc-500">No abnormal vitals in the last 24 hours.</flux:text>
                        @else
                            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @foreach($this->nurseAbnormalVitals as $vital)
                                    <a href="{{ route('vitals.show', $vital) }}" wire:navigate class="flex items-center justify-between py-2.5 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 -mx-1 px-1 rounded transition-colors">
                                        <div class="min-w-0 flex-1">
                                            <flux:text class="text-sm font-medium truncate">{{ $vital->resident?->full_name }}</flux:text>
                                            <flux:text class="text-xs text-zinc-500">Recorded {{ $vital->recorded_at->diffForHumans() }}</flux:text>
                                        </div>
                                        <flux:badge size="sm" color="red">Abnormal</flux:badge>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </x-dashboard.widget-card>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    {{-- Recent Medications --}}
                    <x-dashboard.widget-card title="Recent Medication Administrations" icon="beaker">
                        @if($this->nurseRecentMedications->isEmpty())
                            <flux:text class="text-sm text-zinc-500">No recent medication administrations.</flux:text>
                        @else
                            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @foreach($this->nurseRecentMedications as $log)
                                    <div class="flex items-center justify-between py-2.5">
                                        <div class="min-w-0 flex-1">
                                            <flux:text class="text-sm font-medium truncate">{{ $log->medication?->name ?? 'Unknown' }} — {{ $log->medication?->dosage ?? '' }}</flux:text>
                                            <flux:text class="text-xs text-zinc-500">{{ $log->resident?->full_name }} &middot; {{ $log->administered_at->diffForHumans() }}</flux:text>
                                        </div>
                                        <flux:badge size="sm" :color="$log->status_color">{{ ucfirst($log->status) }}</flux:badge>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </x-dashboard.widget-card>

                    {{-- New Admissions for Clinical Review --}}
                    <x-dashboard.widget-card title="New Admissions" icon="user-plus">
                        @if($this->recentAdmissions->isEmpty())
                            <flux:text class="text-sm text-zinc-500">No new admissions requiring review.</flux:text>
                        @else
                            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @foreach($this->recentAdmissions->take(3) as $resident)
                                    <a href="{{ route('residents.show', $resident) }}" wire:navigate class="flex items-center justify-between py-2.5 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 -mx-1 px-1 rounded transition-colors">
                                        <div class="min-w-0 flex-1">
                                            <flux:text class="text-sm font-medium truncate">{{ $resident->full_name }}</flux:text>
                                            <flux:text class="text-xs text-zinc-500">
                                                Room {{ $resident->room_number ?? 'N/A' }} &middot;
                                                {{ $resident->admission_date->diffForHumans() }}
                                            </flux:text>
                                        </div>
                                        <flux:badge size="sm" color="green">New</flux:badge>
                                    </a>
                                @endforeach
                            </div>
                            @if($this->recentAdmissions->count() > 3)
                                <div class="mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-800">
                                    <flux:button variant="ghost" size="sm" :href="route('residents.index')" icon="arrow-right" icon-trailing wire:navigate>
                                        {{ __('View all residents') }}
                                    </flux:button>
                                </div>
                            @endif
                        @endif
                    </x-dashboard.widget-card>
                </div>
            </div>
        @endif

        {{-- ══ Caregiver Dashboard ══ --}}
        @if($this->isCaregiver)
            <div class="space-y-6">
                <flux:separator text="My Shift" />
                <div class="grid gap-4 md:grid-cols-3">
                    @foreach($this->caregiverStats as $stat)
                        <x-dashboard.stat-card
                            :title="$stat['title']"
                            :value="$stat['value']"
                            :icon="$stat['icon']"
                            :description="$stat['description'] ?? null"
                        />
                    @endforeach
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <x-dashboard.quick-actions :actions="$this->caregiverActions" title="Quick Actions" />

                    {{-- Active Care Plans --}}
                    <x-dashboard.widget-card title="Active Care Plans" icon="clipboard-document-list">
                        @if($this->caregiverRecentCarePlans->isEmpty())
                            <flux:text class="text-sm text-zinc-500">No active care plans.</flux:text>
                        @else
                            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @foreach($this->caregiverRecentCarePlans as $plan)
                                    <a href="{{ route('care-plans.show', $plan) }}" wire:navigate class="flex items-center justify-between py-2.5 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 -mx-1 px-1 rounded transition-colors">
                                        <div class="min-w-0 flex-1">
                                            <flux:text class="text-sm font-medium truncate">{{ $plan->title }}</flux:text>
                                            <flux:text class="text-xs text-zinc-500">{{ $plan->resident?->full_name }}</flux:text>
                                        </div>
                                        <flux:badge size="sm" :color="$plan->status_color">{{ $plan->type_label }}</flux:badge>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </x-dashboard.widget-card>
                </div>
            </div>
        @endif

        {{-- ══ Therapist Dashboard ══ --}}
        @if($this->isTherapist)
            <div class="space-y-6">
                <flux:separator text="Therapy" />

                {{-- Quick Stats --}}
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($this->therapistStats as $stat)
                        <flux:card>
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-lg
                                    @if($stat['color'] === 'accent') theme-accent-bg-muted
                                    @elseif($stat['color'] === 'green') bg-green-100 dark:bg-green-900/30
                                    @elseif($stat['color'] === 'purple') bg-purple-100 dark:bg-purple-900/30
                                    @elseif($stat['color'] === 'amber') bg-amber-100 dark:bg-amber-900/30
                                    @else bg-zinc-100 dark:bg-zinc-800
                                    @endif
                                ">
                                    <flux:icon :name="$stat['icon']" class="h-6 w-6
                                        @if($stat['color'] === 'accent') theme-accent-text
                                        @elseif($stat['color'] === 'green') text-green-600 dark:text-green-400
                                        @elseif($stat['color'] === 'purple') text-purple-600 dark:text-purple-400
                                        @elseif($stat['color'] === 'amber') text-amber-600 dark:text-amber-400
                                        @else text-zinc-600 dark:text-zinc-400
                                        @endif
                                    " />
                                </div>
                                <div>
                                    <div class="text-2xl font-bold">{{ $stat['value'] }}</div>
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $stat['title'] }}</div>
                                </div>
                            </div>
                        </flux:card>
                    @endforeach
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    {{-- Today's Sessions --}}
                    <flux:card>
                        <div class="flex items-center justify-between mb-4">
                            <flux:heading size="sm">{{ __("Today's Sessions") }}</flux:heading>
                            <flux:button variant="ghost" size="sm" :href="route('therapy.sessions.create')" wire:navigate icon="plus">
                                {{ __('New Session') }}
                            </flux:button>
                        </div>

                        @if($this->therapistTodaySessions->isEmpty())
                            <div class="py-8 text-center">
                                <flux:icon name="calendar" class="mx-auto h-12 w-12 text-zinc-300 dark:text-zinc-600" />
                                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('No sessions scheduled for today') }}</p>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach($this->therapistTodaySessions as $session)
                                    <div class="flex items-center justify-between rounded-lg border border-zinc-200 dark:border-zinc-700 p-3">
                                        <div class="flex items-center gap-3">
                                            <div class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                                                {{ Carbon\Carbon::parse($session->start_time)->format('g:i A') }}
                                            </div>
                                            <div>
                                                <div class="font-medium">{{ $session->resident->full_name }}</div>
                                                <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $session->session_topic }}</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <flux:badge size="sm" :color="$session->status_color">{{ $session->status_label }}</flux:badge>
                                            @if($session->status === 'completed' && !$session->progress_notes)
                                                <flux:button variant="primary" size="sm" :href="route('therapy.sessions.document', $session)" wire:navigate>
                                                    {{ __('Document') }}
                                                </flux:button>
                                            @elseif($session->status === 'scheduled')
                                                <flux:button variant="ghost" size="sm" :href="route('therapy.sessions.show', $session)" wire:navigate icon="eye" />
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </flux:card>

                    {{-- Upcoming Sessions --}}
                    <flux:card>
                        <div class="flex items-center justify-between mb-4">
                            <flux:heading size="sm">{{ __('Upcoming Sessions') }}</flux:heading>
                            <flux:button variant="ghost" size="sm" :href="route('therapy.sessions.index')" wire:navigate>
                                {{ __('View All') }}
                            </flux:button>
                        </div>

                        @if($this->therapistUpcomingSessions->isEmpty())
                            <div class="py-8 text-center">
                                <flux:icon name="calendar" class="mx-auto h-12 w-12 text-zinc-300 dark:text-zinc-600" />
                                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('No upcoming sessions') }}</p>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach($this->therapistUpcomingSessions as $session)
                                    <div class="flex items-center justify-between rounded-lg border border-zinc-200 dark:border-zinc-700 p-3">
                                        <div>
                                            <div class="font-medium">{{ $session->resident->full_name }}</div>
                                            <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                                {{ $session->session_date->format('M d, Y') }} at {{ Carbon\Carbon::parse($session->start_time)->format('g:i A') }}
                                            </div>
                                        </div>
                                        <flux:badge size="sm" :color="$session->service_type_color">{{ $session->service_type_label }}</flux:badge>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </flux:card>
                </div>

                {{-- Pending Documentation --}}
                @if($this->therapistPendingDocumentation->isNotEmpty())
                    <flux:card>
                        <div class="flex items-center justify-between mb-4">
                            <flux:heading size="sm">{{ __('Pending Documentation') }}</flux:heading>
                        </div>

                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>{{ __('Resident') }}</flux:table.column>
                                <flux:table.column>{{ __('Session Date') }}</flux:table.column>
                                <flux:table.column>{{ __('Topic') }}</flux:table.column>
                                <flux:table.column class="w-24"></flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach($this->therapistPendingDocumentation as $session)
                                    <flux:table.row :key="$session->id">
                                        <flux:table.cell class="font-medium">{{ $session->resident->full_name }}</flux:table.cell>
                                        <flux:table.cell>{{ $session->session_date->format('M d, Y') }}</flux:table.cell>
                                        <flux:table.cell>{{ Str::limit($session->session_topic, 40) }}</flux:table.cell>
                                        <flux:table.cell>
                                            <flux:button variant="primary" size="sm" :href="route('therapy.sessions.document', $session)" wire:navigate>
                                                {{ __('Document') }}
                                            </flux:button>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    </flux:card>
                @endif

                {{-- Quick Actions --}}
                <div class="flex flex-wrap gap-3">
                    <flux:button variant="primary" :href="route('therapy.sessions.create')" wire:navigate icon="plus">
                        {{ __('New Session') }}
                    </flux:button>
                    <flux:button variant="ghost" :href="route('therapy.my-residents')" wire:navigate icon="users">
                        {{ __('My Residents') }}
                    </flux:button>
                    @can('view-reports')
                    <flux:button variant="ghost" :href="route('therapy.reports.generate')" wire:navigate icon="document-chart-bar">
                        {{ __('Generate Report') }}
                    </flux:button>
                    @endcan
                </div>
            </div>
        @endif
    @endif
</div>
