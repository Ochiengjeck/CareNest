<?php

use App\Models\TherapistAssignment;
use App\Models\TherapySession;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Therapist Details')]
class extends Component {
    #[Locked]
    public int $userId;

    public function mount(User $user): void
    {
        $this->userId = $user->id;
    }

    #[Computed]
    public function therapist(): User
    {
        return User::with('roles')->findOrFail($this->userId);
    }

    #[Computed]
    public function assignments()
    {
        return TherapistAssignment::query()
            ->forTherapist($this->userId)
            ->with('resident')
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->latest('assigned_date')
            ->get();
    }

    #[Computed]
    public function recentSessions()
    {
        return TherapySession::query()
            ->forTherapist($this->userId)
            ->with('resident')
            ->latest('session_date')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function stats(): array
    {
        $totalSessions = TherapySession::forTherapist($this->userId)->count();
        $completedSessions = TherapySession::forTherapist($this->userId)->completed()->count();
        $activeResidents = TherapistAssignment::forTherapist($this->userId)->active()->count();
        $thisMonthSessions = TherapySession::forTherapist($this->userId)
            ->completed()
            ->whereMonth('session_date', now()->month)
            ->whereYear('session_date', now()->year)
            ->count();

        return [
            'total_sessions' => $totalSessions,
            'completed_sessions' => $completedSessions,
            'active_residents' => $activeResidents,
            'this_month' => $thisMonthSessions,
        ];
    }
}; ?>

<flux:main>
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <flux:button variant="ghost" size="sm" :href="route('therapy.therapists.index')" wire:navigate icon="arrow-left">
                        {{ __('Back') }}
                    </flux:button>
                </div>
                <div class="flex items-center gap-4">
                    <flux:avatar size="xl" :name="$this->therapist->name" />
                    <div>
                        <flux:heading size="xl">{{ $this->therapist->name }}</flux:heading>
                        <flux:subheading>{{ $this->therapist->email }}</flux:subheading>
                    </div>
                </div>
            </div>

            <flux:button variant="primary" :href="route('therapy.assignments.create', ['therapist' => $this->therapist->id])" wire:navigate icon="plus">
                {{ __('Assign Resident') }}
            </flux:button>
        </div>

        {{-- Stats --}}
        <div class="grid gap-4 sm:grid-cols-4">
            <flux:card>
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $this->stats['active_residents'] }}</div>
                    <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Active Residents') }}</div>
                </div>
            </flux:card>
            <flux:card>
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $this->stats['this_month'] }}</div>
                    <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Sessions This Month') }}</div>
                </div>
            </flux:card>
            <flux:card>
                <div class="text-center">
                    <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ $this->stats['completed_sessions'] }}</div>
                    <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Completed Sessions') }}</div>
                </div>
            </flux:card>
            <flux:card>
                <div class="text-center">
                    <div class="text-3xl font-bold text-zinc-600 dark:text-zinc-400">{{ $this->stats['total_sessions'] }}</div>
                    <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Total Sessions') }}</div>
                </div>
            </flux:card>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Assigned Residents --}}
            <flux:card>
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="sm">{{ __('Assigned Residents') }}</flux:heading>
                    <flux:button variant="ghost" size="sm" :href="route('therapy.assignments.index', ['therapist' => $this->therapist->id])" wire:navigate>
                        {{ __('View All') }}
                    </flux:button>
                </div>

                @if($this->assignments->isEmpty())
                    <div class="py-8 text-center">
                        <flux:icon name="users" class="mx-auto h-12 w-12 text-zinc-300 dark:text-zinc-600" />
                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('No residents assigned') }}</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($this->assignments->take(5) as $assignment)
                            <div class="flex items-center justify-between rounded-lg border border-zinc-200 dark:border-zinc-700 p-3">
                                <div class="flex items-center gap-3">
                                    <flux:avatar size="sm" :name="$assignment->resident->full_name" />
                                    <div>
                                        <div class="font-medium">{{ $assignment->resident->full_name }}</div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ __('Room :room', ['room' => $assignment->resident->room_number ?? 'N/A']) }}
                                        </div>
                                    </div>
                                </div>
                                <flux:badge size="sm" :color="$assignment->status_color">{{ $assignment->status_label }}</flux:badge>
                            </div>
                        @endforeach
                    </div>
                @endif
            </flux:card>

            {{-- Recent Sessions --}}
            <flux:card>
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="sm">{{ __('Recent Sessions') }}</flux:heading>
                    <flux:button variant="ghost" size="sm" :href="route('therapy.sessions.index', ['therapist' => $this->therapist->id])" wire:navigate>
                        {{ __('View All') }}
                    </flux:button>
                </div>

                @if($this->recentSessions->isEmpty())
                    <div class="py-8 text-center">
                        <flux:icon name="clipboard-document-list" class="mx-auto h-12 w-12 text-zinc-300 dark:text-zinc-600" />
                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('No sessions yet') }}</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($this->recentSessions as $session)
                            <div class="flex items-center justify-between rounded-lg border border-zinc-200 dark:border-zinc-700 p-3">
                                <div>
                                    <div class="font-medium">{{ $session->resident->full_name }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ $session->session_date->format('M d, Y') }} &bull; {{ Str::limit($session->session_topic, 25) }}
                                    </div>
                                </div>
                                <flux:badge size="sm" :color="$session->status_color">{{ $session->status_label }}</flux:badge>
                            </div>
                        @endforeach
                    </div>
                @endif
            </flux:card>
        </div>

        {{-- Account Info --}}
        <flux:card>
            <flux:heading size="sm" class="mb-4">{{ __('Account Information') }}</flux:heading>

            <div class="grid gap-4 sm:grid-cols-3 text-sm">
                <div>
                    <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">{{ __('Roles') }}</div>
                    <div class="mt-1 flex flex-wrap gap-1">
                        @foreach($this->therapist->roles as $role)
                            <flux:badge size="sm" color="zinc">{{ str_replace('_', ' ', ucwords($role->name, '_')) }}</flux:badge>
                        @endforeach
                    </div>
                </div>
                <div>
                    <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">{{ __('Account Created') }}</div>
                    <div class="mt-1">{{ $this->therapist->created_at->format('M d, Y') }}</div>
                </div>
                <div>
                    <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">{{ __('Email Verified') }}</div>
                    <div class="mt-1">
                        @if($this->therapist->email_verified_at)
                            <span class="text-green-600 dark:text-green-400">{{ $this->therapist->email_verified_at->format('M d, Y') }}</span>
                        @else
                            <span class="text-amber-600 dark:text-amber-400">{{ __('Not verified') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </flux:card>
    </div>
</flux:main>
