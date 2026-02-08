<?php

use App\Models\MentorshipTopic;
use App\Models\MentorshipNote;
use App\Models\MentorshipSession;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.mentorship')]
#[Title('Mentorship Dashboard')]
class extends Component {

    #[Computed]
    public function stats(): array
    {
        $userId = auth()->id();

        return [
            'total_topics' => MentorshipTopic::published()->count(),
            'upcoming_topics' => MentorshipTopic::published()->upcoming()->count(),
            'my_notes' => MentorshipNote::forUser($userId)->count(),
            'this_week' => MentorshipTopic::published()
                ->forWeek(now()->startOfWeek(\Carbon\Carbon::SUNDAY), now()->endOfWeek(\Carbon\Carbon::SATURDAY))
                ->count(),
        ];
    }

    #[Computed]
    public function teachingStats(): array
    {
        $userId = auth()->id();

        $sessions = MentorshipSession::forMentor($userId);
        $completedSessions = (clone $sessions)->completed();

        return [
            'sessions_conducted' => $completedSessions->count(),
            'participants_reached' => $completedSessions->sum('participant_count'),
            'topics_covered' => $completedSessions->distinct('topic_id')->count('topic_id'),
            'upcoming_sessions' => MentorshipSession::forMentor($userId)->upcoming()->count(),
        ];
    }

