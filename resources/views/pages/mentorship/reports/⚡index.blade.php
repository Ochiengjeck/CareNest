<?php

use App\Models\MentorshipSession;
use App\Models\MentorshipTopic;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

new
#[Layout('layouts.mentorship')]
#[Title('Mentorship Reports')]
class extends Component {

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    public function mount(): void
    {
        if (!$this->dateFrom) {
            $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        }
        if (!$this->dateTo) {
            $this->dateTo = now()->endOfMonth()->format('Y-m-d');
        }
    }

    #[Computed]
    public function overviewStats(): array
    {
        $query = MentorshipSession::query();

        if ($this->dateFrom) {
            $query->where('session_date', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->where('session_date', '<=', $this->dateTo);
        }

        $completed = (clone $query)->completed();
        $totalTopics = MentorshipTopic::published()->count();
        $taughtTopics = (clone $completed)->distinct('topic_id')->count('topic_id');

        return [
            'total_sessions' => (clone $query)->count(),
            'completed_sessions' => $completed->count(),
            'total_participants' => (clone $query)->completed()->sum('participant_count'),
            'total_topics' => $totalTopics,
            'topics_taught' => $taughtTopics,
            'coverage_pct' => $totalTopics > 0 ? round(($taughtTopics / $totalTopics) * 100) : 0,
            'avg_participants' => $completed->count() > 0
                ? round((clone $query)->completed()->avg('participant_count'), 1)
                : 0,
        ];
    }

    #[Computed]
    public function mostTaughtTopics()
    {
        $query = MentorshipSession::completed()
            ->select('topic_id', DB::raw('COUNT(*) as session_count'), DB::raw('SUM(participant_count) as total_participants'))
            ->groupBy('topic_id')
            ->orderByDesc('session_count')
            ->limit(10);

        if ($this->dateFrom) {
            $query->where('session_date', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->where('session_date', '<=', $this->dateTo);
        }

        return $query->get()->map(function ($item) {
            $topic = MentorshipTopic::withTrashed()->find($item->topic_id);
            return (object) [
                'title' => $topic?->title ?? 'Unknown',
                'category' => $topic?->category ?? 'Unknown',
                'category_color' => $topic?->category_color ?? 'zinc',
                'session_count' => $item->session_count,
                'total_participants' => $item->total_participants,
            ];
        });
    }

    #[Computed]
    public function sessionsByMentor()
    {
        $query = MentorshipSession::completed()
            ->select('mentor_id', DB::raw('COUNT(*) as session_count'), DB::raw('SUM(participant_count) as total_participants'))
            ->groupBy('mentor_id')
            ->orderByDesc('session_count')
            ->limit(20);

        if ($this->dateFrom) {
            $query->where('session_date', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->where('session_date', '<=', $this->dateTo);
        }

        return $query->get()->map(function ($item) {
            $mentor = User::find($item->mentor_id);
            return (object) [
                'name' => $mentor?->name ?? 'Unknown',
                'session_count' => $item->session_count,
                'total_participants' => $item->total_participants,
            ];
        });
    }

    #[Computed]
    public function sessionsByCategory()
    {
        $query = MentorshipSession::completed()
            ->join('mentorship_topics', 'mentorship_sessions.topic_id', '=', 'mentorship_topics.id')
            ->select('mentorship_topics.category', DB::raw('COUNT(*) as session_count'), DB::raw('SUM(mentorship_sessions.participant_count) as total_participants'))
            ->groupBy('mentorship_topics.category')
            ->orderByDesc('session_count');

        if ($this->dateFrom) {
            $query->where('mentorship_sessions.session_date', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->where('mentorship_sessions.session_date', '<=', $this->dateTo);
        }

        return $query->get();
    }

    public function getCategoryColor(string $category): string
    {
        return match ($category) {
            'Mental Health' => 'blue',
            'Substance Use Disorder' => 'purple',
            'Employment/Education' => 'green',
            'Physical Health' => 'red',
            'Financial/Housing' => 'amber',
            'Psycho-Social/Family' => 'cyan',
            'Spirituality' => 'rose',
            default => 'zinc',
        };
    }

    public function resetFilters(): void
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
    }
}; ?>

<flux:main>
    <div class="space-y-6">
        {{-- Header --}}
        <div>
            <flux:heading size="xl">{{ __('Mentorship Reports') }}</flux:heading>
            <flux:subheading>{{ __('Overview of teaching sessions and mentorship activity') }}</flux:subheading>
        </div>

        {{-- Date Range Filter --}}
        <flux:card>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                <flux:input type="date" wire:model.live="dateFrom" :label="__('From Date')" class="sm:max-w-xs" />
                <flux:input type="date" wire:model.live="dateTo" :label="__('To Date')" class="sm:max-w-xs" />

                <flux:button variant="ghost" wire:click="resetFilters" icon="arrow-path">
                    {{ __('Reset to This Month') }}
                </flux:button>
            </div>
        </flux:card>

        {{-- Overview Stats --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <flux:card>
                <div class="flex items-center gap-4">
                    <flux:icon.presentation-chart-bar class="size-10 text-blue-600 dark:text-blue-400" />
                    <div>
                        <div class="text-2xl font-bold">{{ $this->overviewStats['completed_sessions'] }}</div>
                        <div class="text-sm text-zinc-500">{{ __('Sessions Completed') }}</div>
                    </div>
                </div>
            </flux:card>

            <flux:card>
                <div class="flex items-center gap-4">
                    <flux:icon.user-group class="size-10 text-green-600 dark:text-green-400" />
                    <div>
                        <div class="text-2xl font-bold">{{ $this->overviewStats['total_participants'] }}</div>
                        <div class="text-sm text-zinc-500">{{ __('Total Participants') }}</div>
                    </div>
                </div>
            </flux:card>

            <flux:card>
                <div class="flex items-center gap-4">
                    <flux:icon.academic-cap class="size-10 text-purple-600 dark:text-purple-400" />
                    <div>
                        <div class="text-2xl font-bold">{{ $this->overviewStats['topics_taught'] }} / {{ $this->overviewStats['total_topics'] }}</div>
                        <div class="text-sm text-zinc-500">{{ __('Topics Covered') }}</div>
                    </div>
                </div>
            </flux:card>

            <flux:card>
                <div class="flex items-center gap-4">
                    <flux:icon.chart-bar class="size-10 text-amber-600 dark:text-amber-400" />
                    <div>
                        <div class="text-2xl font-bold">{{ $this->overviewStats['avg_participants'] }}</div>
                        <div class="text-sm text-zinc-500">{{ __('Avg. Participants') }}</div>
                    </div>
                </div>
            </flux:card>
        </div>

        {{-- Coverage Progress --}}
        <flux:card>
            <div class="flex items-center justify-between mb-2">
                <flux:heading size="lg">{{ __('Topic Coverage') }}</flux:heading>
                <span class="text-2xl font-bold text-purple-600">{{ $this->overviewStats['coverage_pct'] }}%</span>
            </div>
            <div class="w-full bg-zinc-200 rounded-full h-4 dark:bg-zinc-700">
                <div class="bg-gradient-to-r from-purple-500 to-indigo-500 h-4 rounded-full transition-all duration-500"
                     style="width: {{ $this->overviewStats['coverage_pct'] }}%"></div>
            </div>
            <p class="text-sm text-zinc-500 mt-2">
                {{ __(':taught of :total topics have been taught at least once', ['taught' => $this->overviewStats['topics_taught'], 'total' => $this->overviewStats['total_topics']]) }}
            </p>
        </flux:card>

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Sessions by Mentor --}}
            <flux:card>
                <flux:heading size="lg" class="mb-4">{{ __('Sessions by Mentor') }}</flux:heading>

                @if($this->sessionsByMentor->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($this->sessionsByMentor as $mentor)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800">
                                <div class="flex items-center gap-3">
                                    <flux:icon.user class="size-5 text-zinc-400" />
                                    <span class="font-medium">{{ $mentor->name }}</span>
                                </div>
                                <div class="flex items-center gap-4 text-sm">
                                    <span class="text-zinc-500">{{ $mentor->session_count }} {{ Str::plural('session', $mentor->session_count) }}</span>
                                    <flux:badge color="green" size="sm">{{ $mentor->total_participants }} {{ __('participants') }}</flux:badge>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center py-8 text-zinc-500">{{ __('No completed sessions in this date range.') }}</p>
                @endif
            </flux:card>

            {{-- Sessions by Category --}}
            <flux:card>
                <flux:heading size="lg" class="mb-4">{{ __('Sessions by Category') }}</flux:heading>

                @if($this->sessionsByCategory->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($this->sessionsByCategory as $cat)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800">
                                <flux:badge :color="$this->getCategoryColor($cat->category)" size="sm">{{ $cat->category }}</flux:badge>
                                <div class="flex items-center gap-4 text-sm">
                                    <span class="text-zinc-500">{{ $cat->session_count }} {{ Str::plural('session', $cat->session_count) }}</span>
                                    <span class="font-medium">{{ $cat->total_participants }} {{ __('participants') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center py-8 text-zinc-500">{{ __('No completed sessions in this date range.') }}</p>
                @endif
            </flux:card>
        </div>

        {{-- Most Taught Topics --}}
        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('Most Taught Topics') }}</flux:heading>

            @if($this->mostTaughtTopics->isNotEmpty())
                <div class="space-y-3">
                    @foreach($this->mostTaughtTopics as $index => $topic)
                        <div class="flex items-center gap-4 p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800">
                            <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 font-bold text-sm">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-medium truncate">{{ $topic->title }}</h4>
                                <flux:badge :color="$topic->category_color" size="sm">{{ $topic->category }}</flux:badge>
                            </div>
                            <div class="flex items-center gap-4 text-sm text-right">
                                <div>
                                    <div class="font-medium">{{ $topic->session_count }}</div>
                                    <div class="text-zinc-500">{{ Str::plural('session', $topic->session_count) }}</div>
                                </div>
                                <div>
                                    <div class="font-medium">{{ $topic->total_participants }}</div>
                                    <div class="text-zinc-500">{{ __('participants') }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center py-8 text-zinc-500">{{ __('No completed sessions in this date range.') }}</p>
            @endif
        </flux:card>
    </div>
</flux:main>
