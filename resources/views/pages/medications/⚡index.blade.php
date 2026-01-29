<?php

use App\Models\Medication;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new
#[Layout('layouts.app.sidebar')]
#[Title('Medications')]
class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    #[Url]
    public string $routeFilter = '';

    #[Computed]
    public function medications()
    {
        return Medication::query()
            ->with('resident')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhereHas('resident', fn ($r) => $r->where('first_name', 'like', "%{$this->search}%")
                    ->orWhere('last_name', 'like', "%{$this->search}%")))
            ->when($this->statusFilter, fn ($q) => $q->byStatus($this->statusFilter))
            ->when($this->routeFilter, fn ($q) => $q->where('route', $this->routeFilter))
            ->latest()
            ->paginate(15);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedRouteFilter(): void
    {
        $this->resetPage();
    }
}; ?>

<flux:main>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Medications') }}</flux:heading>
                <flux:subheading>{{ __('Manage prescriptions and medication records') }}</flux:subheading>
            </div>

            @can('manage-medications')
                <flux:button variant="primary" :href="route('medications.create')" wire:navigate icon="plus">
                    {{ __('Add Medication') }}
                </flux:button>
            @endcan
        </div>

        <div class="flex flex-1 flex-wrap gap-4">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Search by medication or resident..."
                icon="magnifying-glass"
                class="max-w-xs"
            />

            <flux:select wire:model.live="statusFilter" class="max-w-[160px]">
                <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                <flux:select.option value="on_hold">{{ __('On Hold') }}</flux:select.option>
                <flux:select.option value="completed">{{ __('Completed') }}</flux:select.option>
                <flux:select.option value="discontinued">{{ __('Discontinued') }}</flux:select.option>
            </flux:select>

            <flux:select wire:model.live="routeFilter" class="max-w-[160px]">
                <flux:select.option value="">{{ __('All Routes') }}</flux:select.option>
                <flux:select.option value="oral">{{ __('Oral') }}</flux:select.option>
                <flux:select.option value="topical">{{ __('Topical') }}</flux:select.option>
                <flux:select.option value="injection">{{ __('Injection') }}</flux:select.option>
                <flux:select.option value="inhalation">{{ __('Inhalation') }}</flux:select.option>
                <flux:select.option value="sublingual">{{ __('Sublingual') }}</flux:select.option>
                <flux:select.option value="rectal">{{ __('Rectal') }}</flux:select.option>
                <flux:select.option value="other">{{ __('Other') }}</flux:select.option>
            </flux:select>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Medication') }}</flux:table.column>
                <flux:table.column>{{ __('Resident') }}</flux:table.column>
                <flux:table.column>{{ __('Dosage') }}</flux:table.column>
                <flux:table.column>{{ __('Frequency') }}</flux:table.column>
                <flux:table.column>{{ __('Route') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column>{{ __('Start Date') }}</flux:table.column>
                <flux:table.column class="w-24"></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($this->medications as $medication)
                    <flux:table.row :key="$medication->id">
                        <flux:table.cell>
                            <flux:link :href="route('medications.show', $medication)" wire:navigate class="font-medium">
                                {{ $medication->name }}
                            </flux:link>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if($medication->resident)
                                <flux:link :href="route('residents.show', $medication->resident)" wire:navigate>
                                    {{ $medication->resident->full_name }}
                                </flux:link>
                            @else
                                <flux:text class="text-zinc-400">-</flux:text>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>{{ $medication->dosage }}</flux:table.cell>
                        <flux:table.cell>{{ $medication->frequency }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="zinc">{{ $medication->route_label }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$medication->status_color">
                                {{ str_replace('_', ' ', ucfirst($medication->status)) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $medication->start_date->format('M d, Y') }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                <flux:menu>
                                    <flux:menu.item :href="route('medications.show', $medication)" wire:navigate icon="eye">
                                        {{ __('View') }}
                                    </flux:menu.item>
                                    @can('manage-medications')
                                        <flux:menu.item :href="route('medications.edit', $medication)" wire:navigate icon="pencil">
                                            {{ __('Edit') }}
                                        </flux:menu.item>
                                    @endcan
                                    @can('administer-medications')
                                        @if($medication->status === 'active')
                                            <flux:menu.item :href="route('medications.administer', $medication)" wire:navigate icon="clipboard-document-check">
                                                {{ __('Administer') }}
                                            </flux:menu.item>
                                        @endif
                                    @endcan
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="8" class="text-center py-8">
                            <x-dashboard.empty-state
                                title="No medications found"
                                description="Try adjusting your search or filters."
                                icon="beaker"
                            />
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="mt-6">
            {{ $this->medications->links() }}
        </div>
    </div>
</flux:main>
