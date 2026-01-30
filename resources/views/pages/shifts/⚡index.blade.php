<?php

use App\Models\Shift;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new
#[Layout('layouts.app.sidebar')]
#[Title('Shift Schedule')]
class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $dateFilter = '';

    #[Url]
    public string $typeFilter = '';

    #[Url]
    public string $statusFilter = '';

    #[Computed]
    public function shifts()
    {
        return Shift::query()
            ->with('user')
            ->when($this->search, fn ($q) => $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$this->search}%")))
            ->when($this->dateFilter, fn ($q) => $q->forDate($this->dateFilter))
            ->when($this->typeFilter, fn ($q) => $q->byType($this->typeFilter))
            ->when($this->statusFilter, fn ($q) => $q->byStatus($this->statusFilter))
            ->latest('shift_date')
            ->paginate(15);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedDateFilter(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
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
                <flux:heading size="xl">{{ __('Shift Schedule') }}</flux:heading>
                <flux:subheading>{{ __('Manage staff shift assignments') }}</flux:subheading>
            </div>

            <flux:button variant="primary" :href="route('shifts.create')" wire:navigate icon="plus">
                {{ __('Create Shift') }}
            </flux:button>
        </div>

        <div class="flex flex-1 flex-wrap gap-4">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Search by staff name..."
                icon="magnifying-glass"
                class="max-w-xs"
            />

            <flux:input
                wire:model.live="dateFilter"
                type="date"
                class="max-w-[180px]"
            />

            <flux:select wire:model.live="typeFilter" class="max-w-[160px]">
                <flux:select.option value="">{{ __('All Types') }}</flux:select.option>
                <flux:select.option value="morning">{{ __('Morning') }}</flux:select.option>
                <flux:select.option value="afternoon">{{ __('Afternoon') }}</flux:select.option>
                <flux:select.option value="night">{{ __('Night') }}</flux:select.option>
                <flux:select.option value="custom">{{ __('Custom') }}</flux:select.option>
            </flux:select>

            <flux:select wire:model.live="statusFilter" class="max-w-[180px]">
                <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                <flux:select.option value="scheduled">{{ __('Scheduled') }}</flux:select.option>
                <flux:select.option value="in_progress">{{ __('In Progress') }}</flux:select.option>
                <flux:select.option value="completed">{{ __('Completed') }}</flux:select.option>
                <flux:select.option value="cancelled">{{ __('Cancelled') }}</flux:select.option>
                <flux:select.option value="no_show">{{ __('No Show') }}</flux:select.option>
            </flux:select>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Staff Member') }}</flux:table.column>
                <flux:table.column>{{ __('Date') }}</flux:table.column>
                <flux:table.column>{{ __('Start') }}</flux:table.column>
                <flux:table.column>{{ __('End') }}</flux:table.column>
                <flux:table.column>{{ __('Type') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column class="w-24"></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($this->shifts as $shift)
                    <flux:table.row :key="$shift->id">
                        <flux:table.cell>
                            <div class="flex items-center gap-3">
                                <flux:avatar :name="$shift->user->name" size="sm" />
                                <flux:link :href="route('staff.show', $shift->user)" wire:navigate class="font-medium">
                                    {{ $shift->user->name }}
                                </flux:link>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>{{ $shift->shift_date->format('M d, Y') }}</flux:table.cell>
                        <flux:table.cell>{{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }}</flux:table.cell>
                        <flux:table.cell>{{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$shift->type_color">{{ $shift->type_label }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$shift->status_color">{{ $shift->status_label }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                <flux:menu>
                                    <flux:menu.item :href="route('shifts.show', $shift)" wire:navigate icon="eye">
                                        {{ __('View') }}
                                    </flux:menu.item>
                                    <flux:menu.item :href="route('shifts.edit', $shift)" wire:navigate icon="pencil">
                                        {{ __('Edit') }}
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center py-8">
                            <x-dashboard.empty-state
                                title="No shifts found"
                                description="Try adjusting your search or filters, or create a new shift."
                                icon="calendar"
                            />
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="mt-6">
            {{ $this->shifts->links() }}
        </div>
    </div>
</flux:main>
