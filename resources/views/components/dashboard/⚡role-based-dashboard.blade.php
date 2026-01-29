<?php

use App\Models\CarePlan;
use App\Models\Incident;
use App\Models\Medication;
use App\Models\Resident;
use App\Models\User;
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

    #[Computed]
    public function adminStats(): array
    {
        return [
            ['title' => 'Total Users', 'value' => User::count(), 'icon' => 'users'],
            ['title' => 'Active Roles', 'value' => \Spatie\Permission\Models\Role::count(), 'icon' => 'shield-check'],
            ['title' => 'System Alerts', 'value' => '0', 'icon' => 'bell'],
        ];
    }

    #[Computed]
    public function managerStats(): array
    {
        $activeResidents = Resident::active()->count();
        $activeCarePlans = CarePlan::active()->count();

        return [
            ['title' => 'Total Residents', 'value' => $activeResidents, 'icon' => 'user-group', 'description' => $activeResidents > 0 ? 'Active residents' : 'No residents registered yet'],
            ['title' => 'Staff on Duty', 'value' => '0', 'icon' => 'identification', 'description' => 'No staff scheduled'],
            ['title' => 'Active Care Plans', 'value' => $activeCarePlans, 'icon' => 'clipboard-document-list'],
        ];
    }

    #[Computed]
    public function nurseStats(): array
    {
        $activeResidents = Resident::active()->count();
        $activeMedications = Medication::active()->count();
        $openIncidents = Incident::open()->count();

        return [
            ['title' => 'My Residents', 'value' => $activeResidents, 'icon' => 'heart', 'description' => $activeResidents > 0 ? 'Active residents' : 'No residents assigned'],
            ['title' => 'Active Medications', 'value' => $activeMedications, 'icon' => 'beaker', 'description' => 'Active prescriptions'],
            ['title' => 'Open Incidents', 'value' => $openIncidents, 'icon' => 'exclamation-triangle', 'description' => $openIncidents > 0 ? 'Require attention' : 'No open incidents'],
        ];
    }

    #[Computed]
    public function caregiverStats(): array
    {
        $activeResidents = Resident::active()->count();

        return [
            ['title' => 'Assigned Residents', 'value' => $activeResidents, 'icon' => 'users', 'description' => $activeResidents > 0 ? 'Active residents' : 'No residents assigned'],
            ['title' => 'Tasks Today', 'value' => '0', 'icon' => 'clipboard-document-check'],
            ['title' => 'Shift Info', 'value' => '-', 'icon' => 'clock', 'description' => 'No shift scheduled'],
        ];
    }

    #[Computed]
    public function adminActions(): array
    {
        return [
            ['label' => 'Manage Users', 'href' => route('admin.users.index'), 'icon' => 'users'],
            ['label' => 'View Logs', 'href' => '#', 'icon' => 'document-text'],
            ['label' => 'Settings', 'href' => route('admin.settings.general'), 'icon' => 'cog-6-tooth'],
        ];
    }

    #[Computed]
    public function managerActions(): array
    {
        return [
            ['label' => 'Add Resident', 'href' => route('residents.create'), 'icon' => 'user-plus'],
            ['label' => 'Create Shift', 'href' => '#', 'icon' => 'calendar'],
            ['label' => 'View Reports', 'href' => '#', 'icon' => 'chart-bar'],
        ];
    }

    #[Computed]
    public function nurseActions(): array
    {
        return [
            ['label' => 'Record Vitals', 'href' => route('vitals.create'), 'icon' => 'heart'],
            ['label' => 'Medication Round', 'href' => route('medications.index'), 'icon' => 'beaker'],
            ['label' => 'Incident Report', 'href' => route('incidents.create'), 'icon' => 'exclamation-circle'],
        ];
    }

    #[Computed]
    public function caregiverActions(): array
    {
        return [
            ['label' => 'Log Activity', 'href' => '#', 'icon' => 'pencil-square'],
            ['label' => 'Request Help', 'href' => '#', 'icon' => 'hand-raised'],
            ['label' => 'View Care Plans', 'href' => route('care-plans.index'), 'icon' => 'document-text'],
        ];
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
        {{-- No Role Assigned --}}
        <x-dashboard.widget-card>
            <x-dashboard.empty-state
                title="No role assigned"
                description="Your account doesn't have any roles assigned yet. Please contact a system administrator to get started."
                icon="shield-exclamation"
            />
        </x-dashboard.widget-card>
    @else
        {{-- Admin Dashboard --}}
        @if($this->isAdmin)
            <div class="space-y-6">
                <flux:separator text="Administration" />
                <div class="grid gap-4 md:grid-cols-3">
                    @foreach($this->adminStats as $stat)
                        <x-dashboard.stat-card
                            :title="$stat['title']"
                            :value="$stat['value']"
                            :icon="$stat['icon']"
                            :description="$stat['description'] ?? null"
                        />
                    @endforeach
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <x-dashboard.quick-actions :actions="$this->adminActions" title="Admin Actions" />
                    <x-dashboard.widget-card title="Recent Activity" icon="clock">
                        <x-dashboard.empty-state
                            title="No recent activity"
                            description="System activity will appear here"
                            icon="clock"
                        />
                    </x-dashboard.widget-card>
                </div>
            </div>
        @endif

        {{-- Manager Dashboard --}}
        @if($this->isManager)
            <div class="space-y-6">
                <flux:separator text="Care Home Management" />
                <div class="grid gap-4 md:grid-cols-3">
                    @foreach($this->managerStats as $stat)
                        <x-dashboard.stat-card
                            :title="$stat['title']"
                            :value="$stat['value']"
                            :icon="$stat['icon']"
                            :description="$stat['description'] ?? null"
                        />
                    @endforeach
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <x-dashboard.quick-actions :actions="$this->managerActions" title="Manager Actions" />
                    <x-dashboard.widget-card title="Compliance Status" icon="clipboard-document-check">
                        <x-dashboard.empty-state
                            title="No compliance data"
                            description="Training compliance will be tracked here"
                            icon="academic-cap"
                        />
                    </x-dashboard.widget-card>
                </div>
            </div>
        @endif

        {{-- Nurse Dashboard --}}
        @if($this->isNurse)
            <div class="space-y-6">
                <flux:separator text="Clinical Overview" />
                <div class="grid gap-4 md:grid-cols-3">
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
                    <x-dashboard.widget-card title="Medication Schedule" icon="beaker">
                        <x-dashboard.empty-state
                            title="No medications scheduled"
                            description="Upcoming medication rounds will appear here"
                            icon="beaker"
                        />
                    </x-dashboard.widget-card>
                </div>
            </div>
        @endif

        {{-- Caregiver Dashboard --}}
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
                    <x-dashboard.widget-card title="Today's Tasks" icon="clipboard-document-list">
                        <x-dashboard.empty-state
                            title="No tasks assigned"
                            description="Your daily tasks will appear here"
                            icon="clipboard-document-list"
                        />
                    </x-dashboard.widget-card>
                </div>
            </div>
        @endif
    @endif
</div>
