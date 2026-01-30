<?php

use App\Models\Incident;
use App\Models\Medication;
use App\Models\MedicationLog;
use App\Models\Vital;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Clinical Summary Report')]
class extends Component {
    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    #[Computed]
    public function summaryStats(): array
    {
        $activeMeds = Medication::active()->count();
        $totalVitals = $this->dateFilteredQuery(Vital::query(), 'recorded_at')->count();
        $totalIncidents = $this->dateFilteredQuery(Incident::query(), 'occurred_at')->count();

        $totalLogs = $this->dateFilteredQuery(MedicationLog::query(), 'administered_at')->count();
        $givenLogs = $this->dateFilteredQuery(MedicationLog::query(), 'administered_at')
            ->where('status', 'given')->count();
        $complianceRate = $totalLogs > 0 ? round(($givenLogs / $totalLogs) * 100, 1) : 0;

        return [
            ['title' => 'Active Medications', 'value' => $activeMeds, 'icon' => 'beaker', 'color' => 'blue'],
            ['title' => 'Compliance Rate', 'value' => $complianceRate . '%', 'icon' => 'check-circle', 'color' => 'green', 'description' => "{$givenLogs} of {$totalLogs} doses given"],
            ['title' => 'Vitals Recorded', 'value' => $totalVitals, 'icon' => 'heart', 'color' => 'red'],
            ['title' => 'Total Incidents', 'value' => $totalIncidents, 'icon' => 'exclamation-triangle', 'color' => 'amber'],
        ];
    }

    #[Computed]
    public function medicationComplianceBreakdown(): array
    {
        $total = $this->dateFilteredQuery(MedicationLog::query(), 'administered_at')->count();

        if ($total === 0) {
            return [];
        }

        $statuses = ['given', 'refused', 'withheld', 'missed'];
        $breakdown = [];

        foreach ($statuses as $status) {
            $count = $this->dateFilteredQuery(MedicationLog::query(), 'administered_at')
                ->where('status', $status)->count();
            $breakdown[$status] = [
                'count' => $count,
                'percentage' => round(($count / $total) * 100, 1),
            ];
        }

        return $breakdown;
    }

    #[Computed]
    public function incidentsByType(): array
    {
        return $this->dateFilteredQuery(Incident::query(), 'occurred_at')
            ->selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();
    }

    #[Computed]
    public function incidentsBySeverity(): array
    {
        return $this->dateFilteredQuery(Incident::query(), 'occurred_at')
            ->selectRaw('severity, count(*) as count')
            ->groupBy('severity')
            ->pluck('count', 'severity')
            ->toArray();
    }

    #[Computed]
    public function recentAbnormalVitals()
    {
        return Vital::query()
            ->with(['resident', 'recordedBy'])
            ->latest('recorded_at')
            ->limit(100)
            ->get()
            ->filter(fn ($v) => $v->hasAbnormalValues())
            ->take(15);
    }

