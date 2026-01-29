<?php

use App\Models\Incident;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new
#[Layout('layouts.app.sidebar')]
#[Title('Incidents')]
class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $typeFilter = '';

    #[Url]
    public string $severityFilter = '';

    #[Url]
    public string $statusFilter = '';

    #[Computed]
    public function incidents()
    {
        return Incident::query()
            ->with(['resident', 'reporter'])
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%")
                ->orWhereHas('resident', fn ($r) => $r->where('first_name', 'like', "%{$this->search}%")
                    ->orWhere('last_name', 'like', "%{$this->search}%")))
            ->when($this->typeFilter, fn ($q) => $q->byType($this->typeFilter))
            ->when($this->severityFilter, fn ($q) => $q->bySeverity($this->severityFilter))
            ->when($this->statusFilter, fn ($q) => $q->byStatus($this->statusFilter))
            ->latest('occurred_at')
            ->paginate(15);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSeverityFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }
}; ?>

<flux:main>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Incidents') }}</flux:heading>
                <flux:subheading>{{ __('Track and manage incident reports') }}</flux:subheading>
            </div>

            @can('report-incidents')
                <flux:button variant="primary" :href="route('incidents.create')" wire:navigate icon="plus">
                    {{ __('Report Incident') }}
                </flux:button>
            @endcan
        </div>

        <div class="flex flex-1 flex-wrap gap-4">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Search by title or resident..."
                icon="magnifying-glass"
                class="max-w-xs"
            />

            <flux:select wire:model.live="typeFilter" class="max-w-[180px]">
                <flux:select.option value="">{{ __('All Types') }}</flux:select.option>
                <flux:select.option value="fall">{{ __('Fall') }}</flux:select.option>
                <flux:select.option value="medication_error">{{ __('Medication Error') }}</flux:select.option>
                <flux:select.option value="injury">{{ __('Injury') }}</flux:select.option>
                <flux:select.option value="behavioral">{{ __('Behavioral') }}</flux:select.option>
                <flux:select.option value="equipment_failure">{{ __('Equipment Failure') }}</flux:select.option>
                <flux:select.option value="other">{{ __('Other') }}</flux:select.option>
            </flux:select>

            <flux:select wire:model.live="severityFilter" class="max-w-[160px]">
                <flux:select.option value="">{{ __('All Severities') }}</flux:select.option>
                <flux:select.option value="minor">{{ __('Minor') }}</flux:select.option>
                <flux:select.option value="moderate">{{ __('Moderate') }}</flux:select.option>
                <flux:select.option value="major">{{ __('Major') }}</flux:select.option>
                <flux:select.option value="critical">{{ __('Critical') }}</flux:select.option>
            </flux:select>

            <flux:select wire:model.live="statusFilter" class="max-w-[180px]">
                <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                <flux:select.option value="open">{{ __('Open') }}</flux:select.option>
                <flux:select.option value="under_investigation">{{ __('Under Investigation') }}</flux:select.option>
                <flux:select.option value="resolved">{{ __('Resolved') }}</flux:select.option>
                <flux:select.option value="closed">{{ __('Closed') }}</flux:select.option>
            </flux:select>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Title') }}</flux:table.column>
                <flux:table.column>{{ __('Resident') }}</flux:table.column>
                <flux:table.column>{{ __('Type') }}</flux:table.column>
                <flux:table.column>{{ __('Severity') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column>{{ __('Occurred At') }}</flux:table.column>
                <flux:table.column>{{ __('Reported By') }}</flux:table.column>
                <flux:table.column class="w-24"></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($this->incidents as $incident)
                    <flux:table.row :key="$incident->id">
                        <flux:table.cell>
                            <flux:link :href="route('incidents.show', $incident)" wire:navigate class="font-medium">
                                {{ $incident->title }}
                            </flux:link>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if($incident->resident)
                                <flux:link :href="route('residents.show', $incident->resident)" wire:navigate>
                                    {{ $incident->resident->full_name }}
                                </flux:link>
                            @else
                                <flux:text class="text-zinc-400">{{ __('N/A') }}</flux:text>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="zinc">{{ $incident->type_label }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$incident->severity_color">
                                {{ ucfirst($incident->severity) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$incident->status_color">
                                {{ str_replace('_', ' ', ucfirst($incident->status)) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $incident->occurred_at->format('M d, Y H:i') }}</flux:table.cell>
                        <flux:table.cell>{{ $incident->reporter?->name ?? '-' }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                <flux:menu>
                                    @can('manage-incidents')
                                        <flux:menu.item :href="route('incidents.show', $incident)" wire:navigate icon="eye">
                                            {{ __('View') }}
                                        </flux:menu.item>
                                        <flux:menu.item :href="route('incidents.edit', $incident)" wire:navigate icon="pencil">
                                            {{ __('Edit') }}
                                        </flux:menu.item>
                                    @endcan
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="8" class="text-center py-8">
                            <x-dashboard.empty-state
                                title="No incidents found"
                                description="Try adjusting your search or filters."
                                icon="exclamation-triangle"
                            />
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="mt-6">
            {{ $this->incidents->links() }}
        </div>
    </div>
</flux:main>
