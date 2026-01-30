<?php

use App\Models\TherapistAssignment;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new
#[Layout('layouts.app.sidebar')]
#[Title('Therapist Assignments')]
class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $therapistFilter = '';

    #[Url]
    public string $statusFilter = '';

    public function mount(): void
    {
        if (request()->has('therapist')) {
            $this->therapistFilter = (string) request()->get('therapist');
        }
    }

    #[Computed]
    public function assignments()
    {
        return TherapistAssignment::query()
            ->with(['therapist', 'resident', 'assignedBy'])
            ->when($this->search, fn ($q) => $q->whereHas('resident', fn ($r) => $r
                ->where('first_name', 'like', "%{$this->search}%")
                ->orWhere('last_name', 'like', "%{$this->search}%")
            ))
            ->when($this->therapistFilter, fn ($q) => $q->forTherapist((int) $this->therapistFilter))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest('assigned_date')
            ->paginate(20);
    }

    #[Computed]
    public function therapists(): array
    {
        return User::role('therapist')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->therapistFilter = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function hasActiveFilters(): bool
    {
        return $this->search !== '' || $this->therapistFilter !== '' || $this->statusFilter !== '';
    }

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedTherapistFilter(): void { $this->resetPage(); }
    public function updatedStatusFilter(): void { $this->resetPage(); }
}; ?>

<flux:main>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Therapist Assignments') }}</flux:heading>
                <flux:subheading>{{ __('Manage therapist-resident assignments') }}</flux:subheading>
            </div>

            <flux:button variant="primary" :href="route('therapy.assignments.create')" wire:navigate icon="plus">
                {{ __('New Assignment') }}
            </flux:button>
        </div>

        {{-- Filters --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Search resident..."
                icon="magnifying-glass"
                class="sm:max-w-xs"
            />

            <flux:select wire:model.live="therapistFilter" class="sm:max-w-xs">
                <flux:select.option value="">{{ __('All Therapists') }}</flux:select.option>
                @foreach($this->therapists as $id => $name)
                    <flux:select.option value="{{ $id }}">{{ $name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="statusFilter" class="sm:max-w-xs">
                <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                <flux:select.option value="inactive">{{ __('Inactive') }}</flux:select.option>
                <flux:select.option value="completed">{{ __('Completed') }}</flux:select.option>
            </flux:select>

            @if($this->hasActiveFilters())
                <flux:button variant="ghost" wire:click="clearFilters" icon="x-mark">
                    {{ __('Clear') }}
                </flux:button>
            @endif
        </div>

        {{-- Assignments Table --}}
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Therapist') }}</flux:table.column>
                <flux:table.column>{{ __('Resident') }}</flux:table.column>
                <flux:table.column>{{ __('Assigned Date') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column>{{ __('Assigned By') }}</flux:table.column>
                <flux:table.column class="w-24"></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($this->assignments as $assignment)
                    <flux:table.row :key="$assignment->id">
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:avatar size="xs" :name="$assignment->therapist->name" />
                                <span>{{ $assignment->therapist->name }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="font-medium">{{ $assignment->resident->full_name }}</div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ __('Room :room', ['room' => $assignment->resident->room_number ?? 'N/A']) }}
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>{{ $assignment->assigned_date->format('M d, Y') }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$assignment->status_color">
                                {{ $assignment->status_label }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $assignment->assignedBy?->name ?? '-' }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                <flux:menu>
                                    <flux:menu.item :href="route('therapy.assignments.edit', $assignment)" wire:navigate icon="pencil">
                                        {{ __('Edit') }}
                                    </flux:menu.item>
                                    <flux:menu.item :href="route('therapy.sessions.create', ['resident' => $assignment->resident_id])" wire:navigate icon="plus">
                                        {{ __('New Session') }}
                                    </flux:menu.item>
                                    <flux:menu.item :href="route('residents.show', $assignment->resident)" wire:navigate icon="eye">
                                        {{ __('View Resident') }}
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center py-8">
                            <x-dashboard.empty-state
                                title="No assignments found"
                                description="Create a new assignment to get started"
                                icon="arrows-right-left"
                            />
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="mt-6">
            {{ $this->assignments->links() }}
        </div>
    </div>
</flux:main>
