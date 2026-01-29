<?php

use App\Models\Vital;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new
#[Layout('layouts.app.sidebar')]
#[Title('Vitals & Observations')]
class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    #[Computed]
    public function vitals()
    {
        return Vital::query()
            ->with(['resident', 'recordedBy'])
            ->when($this->search, fn ($q) => $q->whereHas('resident', fn ($r) => $r->where('first_name', 'like', "%{$this->search}%")
                ->orWhere('last_name', 'like', "%{$this->search}%")))
            ->when($this->dateFrom, fn ($q) => $q->where('recorded_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->where('recorded_at', '<=', $this->dateTo . ' 23:59:59'))
            ->latest('recorded_at')
            ->paginate(15);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }
}; ?>

<flux:main>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Vitals & Observations') }}</flux:heading>
                <flux:subheading>{{ __('Track and monitor resident vital signs') }}</flux:subheading>
            </div>

            <flux:button variant="primary" :href="route('vitals.create')" wire:navigate icon="plus">
                {{ __('Record Vitals') }}
            </flux:button>
        </div>

        <div class="flex flex-1 flex-wrap gap-4">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Search by resident..."
                icon="magnifying-glass"
                class="max-w-xs"
            />

            <flux:input wire:model.live="dateFrom" type="date" class="max-w-[160px]" />
            <flux:input wire:model.live="dateTo" type="date" class="max-w-[160px]" />
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Resident') }}</flux:table.column>
                <flux:table.column>{{ __('Recorded At') }}</flux:table.column>
                <flux:table.column>{{ __('BP') }}</flux:table.column>
                <flux:table.column>{{ __('HR') }}</flux:table.column>
                <flux:table.column>{{ __('Temp') }}</flux:table.column>
                <flux:table.column>{{ __('SpO2') }}</flux:table.column>
                <flux:table.column>{{ __('Recorded By') }}</flux:table.column>
                <flux:table.column class="w-24"></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($this->vitals as $vital)
                    <flux:table.row :key="$vital->id">
                        <flux:table.cell>
                            @if($vital->resident)
                                <flux:link :href="route('residents.show', $vital->resident)" wire:navigate>
                                    {{ $vital->resident->full_name }}
                                </flux:link>
                            @else
                                <flux:text class="text-zinc-400">-</flux:text>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>{{ $vital->recorded_at->format('M d, Y H:i') }}</flux:table.cell>
                        <flux:table.cell>
                            @if($vital->blood_pressure)
                                <span @class(['text-red-500 font-medium' => $vital->hasAbnormalValues() && ($vital->blood_pressure_systolic > 140 || $vital->blood_pressure_systolic < 90)])>
                                    {{ $vital->blood_pressure }}
                                </span>
                            @else
                                <flux:text class="text-zinc-400">-</flux:text>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @if($vital->heart_rate)
                                <span @class(['text-red-500 font-medium' => $vital->heart_rate > 100 || $vital->heart_rate < 60])>
                                    {{ $vital->heart_rate }}
                                </span>
                            @else
                                <flux:text class="text-zinc-400">-</flux:text>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @if($vital->temperature)
                                <span @class(['text-red-500 font-medium' => $vital->temperature > 37.8 || $vital->temperature < 36.0])>
                                    {{ $vital->temperature }}°C
                                </span>
                            @else
                                <flux:text class="text-zinc-400">-</flux:text>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @if($vital->oxygen_saturation)
                                <span @class(['text-red-500 font-medium' => $vital->oxygen_saturation < 95])>
                                    {{ $vital->oxygen_saturation }}%
                                </span>
                            @else
                                <flux:text class="text-zinc-400">-</flux:text>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>{{ $vital->recordedBy?->name ?? '-' }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:button variant="ghost" size="sm" :href="route('vitals.show', $vital)" wire:navigate icon="eye" />
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="8" class="text-center py-8">
                            <x-dashboard.empty-state
                                title="No vitals recorded"
                                description="Start recording vital signs for residents."
                                icon="heart"
                            />
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="mt-6">
            {{ $this->vitals->links() }}
        </div>
    </div>
</flux:main>
