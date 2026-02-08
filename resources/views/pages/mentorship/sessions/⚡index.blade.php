<?php

use App\Models\MentorshipSession;
use App\Models\MentorshipTopic;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new
#[Layout('layouts.mentorship')]
#[Title('My Sessions')]
class extends Component {

    #[Url]
    public string $statusFilter = '';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    #[Computed]
    public function stats(): array
    {
        $userId = auth()->id();
        $sessions = MentorshipSession::forMentor($userId);
        $completed = (clone $sessions)->completed();

        return [
            'sessions_conducted' => $completed->count(),
            'total_participants' => $completed->sum('participant_count'),
            'topics_covered' => $completed->distinct('topic_id')->count('topic_id'),
        ];
    }

    #[Computed]
    public function sessions()
    {
        $query = MentorshipSession::with(['topic', 'lesson'])
            ->forMentor(auth()->id())
            ->orderByDesc('session_date')
            ->orderByDesc('start_time');

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->dateFrom) {
            $query->where('session_date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->where('session_date', '<=', $this->dateTo);
        }

        return $query->get();
    }

    public function resetFilters(): void
    {
        $this->statusFilter = '';
        $this->dateFrom = '';
        $this->dateTo = '';
    }
}; ?>

<flux:main>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('My Sessions') }}</flux:heading>
                <flux:subheading>{{ __('View and manage your teaching sessions') }}</flux:subheading>
            </div>

            <flux:button variant="primary" :href="route('mentorship.sessions.start')" wire:navigate icon="plus">
                {{ __('Start New Session') }}
            </flux:button>
        </div>

        {{-- Stats Cards --}}
        <div class="grid gap-4 sm:grid-cols-3">
            <flux:card class="text-center">
                <div class="text-3xl font-bold text-green-600">{{ $this->stats['sessions_conducted'] }}</div>
                <flux:subheading>{{ __('Sessions Conducted') }}</flux:subheading>
            </flux:card>

            <flux:card class="text-center">
                <div class="text-3xl font-bold text-blue-600">{{ $this->stats['total_participants'] }}</div>
                <flux:subheading>{{ __('Total Participants') }}</flux:subheading>
            </flux:card>

            <flux:card class="text-center">
                <div class="text-3xl font-bold text-purple-600">{{ $this->stats['topics_covered'] }}</div>
                <flux:subheading>{{ __('Topics Covered') }}</flux:subheading>
            </flux:card>
        </div>

        {{-- Filters --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
            <flux:select wire:model.live="statusFilter" :label="__('Status')" class="sm:max-w-xs">
                <option value="">{{ __('All Statuses') }}</option>
                <option value="planned">{{ __('Planned') }}</option>
                <option value="in_progress">{{ __('In Progress') }}</option>
                <option value="completed">{{ __('Completed') }}</option>
                <option value="cancelled">{{ __('Cancelled') }}</option>
            </flux:select>

            <flux:input type="date" wire:model.live="dateFrom" :label="__('From Date')" class="sm:max-w-xs" />
            <flux:input type="date" wire:model.live="dateTo" :label="__('To Date')" class="sm:max-w-xs" />

            @if($statusFilter || $dateFrom || $dateTo)
                <flux:button variant="ghost" wire:click="resetFilters" icon="x-mark">
                    {{ __('Clear') }}
                </flux:button>
            @endif
        </div>

        {{-- Sessions List --}}
        @if($this->sessions->isNotEmpty())
            <flux:card>
                <div class="space-y-3">
                    @foreach($this->sessions as $session)
                        <a href="{{ route('mentorship.sessions.show', $session) }}" wire:navigate
                           class="flex items-center gap-4 p-4 rounded-lg bg-zinc-50 dark:bg-zinc-800 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">
                            <div class="flex-shrink-0 text-center min-w-[70px]">
                                <div class="text-lg font-bold">{{ $session->session_date->format('M d') }}</div>
                                <div class="text-xs text-zinc-500">{{ $session->session_date->format('Y') }}</div>
                                @if($session->start_time)
                                    <div class="text-xs text-zinc-500 mt-1">{{ $session->start_time->format('g:i A') }}</div>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <flux:badge :color="$session->status_color" size="sm">{{ $session->status_label }}</flux:badge>
                                    @if($session->topic)
                                        <flux:badge :color="$session->topic->category_color" size="sm">{{ $session->topic->category }}</flux:badge>
                                    @endif
                                </div>
                                <h4 class="font-semibold truncate">{{ $session->topic?->title ?? 'Unknown Topic' }}</h4>
                                @if($session->lesson)
                                    <p class="text-sm text-zinc-500 truncate">{{ __('Lesson') }}: {{ $session->lesson->title }}</p>
                                @endif
                            </div>

                            <div class="text-right text-sm">
                                @if($session->status === 'completed')
                                    <div class="font-semibold text-green-600">
                                        {{ $session->participant_count }} {{ Str::plural('participant', $session->participant_count) }}
                                    </div>
                                    @if($session->formatted_duration)
                                        <div class="text-zinc-500">{{ $session->formatted_duration }}</div>
                                    @endif
                                @elseif($session->status === 'in_progress')
                                    <flux:badge color="amber">{{ __('Ongoing') }}</flux:badge>
                                @endif
                            </div>

                            <flux:icon.chevron-right class="size-5 text-zinc-400" />
                        </a>
                    @endforeach
                </div>
            </flux:card>
        @else
            <flux:card class="text-center py-12">
                <flux:icon.presentation-chart-bar class="mx-auto size-12 text-zinc-400" />
                <flux:heading size="lg" class="mt-4">{{ __('No Sessions Found') }}</flux:heading>
                <flux:subheading>
                    @if($statusFilter || $dateFrom || $dateTo)
                        {{ __('Try adjusting your filters or start a new session.') }}
                    @else
                        {{ __('Start your first teaching session to see it here.') }}
                    @endif
                </flux:subheading>
                <flux:button variant="primary" :href="route('mentorship.sessions.start')" wire:navigate class="mt-4" icon="plus">
                    {{ __('Start New Session') }}
                </flux:button>
            </flux:card>
        @endif
    </div>
</flux:main>
