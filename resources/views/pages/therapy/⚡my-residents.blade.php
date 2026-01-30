<?php

use App\Models\TherapistAssignment;
use App\Models\TherapySession;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new
#[Layout('layouts.app.sidebar')]
#[Title('My Residents')]
class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = 'active';

    #[Computed]
    public function assignments()
    {
        return TherapistAssignment::query()
            ->forTherapist(auth()->id())
            ->with(['resident', 'assignedBy'])
            ->when($this->search, fn ($q) => $q->whereHas('resident', fn ($r) => $r
                ->where('first_name', 'like', "%{$this->search}%")
                ->orWhere('last_name', 'like', "%{$this->search}%")
                ->orWhere('room_number', 'like', "%{$this->search}%")
            ))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest('assigned_date')
            ->paginate(15);
    }

    public function getSessionCount(int $residentId): int
    {
        return TherapySession::query()
            ->forTherapist(auth()->id())
            ->forResident($residentId)
            ->completed()
            ->count();
    }

    public function getLastSession(int $residentId): ?TherapySession
    {
        return TherapySession::query()
            ->forTherapist(auth()->id())
            ->forResident($residentId)
            ->completed()
            ->latest('session_date')
            ->first();
    }

    public function updatedSearch(): void
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
                <flux:heading size="xl">{{ __('My Residents') }}</flux:heading>
                <flux:subheading>{{ __('Residents assigned to you for therapy') }}</flux:subheading>
            </div>

            <flux:button variant="primary" :href="route('therapy.sessions.create')" wire:navigate icon="plus">
                {{ __('New Session') }}
            </flux:button>
        </div>

        {{-- Filters --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Search residents..."
                icon="magnifying-glass"
                class="sm:max-w-xs"
            />

            <flux:select wire:model.live="statusFilter" class="sm:max-w-xs">
                <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                <flux:select.option value="inactive">{{ __('Inactive') }}</flux:select.option>
                <flux:select.option value="completed">{{ __('Completed') }}</flux:select.option>
            </flux:select>
        </div>

        {{-- Residents Grid --}}
        @if($this->assignments->isEmpty())
            <flux:card class="py-12">
                <div class="text-center">
                    <flux:icon name="users" class="mx-auto h-12 w-12 text-zinc-300 dark:text-zinc-600" />
                    <h3 class="mt-4 text-lg font-medium">{{ __('No assigned residents') }}</h3>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('You don\'t have any residents assigned yet.') }}
                    </p>
                </div>
            </flux:card>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($this->assignments as $assignment)
                    @php
                        $sessionCount = $this->getSessionCount($assignment->resident_id);
                        $lastSession = $this->getLastSession($assignment->resident_id);
                    @endphp
                    <flux:card class="relative">
                        <div class="absolute top-4 right-4">
                            <flux:badge size="sm" :color="$assignment->status_color">
                                {{ $assignment->status_label }}
                            </flux:badge>
                        </div>

                        <div class="flex items-start gap-4">
                            <flux:avatar size="lg" :name="$assignment->resident->full_name" />
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold truncate">{{ $assignment->resident->full_name }}</h3>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('Room :room', ['room' => $assignment->resident->room_number ?? 'N/A']) }}
                                </p>
                                @if($assignment->resident->date_of_birth)
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ __(':age years old', ['age' => $assignment->resident->age]) }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <flux:separator class="my-4" />

                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <div class="text-zinc-500 dark:text-zinc-400">{{ __('Sessions') }}</div>
                                <div class="font-medium">{{ $sessionCount }}</div>
                            </div>
                            <div>
                                <div class="text-zinc-500 dark:text-zinc-400">{{ __('Last Session') }}</div>
                                <div class="font-medium">
                                    {{ $lastSession ? $lastSession->session_date->diffForHumans() : __('Never') }}
                                </div>
                            </div>
                            <div>
                                <div class="text-zinc-500 dark:text-zinc-400">{{ __('Assigned') }}</div>
                                <div class="font-medium">{{ $assignment->assigned_date->format('M d, Y') }}</div>
                            </div>
                            <div>
                                <div class="text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</div>
                                <div class="font-medium">{{ $assignment->resident->status ?? 'N/A' }}</div>
                            </div>
                        </div>

                        @if($assignment->notes)
                            <div class="mt-4 text-sm text-zinc-600 dark:text-zinc-400 bg-zinc-50 dark:bg-zinc-800 rounded p-2">
                                {{ Str::limit($assignment->notes, 100) }}
                            </div>
                        @endif

                        <div class="mt-4 flex gap-2">
                            <flux:button variant="primary" size="sm" :href="route('therapy.sessions.create', ['resident' => $assignment->resident_id])" wire:navigate icon="plus" class="flex-1">
                                {{ __('New Session') }}
                            </flux:button>
                            <flux:button variant="ghost" size="sm" :href="route('residents.show', $assignment->resident)" wire:navigate icon="eye" />
                        </div>
                    </flux:card>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $this->assignments->links() }}
            </div>
        @endif
    </div>
</flux:main>