    private function dateFilteredQuery($query, string $column)
    {
        return $query
            ->when($this->dateFrom, fn ($q) => $q->where($column, '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->where($column, '<=', $this->dateTo . ' 23:59:59'));
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
                <flux:heading size="xl">{{ __('Clinical Summary Report') }}</flux:heading>
                <flux:subheading>{{ __('Medication compliance, vitals, and incident analysis') }}</flux:subheading>
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

        {{-- Medication Compliance Breakdown --}}
        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('Medication Compliance Breakdown') }}</flux:heading>
            <flux:separator />
            @if(count($this->medicationComplianceBreakdown) > 0)
                <div class="space-y-3">
                    @foreach($this->medicationComplianceBreakdown as $status => $data)
                        @php
                            $color = match($status) {
                                'given' => 'green',
                                'refused' => 'amber',
                                'withheld' => 'sky',
                                'missed' => 'red',
                                default => 'zinc'
                            };
                        @endphp
                        <div class="flex items-center justify-between gap-4">
                            <div class="w-24">
                                <flux:badge size="sm" :color="$color">{{ ucfirst($status) }}</flux:badge>
                            </div>
                            <div class="flex flex-1 items-center gap-4">
                                <div class="h-2 flex-1 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                                    <div class="h-2 rounded-full bg-{{ $color }}-500" style="width: {{ $data['percentage'] }}%"></div>
                                </div>
                                <flux:text class="w-28 text-right text-sm">{{ $data['count'] }} ({{ $data['percentage'] }}%)</flux:text>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <flux:text class="text-zinc-500">{{ __('No medication administration data for selected period') }}</flux:text>
            @endif
        </flux:card>

        {{-- Incidents Analysis --}}
        <div class="grid gap-4 md:grid-cols-2">
            {{-- By Type --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Incidents by Type') }}</flux:heading>
                <flux:separator />
                @if(count($this->incidentsByType) > 0)
                    <div class="space-y-3">
                        @foreach($this->incidentsByType as $type => $count)
                            @php
                                $typeLabel = match($type) {
                                    'fall' => 'Fall',
                                    'medication_error' => 'Medication Error',
                                    'injury' => 'Injury',
                                    'behavioral' => 'Behavioral',
                                    'equipment_failure' => 'Equipment Failure',
                                    'other' => 'Other',
                                    default => ucfirst($type)
                                };
                            @endphp
                            <div class="flex items-center justify-between">
                                <flux:text>{{ $typeLabel }}</flux:text>
                                <flux:badge size="sm" color="zinc">{{ $count }}</flux:badge>
                            </div>
                        @endforeach
                    </div>
                @else
                    <flux:text class="text-zinc-500">{{ __('No incidents for selected period') }}</flux:text>
                @endif
            </flux:card>

            {{-- By Severity --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Incidents by Severity') }}</flux:heading>
                <flux:separator />
                @if(count($this->incidentsBySeverity) > 0)
                    <div class="space-y-3">
                        @foreach($this->incidentsBySeverity as $severity => $count)
                            @php
                                $severityColor = match($severity) {
                                    'minor' => 'green',
                                    'moderate' => 'amber',
                                    'major' => 'orange',
                                    'critical' => 'red',
                                    default => 'zinc'
                                };
                            @endphp
                            <div class="flex items-center justify-between">
                                <flux:badge size="sm" :color="$severityColor">{{ ucfirst($severity) }}</flux:badge>
                                <flux:text class="font-medium">{{ $count }}</flux:text>
                            </div>
                        @endforeach
                    </div>
                @else
                    <flux:text class="text-zinc-500">{{ __('No incidents for selected period') }}</flux:text>
                @endif
            </flux:card>
        </div>

        {{-- Recent Abnormal Vitals --}}
        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('Recent Abnormal Vitals') }}</flux:heading>
            <flux:separator />
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Resident') }}</flux:table.column>
                    <flux:table.column>{{ __('Recorded At') }}</flux:table.column>
                    <flux:table.column>{{ __('BP') }}</flux:table.column>
                    <flux:table.column>{{ __('HR') }}</flux:table.column>
                    <flux:table.column>{{ __('Temp') }}</flux:table.column>
                    <flux:table.column>{{ __('SpO2') }}</flux:table.column>
                    <flux:table.column>{{ __('Recorded By') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse($this->recentAbnormalVitals as $vital)
                        @php
                            $bpAbnormal = ($vital->blood_pressure_systolic && ($vital->blood_pressure_systolic < 90 || $vital->blood_pressure_systolic > 140))
                                || ($vital->blood_pressure_diastolic && ($vital->blood_pressure_diastolic < 60 || $vital->blood_pressure_diastolic > 90));
                            $hrAbnormal = $vital->heart_rate && ($vital->heart_rate < 60 || $vital->heart_rate > 100);
                            $tempAbnormal = $vital->temperature && ($vital->temperature < 36 || $vital->temperature > 37.5);
                            $spo2Abnormal = $vital->oxygen_saturation && $vital->oxygen_saturation < 95;
                        @endphp
                        <flux:table.row :key="$vital->id">
                            <flux:table.cell>
                                <flux:link :href="route('residents.show', $vital->resident)" wire:navigate>
                                    {{ $vital->resident?->full_name ?? '-' }}
                                </flux:link>
                            </flux:table.cell>
                            <flux:table.cell>{{ $vital->recorded_at->format('M d, Y H:i') }}</flux:table.cell>
                            <flux:table.cell @class(['text-red-600 dark:text-red-400 font-medium' => $bpAbnormal])>
                                {{ $vital->blood_pressure ?? '-' }}
                            </flux:table.cell>
                            <flux:table.cell @class(['text-red-600 dark:text-red-400 font-medium' => $hrAbnormal])>
                                {{ $vital->heart_rate ?? '-' }}
                            </flux:table.cell>
                            <flux:table.cell @class(['text-red-600 dark:text-red-400 font-medium' => $tempAbnormal])>
                                {{ $vital->temperature ? $vital->temperature . '°C' : '-' }}
                            </flux:table.cell>
                            <flux:table.cell @class(['text-red-600 dark:text-red-400 font-medium' => $spo2Abnormal])>
                                {{ $vital->oxygen_saturation ? $vital->oxygen_saturation . '%' : '-' }}
                            </flux:table.cell>
                            <flux:table.cell>{{ $vital->recordedBy?->name ?? '-' }}</flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="7" class="py-8 text-center">
                                <x-dashboard.empty-state
                                    title="No abnormal vitals"
                                    description="All recent vital signs are within normal ranges."
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
