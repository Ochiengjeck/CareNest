<?php

use App\Models\MentorshipTopic;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new
#[Layout('layouts.mentorship')]
#[Title('Weekly Topics')]
class extends Component {

    #[Url]
    public ?string $week = null;

    #[Url]
    public string $categoryFilter = '';

    public function mount(): void
    {
        if (!$this->week) {
            // Start week on Sunday
            $this->week = now()->startOfWeek(\Carbon\Carbon::SUNDAY)->format('Y-m-d');
        }
    }

    #[Computed]
    public function weekStart()
    {
        return \Carbon\Carbon::parse($this->week)->startOfWeek(\Carbon\Carbon::SUNDAY);
    }

    #[Computed]
    public function weekEnd()
    {
        // Week ends on Saturday when starting on Sunday
        return $this->weekStart->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);
    }

    #[Computed]
    public function topics()
    {
        $query = MentorshipTopic::query()
            ->with(['attachments', 'notes' => fn($q) => $q->forUser(auth()->id())])
            ->forWeek($this->weekStart, $this->weekEnd);

        // Show unpublished only to managers
        if (!auth()->user()->can('manage-mentorship')) {
            $query->published();
        }

        if ($this->categoryFilter) {
            $query->byCategory($this->categoryFilter);
        }

        return $query->get()->groupBy(function($topic) {
            return $topic->topic_date->format('Y-m-d');
        });
    }

    #[Computed]
    public function categories(): array
    {
        return MentorshipTopic::query()
            ->selectRaw('DISTINCT category')
            ->orderBy('category')
            ->pluck('category')
            ->toArray();
    }

    public function previousWeek(): void
    {
        $this->week = $this->weekStart->copy()->subWeek()->format('Y-m-d');
        unset($this->topics);
    }

    public function nextWeek(): void
    {
        $this->week = $this->weekStart->copy()->addWeek()->format('Y-m-d');
        unset($this->topics);
    }

    public function currentWeek(): void
    {
        $this->week = now()->startOfWeek(\Carbon\Carbon::SUNDAY)->format('Y-m-d');
        unset($this->topics);
    }
}; ?>

<flux:main>
    <div class="space-y-6">
        {{-- Header with Navigation --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Weekly Topics') }}</flux:heading>
                <flux:subheading>
                    {{ $this->weekStart->format('M d') }} - {{ $this->weekEnd->format('M d, Y') }}
                </flux:subheading>
            </div>

            @can('manage-mentorship')
            <flux:button variant="primary" :href="route('mentorship.topics.create')" wire:navigate icon="plus">
                {{ __('Add Topic') }}
            </flux:button>
            @endcan
        </div>

        {{-- Week Navigation & Filters --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex gap-2">
                <flux:button wire:click="previousWeek" variant="ghost" icon="chevron-left" size="sm">
                    {{ __('Previous') }}
                </flux:button>
                <flux:button wire:click="currentWeek" variant="ghost" size="sm">
                    {{ __('This Week') }}
                </flux:button>
                <flux:button wire:click="nextWeek" variant="ghost" icon-trailing="chevron-right" size="sm">
                    {{ __('Next') }}
                </flux:button>
            </div>

            <flux:select wire:model.live="categoryFilter" class="sm:max-w-xs">
                <option value="">{{ __('All Categories') }}</option>
                @foreach($this->categories as $category)
                    <option value="{{ $category }}">{{ $category }}</option>
                @endforeach
            </flux:select>
        </div>

        {{-- Weekly Schedule Grid --}}
        @php
            $days = [];
            for ($i = 0; $i < 7; $i++) {
                $day = $this->weekStart->copy()->addDays($i);
                $days[] = $day;
            }
            $timeSlots = ['10:00', '14:00', '18:00'];
            $timeLabels = ['10:00 AM', '2:00 PM', '6:00 PM'];
        @endphp

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-zinc-100 dark:bg-zinc-800">
                        <th class="border border-zinc-300 dark:border-zinc-600 p-3 text-left font-semibold min-w-[100px]">
                            {{ __('Date/Day') }}
                        </th>
                        @foreach($timeLabels as $label)
                            <th class="border border-zinc-300 dark:border-zinc-600 p-3 text-center font-semibold min-w-[250px]">
                                {{ $label }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($days as $day)
                        @php
                            $dateKey = $day->format('Y-m-d');
                            $dayTopics = $this->topics[$dateKey] ?? collect();
                        @endphp
                        <tr>
                            <td class="border border-zinc-300 dark:border-zinc-600 p-3 bg-zinc-50 dark:bg-zinc-800">
                                <div class="font-semibold">{{ $day->format('M d') }}</div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $day->format('D') }}</div>
                            </td>

                            @foreach($timeSlots as $slot)
                                @php
                                    // Handle both '10:00' and '10:00:00' formats from database
                                    $topic = $dayTopics->first(fn($t) => str_starts_with($t->time_slot, $slot));
                                @endphp
                                <td class="border border-zinc-300 dark:border-zinc-600 p-3 align-top {{ $topic ? 'hover:bg-zinc-50 dark:hover:bg-zinc-700' : '' }}">
                                    @if($topic)
                                        <a href="{{ route('mentorship.topics.show', $topic) }}" wire:navigate class="block space-y-2">
                                            @if(!$topic->is_published)
                                                <flux:badge color="zinc" size="xs">{{ __('Unpublished') }}</flux:badge>
                                            @endif

                                            <h4 class="font-semibold text-sm leading-tight hover:text-blue-600 dark:hover:text-blue-400">{{ $topic->title }}</h4>
                                            <flux:badge :color="$topic->category_color" size="sm">{{ $topic->category }}</flux:badge>

                                            <div class="flex items-center gap-3 text-xs text-zinc-500">
                                                @if($topic->attachments->isNotEmpty())
                                                    <div class="flex items-center gap-1">
                                                        <flux:icon.paper-clip class="size-3" />
                                                        {{ $topic->attachments->count() }}
                                                    </div>
                                                @endif

                                                @if($topic->notes->isNotEmpty())
                                                    <div class="flex items-center gap-1 text-blue-600 dark:text-blue-400">
                                                        <flux:icon.pencil-square class="size-3" />
                                                        {{ __('Note') }}
                                                    </div>
                                                @endif
                                            </div>
                                        </a>
                                    @else
                                        <div class="text-center text-zinc-400 dark:text-zinc-600 text-sm py-4">
                                            —
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Legend --}}
        <flux:card>
            <flux:heading size="sm" class="mb-3">{{ __('Category Legend') }}</flux:heading>
            <div class="flex flex-wrap gap-2">
                <flux:badge color="blue">{{ __('Mental Health') }}</flux:badge>
                <flux:badge color="purple">{{ __('Substance Use Disorder') }}</flux:badge>
                <flux:badge color="green">{{ __('Employment/Education') }}</flux:badge>
                <flux:badge color="red">{{ __('Physical Health') }}</flux:badge>
                <flux:badge color="amber">{{ __('Financial/Housing') }}</flux:badge>
                <flux:badge color="cyan">{{ __('Psycho-Social/Family') }}</flux:badge>
                <flux:badge color="rose">{{ __('Spirituality') }}</flux:badge>
            </div>
        </flux:card>
    </div>
</flux:main>
