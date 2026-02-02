<?php

use App\Models\TherapistAssignment;
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
#[Title('Therapists')]
class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Computed]
    public function therapists()
    {
        return User::role('therapist')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(15);
    }

    public function getActiveAssignments(int $userId): int
    {
        return TherapistAssignment::forTherapist($userId)->active()->count();
    }

    public function getTotalSessions(int $userId): int
    {
        return TherapySession::forTherapist($userId)->count();
    }

    public function getCompletedThisMonth(int $userId): int
    {
        return TherapySession::forTherapist($userId)
            ->completed()
            ->whereMonth('session_date', now()->month)
            ->whereYear('session_date', now()->year)
            ->count();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }
}; ?>

<flux:main>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Therapists') }}</flux:heading>
                <flux:subheading>{{ __('Manage therapists and their caseloads') }}</flux:subheading>
            </div>

            <flux:button variant="primary" :href="route('therapy.assignments.create')" wire:navigate icon="plus">
                {{ __('New Assignment') }}
            </flux:button>
        </div>

        {{-- Search --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Search therapists..."
                icon="magnifying-glass"
                class="sm:max-w-xs"
            />
        </div>

        {{-- Therapists Grid --}}
        @if($this->therapists->isEmpty())
            <flux:card class="py-12">
                <div class="text-center">
                    <flux:icon name="users" class="mx-auto h-12 w-12 text-zinc-300 dark:text-zinc-600" />
                    <h3 class="mt-4 text-lg font-medium">{{ __('No therapists found') }}</h3>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Users with the therapist role will appear here.') }}
                    </p>
                </div>
            </flux:card>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($this->therapists as $therapist)
                    @php
                        $activeAssignments = $this->getActiveAssignments($therapist->id);
                        $totalSessions = $this->getTotalSessions($therapist->id);
                        $completedThisMonth = $this->getCompletedThisMonth($therapist->id);
                    @endphp
                    <flux:card>
                        <div class="flex items-start gap-4">
                            <flux:avatar size="lg" :name="$therapist->name" />
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold truncate">{{ $therapist->name }}</h3>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400 truncate">{{ $therapist->email }}</p>
                            </div>
                        </div>

                        <flux:separator class="my-4" />

                        <div class="grid grid-cols-3 gap-4 text-center text-sm">
                            <div>
                                <div class="text-2xl font-bold theme-accent-text">{{ $activeAssignments }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Residents') }}</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $completedThisMonth }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('This Month') }}</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-zinc-600 dark:text-zinc-400">{{ $totalSessions }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Total') }}</div>
                            </div>
                        </div>

                        <div class="mt-4 flex gap-2">
                            <flux:button variant="ghost" size="sm" :href="route('therapy.therapists.show', $therapist)" wire:navigate icon="eye" class="flex-1">
                                {{ __('View Details') }}
                            </flux:button>
                            <flux:button variant="ghost" size="sm" :href="route('therapy.assignments.create', ['therapist' => $therapist->id])" wire:navigate icon="plus">
                                {{ __('Assign') }}
                            </flux:button>
                        </div>
                    </flux:card>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $this->therapists->links() }}
            </div>
        @endif
    </div>
</flux:main>
