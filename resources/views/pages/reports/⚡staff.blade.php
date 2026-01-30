<?php

use App\Models\Qualification;
use App\Models\Shift;
use App\Models\StaffProfile;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Staff Overview Report')]
class extends Component {
    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    #[Computed]
    public function summaryStats(): array
    {
        $totalStaff = StaffProfile::active()->count();
        $activeQualifications = Qualification::where('status', 'active')->count();
        $expiringSoon = Qualification::expiringSoon(30)->count();
        $expired = Qualification::where('status', 'active')
            ->where('expiry_date', '<', now())
            ->count();

        return [
            ['title' => 'Active Staff', 'value' => $totalStaff, 'icon' => 'identification', 'color' => 'blue'],
            ['title' => 'Active Qualifications', 'value' => $activeQualifications, 'icon' => 'academic-cap', 'color' => 'green'],
            ['title' => 'Expiring Soon (30d)', 'value' => $expiringSoon, 'icon' => 'clock', 'color' => 'amber', 'description' => $expiringSoon > 0 ? 'Require renewal' : 'All current'],
            ['title' => 'Expired', 'value' => $expired, 'icon' => 'exclamation-triangle', 'color' => 'red', 'description' => $expired > 0 ? 'Needs attention' : 'None expired'],
        ];
    }

    #[Computed]
    public function staffByDepartment(): array
    {
        return StaffProfile::active()
            ->whereNotNull('department')
            ->selectRaw('department, count(*) as count')
            ->groupBy('department')
            ->pluck('count', 'department')
            ->toArray();
    }

    #[Computed]
    public function shiftsToday(): int
    {
        return Shift::today()->scheduled()->count();
    }

    #[Computed]
    public function shiftsThisWeek(): int
    {
        return Shift::where('shift_date', '>=', now()->startOfWeek())
            ->where('shift_date', '<=', now()->endOfWeek())
            ->count();
    }

    #[Computed]
    public function shiftsByType(): array
    {
        $query = Shift::query();

        if ($this->dateFrom) {
            $query->where('shift_date', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->where('shift_date', '<=', $this->dateTo);
        }

        return $query
            ->selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();
    }

    #[Computed]
    public function qualificationsRequiringRenewal()
    {
        return Qualification::query()
            ->with('user')
            ->whereNotNull('expiry_date')
            ->where(function ($q) {
                $q->where('expiry_date', '<', now())
                    ->orWhere(function ($q2) {
                        $q2->where('expiry_date', '>', now())
                            ->where('expiry_date', '<=', now()->addDays(30));
                    });
            })
            ->orderBy('expiry_date')
            ->get();
    }
}; ?>

<flux:main>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:button variant="ghost" size="sm" :href="route('reports.index')" wire:navigate icon="arrow-left" class="mb-2">
                    {{ __('All Reports') }}
                </flux:button>
                <flux:heading size="xl">{{ __('Staff Overview Report') }}</flux:heading>
                <flux:subheading>{{ __('Staffing levels, shift coverage, and qualifications') }}</flux:subheading>
            </div>
            <div class="flex gap-2">
                <flux:input wire:model.live="dateFrom" type="date" size="sm" class="max-w-[150px]" placeholder="From" />
                <flux:input wire:model.live="dateTo" type="date" size="sm" class="max-w-[150px]" placeholder="To" />
            </div>
        </div>

        {{-- Summary Stats --}}
        <div class="grid gap-4 md:grid-cols-4">
            @foreach($this->summaryStats as $stat)
                <x-dashboard.stat-card
                    :title="$stat['title']"
                    :value="$stat['value']"
                    :icon="$stat['icon']"
                    :description="$stat['description'] ?? null"
                />
            @endforeach
        </div>

        {{-- Staff by Department + Shift Coverage --}}
        <div class="grid gap-4 md:grid-cols-2">
            {{-- Staff by Department --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Staff by Department') }}</flux:heading>
                <flux:separator />
                @if(count($this->staffByDepartment) > 0)
                    <div class="space-y-3">
                        @foreach($this->staffByDepartment as $department => $count)
                            <div class="flex items-center justify-between">
                                <flux:text>{{ $department }}</flux:text>
                                <flux:badge size="sm" color="zinc">{{ $count }}</flux:badge>
                            </div>
                        @endforeach
                    </div>
                @else
                    <flux:text class="text-zinc-500">{{ __('No department data recorded') }}</flux:text>
                @endif
            </flux:card>

            {{-- Shift Coverage --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Shift Coverage') }}</flux:heading>
                <flux:separator />
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <flux:text>{{ __('Shifts Today') }}</flux:text>
                        <flux:badge size="sm" color="sky">{{ $this->shiftsToday }}</flux:badge>
                    </div>
                    <div class="flex items-center justify-between">
                        <flux:text>{{ __('Shifts This Week') }}</flux:text>
                        <flux:badge size="sm" color="sky">{{ $this->shiftsThisWeek }}</flux:badge>
                    </div>
                    @if(count($this->shiftsByType) > 0)
                        <flux:separator />
                        <flux:subheading size="sm">{{ __('By Type (filtered period)') }}</flux:subheading>
                        @foreach($this->shiftsByType as $type => $count)
                            @php
                                $typeColor = match($type) {
                                    'morning' => 'amber',
                                    'afternoon' => 'sky',
                                    'night' => 'indigo',
                                    'custom' => 'zinc',
                                    default => 'zinc'
                                };
                            @endphp
                            <div class="flex items-center justify-between">
                                <flux:badge size="sm" :color="$typeColor">{{ ucfirst($type) }}</flux:badge>
                                <flux:text class="font-medium">{{ $count }}</flux:text>
                            </div>
                        @endforeach
                    @endif
                </div>
            </flux:card>
        </div>

        {{-- Qualifications Requiring Renewal --}}
        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('Qualifications Requiring Renewal') }}</flux:heading>
            <flux:separator />
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Staff Member') }}</flux:table.column>
                    <flux:table.column>{{ __('Qualification') }}</flux:table.column>
                    <flux:table.column>{{ __('Type') }}</flux:table.column>
                    <flux:table.column>{{ __('Expiry Date') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse($this->qualificationsRequiringRenewal as $qual)
                        @php
                            $isExpired = $qual->expiry_date->isPast();
                        @endphp
                        <flux:table.row :key="$qual->id">
                            <flux:table.cell>
                                @if($qual->user)
                                    <flux:link :href="route('staff.show', $qual->user)" wire:navigate>
                                        {{ $qual->user->name }}
                                    </flux:link>
                                @else
                                    -
                                @endif
                            </flux:table.cell>
                            <flux:table.cell class="font-medium">{{ $qual->title }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="zinc">{{ $qual->type_label }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell @class(['text-red-600 dark:text-red-400 font-medium' => $isExpired, 'text-amber-600 dark:text-amber-400 font-medium' => !$isExpired])>
                                {{ $qual->expiry_date->format('M d, Y') }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$isExpired ? 'red' : 'amber'">
                                    {{ $isExpired ? __('Expired') : __('Expiring Soon') }}
                                </flux:badge>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="py-8 text-center">
                                <x-dashboard.empty-state
                                    title="All qualifications are current"
                                    description="No qualifications require renewal at this time."
                                    icon="check-circle"
                                />
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>
</flux:main>
