<?php

use App\Models\MentorshipLesson;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
new
#[Layout('layouts.mentorship')]
#[Title('View Lesson')]
class extends Component {

    #[Locked]
    public int $lessonId;

    public function mount(MentorshipLesson $lesson): void
    {
        $this->lessonId = $lesson->id;
    }

    #[Computed]
    public function lesson(): MentorshipLesson
    {
        return MentorshipLesson::with(['sourceTopic', 'creator', 'updater'])->findOrFail($this->lessonId);
    }

    public function togglePublish(): void
    {
        $this->lesson->update([
            'is_published' => !$this->lesson->is_published,
            'updated_by' => auth()->id(),
        ]);

        unset($this->lesson);
        $this->dispatch('publish-toggled');
    }

    public function delete(): void
    {
        $this->lesson->delete();

        session()->flash('status', __('Lesson deleted successfully!'));
        $this->redirect(route('mentorship.lessons.index'), navigate: true);
    }
}; ?>

<flux:main>
    <div class="space-y-6 max-w-4xl">
        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <flux:heading size="xl">{{ $this->lesson->title }}</flux:heading>
                    @if($this->lesson->is_ai_generated)
                        <flux:badge color="purple">{{ __('AI Generated') }}</flux:badge>
                    @endif
                    @if(!$this->lesson->is_published)
                        <flux:badge color="zinc">{{ __('Draft') }}</flux:badge>
                    @else
                        <flux:badge color="green">{{ __('Published') }}</flux:badge>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    <flux:badge :color="$this->lesson->category_color">{{ $this->lesson->category }}</flux:badge>

                    @if($this->lesson->sourceTopic)
                        <span class="text-sm text-zinc-500">
                            {{ __('From topic:') }}
                            <a href="{{ route('mentorship.topics.show', $this->lesson->sourceTopic) }}" wire:navigate
                               class="hover:underline text-purple-600 dark:text-purple-400">
                                {{ $this->lesson->sourceTopic->title }}
                            </a>
                        </span>
                    @endif
                </div>
            </div>

            <div class="flex gap-2">
                <flux:button variant="ghost" :href="route('mentorship.lessons.index')" wire:navigate icon="arrow-left">
                    {{ __('Back') }}
                </flux:button>
                <flux:button variant="ghost" :href="route('mentorship.lessons.edit', $this->lesson)" wire:navigate icon="pencil">
                    {{ __('Edit') }}
                </flux:button>
            </div>
        </div>

        {{-- Summary --}}
        @if($this->lesson->summary)
            <flux:card class="bg-purple-50 dark:bg-purple-900/20 border-purple-200 dark:border-purple-800">
                <flux:heading size="sm" class="mb-2">{{ __('Summary') }}</flux:heading>
                <p class="text-zinc-700 dark:text-zinc-300">{{ $this->lesson->summary }}</p>
            </flux:card>
        @endif

        {{-- Content --}}
        <flux:card>
            <x-mentorship.formatted-content :content="$this->lesson->content" />
        </flux:card>

        {{-- Metadata --}}
        <flux:card>
            <flux:heading size="sm" class="mb-4">{{ __('Lesson Information') }}</flux:heading>

            <div class="grid gap-4 sm:grid-cols-2 text-sm">
                <div>
                    <span class="text-zinc-500">{{ __('Created') }}:</span>
                    <span class="ml-2">{{ $this->lesson->created_at->format('M d, Y g:i A') }}</span>
                    @if($this->lesson->creator)
                        <span class="text-zinc-500">{{ __('by') }}</span>
                        <span>{{ $this->lesson->creator->name }}</span>
                    @endif
                </div>

                @if($this->lesson->updated_at != $this->lesson->created_at)
                    <div>
                        <span class="text-zinc-500">{{ __('Last Updated') }}:</span>
                        <span class="ml-2">{{ $this->lesson->updated_at->format('M d, Y g:i A') }}</span>
                        @if($this->lesson->updater)
                            <span class="text-zinc-500">{{ __('by') }}</span>
                            <span>{{ $this->lesson->updater->name }}</span>
                        @endif
                    </div>
                @endif
            </div>
        </flux:card>

        {{-- Actions --}}
        <div class="flex justify-between">
            <flux:button
                variant="danger"
                wire:click="delete"
                wire:confirm="{{ __('Are you sure you want to delete this lesson?') }}"
                icon="trash"
            >
                {{ __('Delete Lesson') }}
            </flux:button>

            <flux:button
                :variant="$this->lesson->is_published ? 'ghost' : 'primary'"
                wire:click="togglePublish"
                :icon="$this->lesson->is_published ? 'eye-slash' : 'eye'"
            >
                {{ $this->lesson->is_published ? __('Unpublish') : __('Publish') }}
            </flux:button>
        </div>
    </div>

    @script
    <script>
        $wire.on('publish-toggled', () => {
            Flux.toast({ text: '{{ __('Publish status updated.') }}', heading: '{{ __('Success') }}', variant: 'success' })
        })
    </script>
    @endscript
</flux:main>
