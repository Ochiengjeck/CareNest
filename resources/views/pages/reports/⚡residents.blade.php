<?php

use App\Models\Resident;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Resident Overview Report')]
class extends Component {
    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    #[Computed]
    public function summaryStats(): array
    {
        $total = Resident::active()->count();
        $maleCount = Resident::active()->where('gender', 'male')->count();
        $femaleCount = Resident::active()->where('gender', 'female')->count();
        $dnrCount = Resident::active()->where('dnr_status', true)->count();

        $avgAge = Resident::active()
            ->whereNotNull('date_of_birth')
            ->get()
            ->avg(fn ($r) => $r->age);

        return [
            ['title' => 'Active Residents', 'value' => $total, 'icon' => 'user-group', 'color' => 'blue'],
            ['title' => 'Average Age', 'value' => $avgAge ? round($avgAge, 1) : '-', 'icon' => 'calendar', 'color' => 'green'],
            ['title' => 'Male / Female', 'value' => "{$maleCount} / {$femaleCount}", 'icon' => 'users', 'color' => 'purple'],
            ['title' => 'DNR Status', 'value' => $dnrCount, 'icon' => 'shield-exclamation', 'color' => 'red', 'description' => 'Residents with DNR'],
        ];
    }

    #[Computed]
    public function fallRiskDistribution(): array
    {
        return Resident::active()
            ->whereNotNull('fall_risk_level')
            ->selectRaw('fall_risk_level, count(*) as count')
            ->groupBy('fall_risk_level')
            ->pluck('count', 'fall_risk_level')
            ->toArray();
    }

    #[Computed]
    public function mobilityDistribution(): array
    {
        return Resident::active()
            ->whereNotNull('mobility_status')
            ->selectRaw('mobility_status, count(*) as count')
            ->groupBy('mobility_status')
            ->pluck('count', 'mobility_status')
            ->toArray();
    }

