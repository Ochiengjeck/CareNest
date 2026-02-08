<?php

use App\Models\MentorshipTopic;
use App\Models\MentorshipNote;
use App\Models\MentorshipSession;
use App\Concerns\MentorshipValidationRules;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;

new
#[Layout('layouts.mentorship')]
#[Title('Topic Detail')]
class extends Component {
    use MentorshipValidationRules;

    #[Locked]
    public int $topicId;

    public string $noteContent = '';
    public bool $showNoteEditor = false;

    public function mount(MentorshipTopic $topic): void
    {
        // Check authorization for unpublished topics
        if (!$topic->is_published && !auth()->user()->can('manage-mentorship')) {
            abort(403);
        }

        $this->topicId = $topic->id;

        // Load existing note if present
        $existingNote = $topic->notes()->forUser(auth()->id())->first();
        if ($existingNote) {
            $this->noteContent = $existingNote->content;
        }
    }

    #[Computed]
    public function topic(): MentorshipTopic
    {
        return MentorshipTopic::with(['attachments', 'creator', 'savedLesson'])->findOrFail($this->topicId);
    }

    #[Computed]
    public function hasNote(): bool
    {
        return MentorshipNote::where('topic_id', $this->topicId)
            ->where('user_id', auth()->id())
            ->exists();
    }

    #[Computed]
    public function recentSessions()
    {
        return MentorshipSession::with(['mentor'])
            ->byTopic($this->topicId)
            ->completed()
            ->latest('session_date')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function topicSessionStats(): array
    {
        $sessions = MentorshipSession::byTopic($this->topicId);
        $completed = (clone $sessions)->completed();

        return [
            'times_taught' => $completed->count(),
            'total_participants' => $completed->sum('participant_count'),
        ];
    }

    public function toggleNoteEditor(): void
    {
        $this->showNoteEditor = !$this->showNoteEditor;
    }

    public function saveNote(): void
    {
        $this->validate($this->mentorshipNoteRules());

        MentorshipNote::updateOrCreate(
            [
                'topic_id' => $this->topicId,
                'user_id' => auth()->id(),
            ],
            [
                'content' => $this->noteContent,
            ]
        );

        $this->showNoteEditor = false;
        $this->dispatch('note-saved');
        unset($this->hasNote);
    }

    public function deleteNote(): void
    {
        MentorshipNote::where('topic_id', $this->topicId)
            ->where('user_id', auth()->id())
            ->delete();

        $this->noteContent = '';
        $this->showNoteEditor = false;
        $this->dispatch('note-deleted');
        unset($this->hasNote);
    }
}; ?>

<flux:main>
    <div class="space-y-6 max-w-4xl">
        {{-- Back Button --}}
        <flux:button variant="ghost" :href="route('mentorship.topics.week')" wire:navigate icon="arrow-left">
            {{ __('Back to Weekly Topics') }}
        </flux:button>

        {{-- Topic Header --}}
        <flux:card>
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        @if(!$this->topic->is_published)
                            <flux:badge color="zinc">{{ __('Unpublished') }}</flux:badge>
                        @endif
                        <flux:badge :color="$this->topic->category_color">{{ $this->topic->category }}</flux:badge>
                    </div>

                    <flux:heading size="xl" class="mb-2">{{ $this->topic->title }}</flux:heading>

                    <div class="flex flex-wrap gap-4 text-sm text-zinc-600 dark:text-zinc-400">
                        <div class="flex items-center gap-1">
                            <flux:icon.calendar class="size-4" />
                            {{ $this->topic->topic_date->format('l, F d, Y') }}
                        </div>
                        <div class="flex items-center gap-1">
                            <flux:icon.clock class="size-4" />
                            {{ $this->topic->time_slot_label }}
                        </div>
                    </div>
                </div>

                @can('manage-mentorship')
                <flux:button variant="ghost" :href="route('mentorship.topics.edit', $this->topic)" wire:navigate icon="pencil">
                    {{ __('Edit') }}
                </flux:button>
                @endcan
            </div>

