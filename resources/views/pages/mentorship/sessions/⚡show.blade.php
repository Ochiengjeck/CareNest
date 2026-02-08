<?php

use App\Models\MentorshipSession;
use App\Concerns\MentorshipValidationRules;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.mentorship')]
#[Title('Session Detail')]
class extends Component {
    use MentorshipValidationRules;

    #[Locked]
    public int $sessionId;

    // Completion form
    public bool $showCompleteForm = false;
    public int $participant_count = 0;
    public string $participant_notes = '';
    public string $session_notes = '';

    public function mount(MentorshipSession $session): void
    {
        // Only the mentor or users with manage-mentorship can view
        if ($session->mentor_id !== auth()->id() && !auth()->user()->can('manage-mentorship')) {
            abort(403);
        }

        $this->sessionId = $session->id;
        $this->session_notes = $session->session_notes ?? '';
    }

    #[Computed]
    public function session(): MentorshipSession
    {
        return MentorshipSession::with(['topic', 'mentor', 'lesson'])->findOrFail($this->sessionId);
    }

    public function toggleCompleteForm(): void
    {
        $this->showCompleteForm = !$this->showCompleteForm;
    }

    public function markInProgress(): void
    {
        $session = $this->session;

        if ($session->mentor_id !== auth()->id()) {
            abort(403);
        }

        $session->markInProgress();
        unset($this->session);
        $this->dispatch('session-updated');
    }

    public function completeSession(): void
    {
        $this->validate($this->mentorshipSessionCompletionRules());

        $session = $this->session;

        if ($session->mentor_id !== auth()->id()) {
            abort(403);
        }

        $session->markCompleted($this->participant_count, $this->participant_notes ?: null);

        if ($this->session_notes) {
            $session->update(['session_notes' => $this->session_notes]);
        }

        $this->showCompleteForm = false;
        unset($this->session);
        $this->dispatch('session-completed');
    }

    public function cancelSession(): void
    {
        $session = $this->session;

        if ($session->mentor_id !== auth()->id()) {
            abort(403);
        }

        $session->cancel();
        unset($this->session);
        $this->dispatch('session-cancelled');
    }
}; ?>