    #[Computed]
    public function recentSessions()
    {
        return MentorshipSession::forMentor(auth()->id())
            ->with(['topic'])
            ->latest('session_date')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function categories(): array
    {
        return MentorshipTopic::published()
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->orderBy('count', 'desc')
            ->get()
            ->pluck('count', 'category')
            ->toArray();
    }

    #[Computed]
    public function upcomingTopics()
    {
        return MentorshipTopic::published()
            ->upcoming()
            ->with(['attachments'])
            ->limit(5)
            ->get();
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
}; ?>

<flux:main>
    <div class="space-y-6">
        {{-- Header --}}
        <div>
            <flux:heading size="xl">{{ __('Welcome to Mentorship Platform') }}</flux:heading>
            <flux:subheading>{{ __('Access weekly counseling topics, prepare lessons, and conduct teaching sessions') }}</flux:subheading>
        </div>

        {{-- Stats Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <flux:card>
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0">
                        <flux:icon.clipboard-document-list class="size-10 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $this->stats['total_topics'] }}</div>
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Total Topics') }}</div>
                    </div>
                </div>
            </flux:card>

            <flux:card>
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0">
                        <flux:icon.calendar class="size-10 text-green-600 dark:text-green-400" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $this->stats['this_week'] }}</div>
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('This Week') }}</div>
                    </div>
                </div>
            </flux:card>

            <flux:card>
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0">
                        <flux:icon.arrow-trending-up class="size-10 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $this->stats['upcoming_topics'] }}</div>
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Upcoming') }}</div>
                    </div>
                </div>
            </flux:card>

            <flux:card>
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0">
                        <flux:icon.pencil-square class="size-10 text-purple-600 dark:text-purple-400" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $this->stats['my_notes'] }}</div>
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('My Notes') }}</div>
                    </div>
                </div>
            </flux:card>
        </div>

        {{-- Quick Actions --}}
        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('Quick Actions') }}</flux:heading>
            <div class="flex flex-wrap gap-3">
                <flux:button variant="primary" :href="route('mentorship.topics.week')" wire:navigate icon="calendar">
                    {{ __('View Weekly Topics') }}
                </flux:button>

                <flux:button variant="filled" :href="route('mentorship.sessions.start')" wire:navigate icon="play">
                    {{ __('Start Session') }}
                </flux:button>

                <flux:button variant="ghost" :href="route('mentorship.sessions.index')" wire:navigate icon="presentation-chart-bar">
                    {{ __('My Sessions') }}
                </flux:button>

                @can('manage-mentorship')
                <flux:button variant="ghost" :href="route('mentorship.topics.create')" wire:navigate icon="plus">
                    {{ __('Add New Topic') }}
                </flux:button>

                <flux:button variant="ghost" :href="route('mentorship.lessons.index')" wire:navigate icon="book-open">
                    {{ __('Lessons Library') }}
                </flux:button>

                <flux:button variant="ghost" :href="route('mentorship.import.csv')" wire:navigate icon="arrow-up-tray">
                    {{ __('Import from CSV') }}
                </flux:button>
                @endcan
            </div>
        </flux:card>

        {{-- My Teaching Activity Card --}}
        <flux:card>
            <div class="flex items-center justify-between mb-4">
                <flux:heading size="lg">{{ __('My Teaching Activity') }}</flux:heading>
                <flux:button variant="ghost" size="sm" :href="route('mentorship.sessions.index')" wire:navigate icon-trailing="arrow-right">
                    {{ __('View All Sessions') }}
                </flux:button>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                    <div class="text-2xl font-bold text-green-700 dark:text-green-400">{{ $this->teachingStats['sessions_conducted'] }}</div>
                    <div class="text-sm text-green-600 dark:text-green-500">{{ __('Sessions Conducted') }}</div>
                </div>

                <div class="p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                    <div class="text-2xl font-bold text-blue-700 dark:text-blue-400">{{ $this->teachingStats['participants_reached'] }}</div>
                    <div class="text-sm text-blue-600 dark:text-blue-500">{{ __('Participants Reached') }}</div>
                </div>

                <div class="p-4 rounded-lg bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800">
                    <div class="text-2xl font-bold text-purple-700 dark:text-purple-400">{{ $this->teachingStats['topics_covered'] }}</div>
                    <div class="text-sm text-purple-600 dark:text-purple-500">{{ __('Topics Covered') }}</div>
                </div>

                <div class="p-4 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
                    <div class="text-2xl font-bold text-amber-700 dark:text-amber-400">{{ $this->teachingStats['upcoming_sessions'] }}</div>
                    <div class="text-sm text-amber-600 dark:text-amber-500">{{ __('Upcoming Sessions') }}</div>
                </div>
            </div>

            {{-- Recent Sessions --}}
            @if($this->recentSessions->isNotEmpty())
            <div class="mt-6">
                <flux:subheading class="mb-3">{{ __('Recent Sessions') }}</flux:subheading>
                <div class="space-y-2">
                    @foreach($this->recentSessions as $session)
                        <a href="{{ route('mentorship.sessions.show', $session) }}" wire:navigate
                           class="flex items-center justify-between p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">
                            <div class="flex items-center gap-3">
                                <flux:badge :color="$session->status_color" size="sm">{{ $session->status_label }}</flux:badge>
                                <span class="font-medium">{{ $session->topic?->title ?? 'Unknown Topic' }}</span>
                            </div>
                            <div class="flex items-center gap-4 text-sm text-zinc-500">
                                @if($session->status === 'completed')
                                    <span>{{ $session->participant_count }} {{ Str::plural('participant', $session->participant_count) }}</span>
                                @endif
                                <span>{{ $session->session_date->format('M d, Y') }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </flux:card>

        {{-- Categories Overview --}}
        @if(!empty($this->categories))
        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('Topics by Category') }}</flux:heading>
            <div class="space-y-3">
                @foreach($this->categories as $category => $count)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <flux:badge :color="$this->getCategoryColor($category)" size="sm">{{ $category }}</flux:badge>
                        </div>
                        <span class="text-sm font-medium">{{ $count }} {{ Str::plural('topic', $count) }}</span>
                    </div>
                @endforeach
            </div>
        </flux:card>
        @endif

        {{-- Upcoming Topics --}}
        @if($this->upcomingTopics->isNotEmpty())
        <flux:card>
            <div class="flex items-center justify-between mb-4">
                <flux:heading size="lg">{{ __('Upcoming Topics') }}</flux:heading>
                <flux:button variant="ghost" size="sm" :href="route('mentorship.topics.week')" wire:navigate icon-trailing="arrow-right">
                    {{ __('View All') }}
                </flux:button>
            </div>

            <div class="space-y-3">
                @foreach($this->upcomingTopics as $topic)
                    <a href="{{ route('mentorship.topics.show', $topic) }}" wire:navigate class="flex items-start gap-4 p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">
                        <div class="flex-shrink-0 text-center min-w-[60px]">
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $topic->day_of_week }}</div>
                            <div class="text-lg font-bold">{{ $topic->topic_date->format('M d') }}</div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $topic->time_slot_label }}</div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <h4 class="font-semibold truncate">{{ $topic->title }}</h4>
                            <flux:badge :color="$topic->category_color" size="sm" class="mt-1">{{ $topic->category }}</flux:badge>

                            @if($topic->attachments->isNotEmpty())
                                <div class="flex items-center gap-1 mt-2 text-xs text-zinc-500">
                                    <flux:icon.paper-clip class="size-3" />
                                    {{ $topic->attachments->count() }} {{ Str::plural('attachment', $topic->attachments->count()) }}
                                </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </flux:card>
        @endif
    </div>
</flux:main>