            @if($this->topic->description)
                <flux:separator class="my-4" />
                <div class="prose dark:prose-invert max-w-none">
                    {!! nl2br(e($this->topic->description)) !!}
                </div>
            @endif
        </flux:card>

        {{-- Teach This Topic --}}
        <flux:card class="border-indigo-200 dark:border-indigo-800 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <flux:icon.presentation-chart-bar class="size-8 text-indigo-600 dark:text-indigo-400" />
                    <div>
                        <flux:heading size="lg">{{ __('Teach This Topic') }}</flux:heading>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            {{ __('Prepare a lesson and start a teaching session') }}
                        </p>
                    </div>
                </div>

                <flux:button variant="primary" :href="route('mentorship.sessions.start-topic', $this->topic)" wire:navigate icon="play">
                    {{ __('Prepare & Start Session') }}
                </flux:button>
            </div>

            {{-- Session Stats --}}
            @if($this->topicSessionStats['times_taught'] > 0)
                <div class="flex flex-wrap gap-6 mt-4 pt-4 border-t border-indigo-200 dark:border-indigo-700 text-sm">
                    <div>
                        <span class="font-semibold text-indigo-700 dark:text-indigo-300">{{ $this->topicSessionStats['times_taught'] }}</span>
                        <span class="text-zinc-600 dark:text-zinc-400">{{ __('times taught') }}</span>
                    </div>
                    <div>
                        <span class="font-semibold text-indigo-700 dark:text-indigo-300">{{ $this->topicSessionStats['total_participants'] }}</span>
                        <span class="text-zinc-600 dark:text-zinc-400">{{ __('total participants') }}</span>
                    </div>
                </div>
            @endif
        </flux:card>