<flux:main>
    <div class="space-y-6 max-w-4xl">
        {{-- Flash Message --}}
        @if(session('status'))
            <flux:callout variant="success" icon="check-circle" dismissible>
                {{ session('status') }}
            </flux:callout>
        @endif

        {{-- Back Button --}}
        <flux:button variant="ghost" :href="route('mentorship.sessions.index')" wire:navigate icon="arrow-left">
            {{ __('Back to My Sessions') }}
        </flux:button>

        {{-- Session Header --}}
        <flux:card>
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        <flux:badge :color="$this->session->status_color" size="sm">
                            {{ $this->session->status_label }}
                        </flux:badge>
                        @if($this->session->topic)
                            <flux:badge :color="$this->session->topic->category_color" size="sm">
                                {{ $this->session->topic->category }}
                            </flux:badge>
                        @endif
                    </div>

                    <flux:heading size="xl" class="mb-2">
                        {{ $this->session->topic?->title ?? __('Unknown Topic') }}
                    </flux:heading>

                    <div class="flex flex-wrap gap-4 text-sm text-zinc-600 dark:text-zinc-400">
                        <div class="flex items-center gap-1">
                            <flux:icon.calendar class="size-4" />
                            {{ $this->session->session_date->format('l, F d, Y') }}
                        </div>
                        @if($this->session->start_time)
                            <div class="flex items-center gap-1">
                                <flux:icon.clock class="size-4" />
                                {{ $this->session->start_time->format('g:i A') }}
                                @if($this->session->end_time)
                                    - {{ $this->session->end_time->format('g:i A') }}
                                @endif
                            </div>
                        @endif
                        @if($this->session->formatted_duration)
                            <div class="flex items-center gap-1">
                                <flux:icon.arrow-path class="size-4" />
                                {{ $this->session->formatted_duration }}
                            </div>
                        @endif
                        <div class="flex items-center gap-1">
                            <flux:icon.user class="size-4" />
                            {{ $this->session->mentor?->name ?? __('Unknown') }}
                        </div>
                    </div>
                </div>

                @if($this->session->mentor_id === auth()->id())
                    <div class="flex items-center gap-2">
                        @if($this->session->status === 'planned' || $this->session->status === 'in_progress')
                            <flux:button variant="ghost" :href="route('mentorship.sessions.edit', $this->session)" wire:navigate icon="pencil">
                                {{ __('Edit') }}
                            </flux:button>
                        @endif
                    </div>
                @endif
            </div>
        </flux:card>

        {{-- Actions Card --}}
        @if($this->session->mentor_id === auth()->id() && in_array($this->session->status, ['planned', 'in_progress']))
            <flux:card>
                <flux:heading size="lg" class="mb-4">{{ __('Session Actions') }}</flux:heading>

                <div class="flex flex-wrap gap-3">
                    @if($this->session->status === 'planned')
                        <flux:button variant="primary" wire:click="markInProgress" icon="play">
                            {{ __('Start Session') }}
                        </flux:button>
                    @endif

                    @if($this->session->status === 'in_progress')
                        <flux:button variant="primary" wire:click="toggleCompleteForm" icon="check">
                            {{ __('Complete Session') }}
                        </flux:button>
                    @endif

                    @if(in_array($this->session->status, ['planned', 'in_progress']))
                        <flux:button variant="danger" wire:click="cancelSession"
                            wire:confirm="{{ __('Are you sure you want to cancel this session?') }}" icon="x-mark">
                            {{ __('Cancel Session') }}
                        </flux:button>
                    @endif
                </div>

                {{-- Complete Session Form --}}
                @if($showCompleteForm)
                    <form wire:submit="completeSession" class="mt-6 p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 space-y-4">
                        <flux:heading size="base">{{ __('Complete Session') }}</flux:heading>

                        <flux:input
                            type="number"
                            wire:model="participant_count"
                            :label="__('Number of Participants')"
                            min="0"
                            max="500"
                            required
                        />

                        <flux:textarea
                            wire:model="participant_notes"
                            :label="__('Participant Notes (Optional)')"
                            :placeholder="__('Names, observations, or notes about attendees...')"
                            rows="3"
                        />

                        <flux:textarea
                            wire:model="session_notes"
                            :label="__('Session Notes (Optional)')"
                            :placeholder="__('How did the session go? Key takeaways, challenges, next steps...')"
                            rows="4"
                        />

                        <div class="flex gap-3">
                            <flux:button type="submit" variant="primary" icon="check">
                                {{ __('Mark Completed') }}
                            </flux:button>
                            <flux:button type="button" variant="ghost" wire:click="toggleCompleteForm">
                                {{ __('Cancel') }}
                            </flux:button>
                        </div>
                    </form>
                @endif
            </flux:card>
        @endif

        {{-- Completed Session Stats --}}
        @if($this->session->status === 'completed')
            <flux:card>
                <flux:heading size="lg" class="mb-4">{{ __('Session Results') }}</flux:heading>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-center">
                        <div class="text-3xl font-bold text-green-700 dark:text-green-400">{{ $this->session->participant_count }}</div>
                        <div class="text-sm text-green-600 dark:text-green-500">{{ __('Participants') }}</div>
                    </div>

                    @if($this->session->formatted_duration)
                        <div class="p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-center">
                            <div class="text-3xl font-bold text-blue-700 dark:text-blue-400">{{ $this->session->formatted_duration }}</div>
                            <div class="text-sm text-blue-600 dark:text-blue-500">{{ __('Duration') }}</div>
                        </div>
                    @endif

                    @if($this->session->lesson)
                        <div class="p-4 rounded-lg bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 text-center">
                            <div class="text-lg font-bold text-purple-700 dark:text-purple-400 truncate">{{ $this->session->lesson->title }}</div>
                            <div class="text-sm text-purple-600 dark:text-purple-500">{{ __('Lesson Used') }}</div>
                        </div>
                    @endif
                </div>

                @if($this->session->participant_notes)
                    <div class="mt-4 p-4 rounded-lg bg-zinc-50 dark:bg-zinc-800">
                        <flux:subheading class="mb-2">{{ __('Participant Notes') }}</flux:subheading>
                        <div class="prose dark:prose-invert max-w-none">
                            {!! nl2br(e($this->session->participant_notes)) !!}
                        </div>
                    </div>
                @endif
            </flux:card>
        @endif

        {{-- Session Notes --}}
        @if($this->session->session_notes)
            <flux:card>
                <flux:heading size="lg" class="mb-4">{{ __('Session Notes') }}</flux:heading>
                <div class="prose dark:prose-invert max-w-none">
                    {!! nl2br(e($this->session->session_notes)) !!}
                </div>
            </flux:card>
        @endif

        {{-- Lesson Content Used --}}
        @if($this->session->lesson_content_snapshot)
            <flux:card>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center size-9 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-500 text-white shadow-sm">
                            <flux:icon.book-open class="size-5" />
                        </div>
                        <div>
                            <flux:heading size="lg">{{ __('Lesson Content') }}</flux:heading>
                            @if($this->session->lesson)
                                <p class="text-sm text-zinc-500">{{ $this->session->lesson->title }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <x-mentorship.lesson-viewer
                    :content="$this->session->lesson_content_snapshot"
                    :title="$this->session->topic?->title"
                />
            </flux:card>
        @endif

        {{-- Meta Info --}}
        <div class="text-sm text-zinc-500 dark:text-zinc-400">
            {{ __('Created') }} {{ $this->session->created_at->diffForHumans() }}
            @if($this->session->updated_at->ne($this->session->created_at))
                &middot; {{ __('Updated') }} {{ $this->session->updated_at->diffForHumans() }}
            @endif
        </div>
    </div>

    @script
    <script>
        $wire.on('session-updated', () => {
            Flux.toast({ text: '{{ __('Session started!') }}', heading: '{{ __('In Progress') }}', variant: 'success' })
        })
        $wire.on('session-completed', () => {
            Flux.toast({ text: '{{ __('Session completed successfully!') }}', heading: '{{ __('Completed') }}', variant: 'success' })
        })
        $wire.on('session-cancelled', () => {
            Flux.toast({ text: '{{ __('Session has been cancelled.') }}', heading: '{{ __('Cancelled') }}', variant: 'warning' })
        })
    </script>
    @endscript
</flux:main>