    #[Computed]
    public function admissionTrends(): array
    {
        $query = Resident::query();

        if ($this->dateFrom) {
            $query->where('admission_date', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->where('admission_date', '<=', $this->dateTo);
        }

        return $query
            ->whereNotNull('admission_date')
            ->selectRaw("strftime('%Y-%m', admission_date) as month, count(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();
    }

    #[Computed]
    public function residents()
    {
        return Resident::active()
            ->withCount(['carePlans', 'medications', 'incidents'])
            ->orderBy('first_name')
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
                <flux:heading size="xl">{{ __('Resident Overview Report') }}</flux:heading>
                <flux:subheading>{{ __('Census, demographics, and risk level analysis') }}</flux:subheading>
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

        {{-- Distribution Cards --}}
        <div class="grid gap-4 md:grid-cols-2">
            {{-- Fall Risk Distribution --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Fall Risk Distribution') }}</flux:heading>
                <flux:separator />
                @if(count($this->fallRiskDistribution) > 0)
                    <div class="space-y-3">
                        @foreach($this->fallRiskDistribution as $level => $count)
                            <div class="flex items-center justify-between">
                                <flux:badge size="sm" :color="match($level) { 'low' => 'green', 'moderate' => 'amber', 'high' => 'red', default => 'zinc' }">
                                    {{ ucfirst($level) }}
                                </flux:badge>
                                <flux:text class="font-medium">{{ $count }} {{ __('residents') }}</flux:text>
                            </div>
                        @endforeach
                    </div>
                @else
                    <flux:text class="text-zinc-500">{{ __('No fall risk data recorded') }}</flux:text>
                @endif
            </flux:card>

            {{-- Mobility Distribution --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Mobility Status') }}</flux:heading>
                <flux:separator />
                @if(count($this->mobilityDistribution) > 0)
                    <div class="space-y-3">
                        @foreach($this->mobilityDistribution as $status => $count)
                            <div class="flex items-center justify-between">
                                <flux:badge size="sm" :color="match($status) { 'independent' => 'green', 'walker' => 'sky', 'wheelchair' => 'amber', 'bedridden' => 'red', default => 'zinc' }">
                                    {{ ucfirst($status) }}
                                </flux:badge>
                                <flux:text class="font-medium">{{ $count }} {{ __('residents') }}</flux:text>
                            </div>
                        @endforeach
                    </div>
                @else
                    <flux:text class="text-zinc-500">{{ __('No mobility data recorded') }}</flux:text>
                @endif
            </flux:card>
        </div>

        {{-- Admission Trends --}}
        <flux:card class="space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <flux:heading size="sm">{{ __('Admission Trends') }}</flux:heading>
                <div class="flex gap-2">
                    <flux:input wire:model.live="dateFrom" type="date" size="sm" class="max-w-[150px]" />
                    <flux:input wire:model.live="dateTo" type="date" size="sm" class="max-w-[150px]" />
                </div>
            </div>
            <flux:separator />
            @if(count($this->admissionTrends) > 0)
                <div class="space-y-2">
                    @foreach($this->admissionTrends as $month => $count)
                        <div class="flex items-center justify-between">
                            <flux:text>{{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}</flux:text>
                            <div class="flex items-center gap-3">
                                <div class="h-2 w-32 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                                    <div class="h-2 rounded-full bg-blue-500" style="width: {{ min($count * 10, 100) }}%"></div>
                                </div>
                                <flux:text class="w-8 text-right font-medium">{{ $count }}</flux:text>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <flux:text class="text-zinc-500">{{ __('No admission data for selected period') }}</flux:text>
            @endif
        </flux:card>

        {{-- Active Residents Table --}}
        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('Active Residents') }}</flux:heading>
            <flux:separator />
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                    <flux:table.column>{{ __('Age') }}</flux:table.column>
                    <flux:table.column>{{ __('Room') }}</flux:table.column>
                    <flux:table.column>{{ __('Gender') }}</flux:table.column>
                    <flux:table.column>{{ __('Mobility') }}</flux:table.column>
                    <flux:table.column>{{ __('Fall Risk') }}</flux:table.column>
                    <flux:table.column>{{ __('DNR') }}</flux:table.column>
                    <flux:table.column>{{ __('Care Plans') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse($this->residents as $resident)
                        <flux:table.row :key="$resident->id">
                            <flux:table.cell>
                                <flux:link :href="route('residents.show', $resident)" wire:navigate class="font-medium">
                                    {{ $resident->full_name }}
                                </flux:link>
                            </flux:table.cell>
                            <flux:table.cell>{{ $resident->age ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $resident->room_number ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ ucfirst($resident->gender ?? '-') }}</flux:table.cell>
                            <flux:table.cell>
                                @if($resident->mobility_status)
                                    <flux:badge size="sm" :color="match($resident->mobility_status) { 'independent' => 'green', 'walker' => 'sky', 'wheelchair' => 'amber', 'bedridden' => 'red', default => 'zinc' }">
                                        {{ ucfirst($resident->mobility_status) }}
                                    </flux:badge>
                                @else
                                    -
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($resident->fall_risk_level)
                                    <flux:badge size="sm" :color="match($resident->fall_risk_level) { 'low' => 'green', 'moderate' => 'amber', 'high' => 'red', default => 'zinc' }">
                                        {{ ucfirst($resident->fall_risk_level) }}
                                    </flux:badge>
                                @else
                                    -
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($resident->dnr_status)
                                    <flux:badge size="sm" color="red">{{ __('DNR') }}</flux:badge>
                                @else
                                    <flux:badge size="sm" color="zinc">{{ __('No') }}</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>{{ $resident->care_plans_count }}</flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="8" class="py-8 text-center">
                                <x-dashboard.empty-state
                                    title="No active residents"
                                    description="There are no active residents in the system."
                                    icon="user-group"
                                />
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>
</flux:main>