        {{-- Recent Sessions for This Topic --}}
        @if($this->recentSessions->isNotEmpty())
            <flux:card>
                <flux:heading size="lg" class="mb-4">{{ __('Recent Sessions') }}</flux:heading>
                <div class="space-y-3">
                    @foreach($this->recentSessions as $session)
                        <a href="{{ route('mentorship.sessions.show', $session) }}" wire:navigate
                           class="flex items-center justify-between p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">
                            <div class="flex items-center gap-3">
                                <flux:badge :color="$session->status_color" size="sm">{{ $session->status_label }}</flux:badge>
                                <span class="font-medium">{{ $session->mentor?->name }}</span>
                            </div>
                            <div class="flex items-center gap-4 text-sm text-zinc-500">
                                <span>{{ $session->participant_count }} {{ Str::plural('participant', $session->participant_count) }}</span>
                                <span>{{ $session->session_date->format('M d, Y') }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </flux:card>
        @endif

        {{-- AI Lesson Content --}}
        @if($this->topic->has_ai_lesson)
            <flux:card class="border-purple-200 dark:border-purple-800">
                <div class="flex items-center gap-2 mb-4">
                    <flux:icon.sparkles class="size-5 text-purple-600" />
                    <flux:heading size="lg">{{ __('AI-Generated Lesson') }}</flux:heading>
                    @if($this->topic->savedLesson)
                        <flux:badge color="purple" size="sm">{{ __('Saved to Library') }}</flux:badge>
                    @endif
                </div>

                <x-mentorship.formatted-content :content="$this->topic->ai_lesson_content" class="p-4 rounded-lg bg-purple-50 dark:bg-purple-900/20" />

                @if($this->topic->ai_lesson_generated_at)
                    <p class="text-xs text-zinc-500 mt-3">
                        {{ __('Generated') }} {{ $this->topic->ai_lesson_generated_at->diffForHumans() }}
                    </p>
                @endif

                @if($this->topic->savedLesson)
                    <flux:button variant="ghost" size="sm" class="mt-3"
                        :href="route('mentorship.lessons.show', $this->topic->savedLesson)" wire:navigate icon="book-open">
                        {{ __('View in Lessons Library') }}
                    </flux:button>
                @endif
            </flux:card>
        @endif

        {{-- Attachments --}}
        @if($this->topic->attachments->isNotEmpty())
        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('Attachments') }}</flux:heading>
            <div class="space-y-3">
                @foreach($this->topic->attachments as $attachment)
                    <div class="flex items-center gap-4 p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800">
                        <flux:icon :name="$attachment->file_icon" class="size-8 text-zinc-500" />

                        <div class="flex-1 min-w-0">
                            <h4 class="font-medium truncate">{{ $attachment->display_name_or_filename }}</h4>
                            @if($attachment->description)
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $attachment->description }}</p>
                            @endif
                            <p class="text-xs text-zinc-400">{{ $attachment->file_size_formatted }}</p>
                        </div>

                        <flux:button variant="ghost" size="sm" :href="Storage::url($attachment->file_path)" target="_blank" icon="arrow-down-tray">
                            {{ __('Download') }}
                        </flux:button>
                    </div>
                @endforeach
            </div>
        </flux:card>
        @endif

        {{-- Personal Notes --}}
        <flux:card>
            <div class="flex items-center justify-between mb-4">
                <flux:heading size="lg">{{ __('My Personal Notes') }}</flux:heading>
                @if($this->hasNote && !$showNoteEditor)
                    <div class="flex gap-2">
                        <flux:button variant="ghost" size="sm" wire:click="toggleNoteEditor" icon="pencil">
                            {{ __('Edit') }}
                        </flux:button>
                        <flux:button variant="ghost" size="sm" wire:click="deleteNote" wire:confirm="{{ __('Are you sure you want to delete your note?') }}" icon="trash" class="text-red-600">
                            {{ __('Delete') }}
                        </flux:button>
                    </div>
                @endif
            </div>

            @if($showNoteEditor)
                <form wire:submit="saveNote" class="space-y-4">
                    <flux:textarea
                        wire:model="noteContent"
                        :label="__('Note Content')"
                        rows="8"
                        :placeholder="__('Your personal reflections, key takeaways, action items...')"
                        required
                    />

                    <div class="flex gap-3">
                        <flux:button type="submit" variant="primary">
                            {{ __('Save Note') }}
                        </flux:button>
                        <flux:button type="button" variant="ghost" wire:click="toggleNoteEditor">
                            {{ __('Cancel') }}
                        </flux:button>
                    </div>
                </form>
            @elseif($this->hasNote)
                <div class="prose dark:prose-invert max-w-none p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                    {!! nl2br(e($noteContent)) !!}
                </div>
            @else
                <div class="text-center py-8">
                    <flux:icon.pencil-square class="size-12 text-zinc-300 dark:text-zinc-600 mx-auto mb-3" />
                    <p class="text-zinc-500 dark:text-zinc-400 mb-4">{{ __('No personal notes yet for this topic.') }}</p>
                    <flux:button variant="primary" wire:click="toggleNoteEditor" icon="plus">
                        {{ __('Add Note') }}
                    </flux:button>
                </div>
            @endif
        </flux:card>

        {{-- Created By Info --}}
        @if($this->topic->creator)
        <div class="text-sm text-zinc-500 dark:text-zinc-400">
            {{ __('Created by :name on :date', [
                'name' => $this->topic->creator->name,
                'date' => $this->topic->created_at->format('M d, Y')
            ]) }}
        </div>
        @endif
    </div>

    @script
    <script>
        $wire.on('note-saved', () => {
            Flux.toast({ text: '{{ __('Your note has been saved.') }}', heading: '{{ __('Note Saved') }}', variant: 'success' })
        })

        $wire.on('note-deleted', () => {
            Flux.toast({ text: '{{ __('Your note has been deleted.') }}', heading: '{{ __('Note Deleted') }}', variant: 'success' })
        })
    </script>
    @endscript
</flux:main>
