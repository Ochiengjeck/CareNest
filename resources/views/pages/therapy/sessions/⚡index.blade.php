<?php

use App\Models\TherapySession;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new
#[Layout('layouts.app.sidebar')]
#[Title('Therapy Sessions')]
class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $therapistFilter = '';

    #[Url]
    public string $serviceTypeFilter = '';

    #[Url]
    public string $statusFilter = '';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    #[Computed]
    public function sessions()
    {
        $query = TherapySession::query()
            ->with(['therapist', 'resident']);

        // If user can only conduct therapy (therapist), show only their sessions
        if (auth()->user()->can('conduct-therapy') && !auth()->user()->can('manage-therapy')) {
            $query->forTherapist(auth()->id());
        }

        return $query
            ->when($this->search, fn ($q) => $q->whereHas('resident', fn ($r) => $r
                ->where('first_name', 'like', "%{$this->search}%")
                ->orWhere('last_name', 'like', "%{$this->search}%")
            )->orWhere('session_topic', 'like', "%{$this->search}%"))
            ->when($this->therapistFilter, fn ($q) => $q->forTherapist((int) $this->therapistFilter))
            ->when($this->serviceTypeFilter, fn ($q) => $q->byServiceType($this->serviceTypeFilter))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->dateFrom || $this->dateTo, fn ($q) => $q->inDateRange($this->dateFrom, $this->dateTo))
            ->latest('session_date')
            ->latest('start_time')
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

    #[Computed]
    public function serviceTypes(): array
    {
        return [
            'individual' => 'Individual Note',
            'group' => 'Group',
            'intake_assessment' => 'Intake/Assessment',
            'crisis' => 'Crisis',
            'collateral' => 'Collateral',
            'case_management' => 'Case Management',
            'treatment_planning' => 'Treatment Planning',
            'discharge' => 'Discharge',
            'other' => 'Other',
        ];
    }

    #[Computed]
    public function statuses(): array
    {
        return [
            'scheduled' => 'Scheduled',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show',
        ];
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->therapistFilter = '';
        $this->serviceTypeFilter = '';
        $this->statusFilter = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    public function hasActiveFilters(): bool
    {
        return $this->search !== ''
            || $this->therapistFilter !== ''
            || $this->serviceTypeFilter !== ''
            || $this->statusFilter !== ''
            || $this->dateFrom !== ''
            || $this->dateTo !== '';
    }

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedTherapistFilter(): void { $this->resetPage(); }
    public function updatedServiceTypeFilter(): void { $this->resetPage(); }
    public function updatedStatusFilter(): void { $this->resetPage(); }
    public function updatedDateFrom(): void { $this->resetPage(); }
    public function updatedDateTo(): void { $this->resetPage(); }
}; ?>

<flux:main>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Therapy Sessions') }}</flux:heading>
                <flux:subheading>{{ __('View and manage therapy sessions') }}</flux:subheading>
            </div>

            @can('conduct-therapy')
            <flux:button variant="primary" :href="route('therapy.sessions.create')" wire:navigate icon="plus">
                {{ __('New Session') }}
            </flux:button>
            @endcan
        </div>

        {{-- Filters --}}
        <div class="flex flex-col gap-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search resident or topic..."
                    icon="magnifying-glass"
                    class="sm:max-w-xs"
                />

                @can('manage-therapy')
                <flux:select wire:model.live="therapistFilter" class="sm:max-w-xs">
                    <flux:select.option value="">{{ __('All Therapists') }}</flux:select.option>
                    @foreach($this->therapists as $id => $name)
                        <flux:select.option value="{{ $id }}">{{ $name }}</flux:select.option>
                    @endforeach
                </flux:select>
                @endcan

                <flux:select wire:model.live="serviceTypeFilter" class="sm:max-w-xs">
                    <flux:select.option value="">{{ __('All Service Types') }}</flux:select.option>
                    @foreach($this->serviceTypes as $value => $label)
                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="statusFilter" class="sm:max-w-xs">
                    <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                    @foreach($this->statuses as $value => $label)
                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <flux:input
                    wire:model.live="dateFrom"
                    type="date"
                    label="From"
                    class="sm:max-w-xs"
                />

                <flux:input
                    wire:model.live="dateTo"
                    type="date"
                    label="To"
                    class="sm:max-w-xs"
                />

                @if($this->hasActiveFilters())
                    <flux:button variant="ghost" wire:click="clearFilters" icon="x-mark">
                        {{ __('Clear Filters') }}
                    </flux:button>
                @endif
            </div>
        </div>

        {{-- Sessions Table --}}
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Date & Time') }}</flux:table.column>
                @can('manage-therapy')
                <flux:table.column>{{ __('Therapist') }}</flux:table.column>
                @endcan
                <flux:table.column>{{ __('Resident') }}</flux:table.column>
                <flux:table.column>{{ __('Topic') }}</flux:table.column>
                <flux:table.column>{{ __('Type') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column class="w-24"></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($this->sessions as $session)
                    <flux:table.row :key="$session->id">
                        <flux:table.cell>
                            <div class="text-sm font-medium">{{ $session->session_date->format('M d, Y') }}</div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $session->formatted_time_range }}</div>
                        </flux:table.cell>
                        @can('manage-therapy')
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:avatar size="xs" :name="$session->therapist->name" />
                                <span class="text-sm">{{ $session->therapist->name }}</span>
                            </div>
                        </flux:table.cell>
                        @endcan
                        <flux:table.cell>
                            <div class="font-medium">{{ $session->resident->full_name }}</div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ __('Room :room', ['room' => $session->resident->room_number ?? 'N/A']) }}
                            </div>
                        </flux:table.cell>
                        <flux:table.cell class="max-w-xs truncate">
                            <span title="{{ $session->session_topic }}">{{ Str::limit($session->session_topic, 30) }}</span>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$session->service_type_color">
                                {{ $session->service_type_label }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$session->status_color">
                                {{ $session->status_label }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                <flux:menu>
                                    <flux:menu.item :href="route('therapy.sessions.show', $session)" wire:navigate icon="eye">
                                        {{ __('View') }}
                                    </flux:menu.item>
                                    @if($session->status === 'completed' && !$session->progress_notes && ($session->therapist_id === auth()->id() || auth()->user()->can('manage-therapy')))
                                        <flux:menu.item :href="route('therapy.sessions.document', $session)" wire:navigate icon="document-text">
                                            {{ __('Document') }}
                                        </flux:menu.item>
                                    @endif
                                    @can('manage-therapy')
                                    <flux:menu.item :href="route('therapy.sessions.edit', $session)" wire:navigate icon="pencil">
                                        {{ __('Edit') }}
                                    </flux:menu.item>
                                    @endcan
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="{{ auth()->user()->can('manage-therapy') ? 7 : 6 }}" class="text-center py-8">
                            <x-dashboard.empty-state
                                title="No sessions found"
                                description="Try adjusting your filters or create a new session"
                                icon="clipboard-document-list"
                            />
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="mt-6">
            {{ $this->sessions->links() }}
        </div>
    </div>
</flux:main>
