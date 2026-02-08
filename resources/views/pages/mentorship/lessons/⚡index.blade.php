<?php

use App\Models\MentorshipLesson;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new
#[Layout('layouts.mentorship')]
#[Title('Lessons Library')]
class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $categoryFilter = '';

    #[Url]
    public string $publishFilter = '';

    #[Computed]
    public function lessons()
    {
        $query = MentorshipLesson::with(['sourceTopic', 'creator'])
            ->latest();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                  ->orWhere('content', 'like', "%{$this->search}%");
            });
        }

        if ($this->categoryFilter) {
            $query->byCategory($this->categoryFilter);
        }

        if ($this->publishFilter === 'published') {
            $query->published();
        } elseif ($this->publishFilter === 'unpublished') {
            $query->unpublished();
        }

        return $query->paginate(15);
    }

    #[Computed]
    public function categories(): array
    {
        return MentorshipLesson::selectRaw('DISTINCT category')
            ->orderBy('category')
            ->pluck('category')
            ->toArray();
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'total' => MentorshipLesson::count(),
            'published' => MentorshipLesson::published()->count(),
            'ai_generated' => MentorshipLesson::aiGenerated()->count(),
        ];
    }

    public function togglePublish(int $lessonId): void
    {
        $lesson = MentorshipLesson::findOrFail($lessonId);
        $lesson->update([
            'is_published' => !$lesson->is_published,
            'updated_by' => auth()->id(),
        ]);

        unset($this->lessons, $this->stats);
    }

    public function delete(int $lessonId): void
    {
        $lesson = MentorshipLesson::findOrFail($lessonId);
        $lesson->delete();

        unset($this->lessons, $this->stats);
        $this->dispatch('lesson-deleted');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPublishFilter(): void
    {
        $this->resetPage();
    }
}; ?>

<flux:main>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Lessons Library') }}</flux:heading>
                <flux:subheading>{{ __('Saved educational content for reuse') }}</flux:subheading>
            </div>

            <flux:button variant="primary" :href="route('mentorship.lessons.create')" wire:navigate icon="plus">
                {{ __('Create Lesson') }}
            </flux:button>
        </div>

        {{-- Stats --}}
        <div class="grid gap-4 sm:grid-cols-3">
            <flux:card class="text-center">
                <div class="text-2xl font-bold">{{ $this->stats['total'] }}</div>
                <flux:subheading>{{ __('Total Lessons') }}</flux:subheading>
            </flux:card>
            <flux:card class="text-center">
                <div class="text-2xl font-bold text-green-600">{{ $this->stats['published'] }}</div>
                <flux:subheading>{{ __('Published') }}</flux:subheading>
            </flux:card>
            <flux:card class="text-center">
                <div class="text-2xl font-bold text-purple-600">{{ $this->stats['ai_generated'] }}</div>
                <flux:subheading>{{ __('AI Generated') }}</flux:subheading>
            </flux:card>
        </div>

        {{-- Filters --}}
        <div class="flex flex-col gap-4 sm:flex-row">
            <flux:input wire:model.live.debounce.300ms="search" :placeholder="__('Search lessons...')" icon="magnifying-glass" class="sm:max-w-xs" />

            <flux:select wire:model.live="categoryFilter" class="sm:max-w-xs">
                <option value="">{{ __('All Categories') }}</option>
                @foreach($this->categories as $category)
                    <option value="{{ $category }}">{{ $category }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="publishFilter" class="sm:max-w-xs">
                <option value="">{{ __('All Status') }}</option>
                <option value="published">{{ __('Published') }}</option>
                <option value="unpublished">{{ __('Unpublished') }}</option>
            </flux:select>
        </div>

        {{-- Lessons List --}}
        @if($this->lessons->isNotEmpty())
            <div class="space-y-4">
                @foreach($this->lessons as $lesson)
                    <flux:card class="hover:shadow-md transition-shadow">
                        <div class="flex items-start gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <a href="{{ route('mentorship.lessons.show', $lesson) }}" wire:navigate
                                       class="font-semibold text-lg hover:text-purple-600 dark:hover:text-purple-400">
                                        {{ $lesson->title }}
                                    </a>
                                    @if($lesson->is_ai_generated)
                                        <flux:badge color="purple" size="sm">{{ __('AI') }}</flux:badge>
                                    @endif
                                    @if(!$lesson->is_published)
                                        <flux:badge color="zinc" size="sm">{{ __('Draft') }}</flux:badge>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2 mb-2">
                                    <flux:badge :color="$lesson->category_color" size="sm">{{ $lesson->category }}</flux:badge>
                                    @if($lesson->sourceTopic)
                                        <span class="text-sm text-zinc-500">
                                            {{ __('from') }}
                                            <a href="{{ route('mentorship.topics.show', $lesson->sourceTopic) }}" wire:navigate
                                               class="hover:underline">{{ $lesson->sourceTopic->title }}</a>
                                        </span>
                                    @endif
                                </div>

                                <p class="text-sm text-zinc-600 dark:text-zinc-400 line-clamp-2">
                                    {{ $lesson->summary_or_preview }}
                                </p>

                                <div class="flex items-center gap-4 mt-2 text-xs text-zinc-500">
                                    <span>{{ __('Created') }} {{ $lesson->created_at->diffForHumans() }}</span>
                                    @if($lesson->creator)
                                        <span>{{ __('by') }} {{ $lesson->creator->name }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <flux:button variant="ghost" size="sm" :href="route('mentorship.lessons.show', $lesson)" wire:navigate icon="eye" />
                                <flux:button variant="ghost" size="sm" :href="route('mentorship.lessons.edit', $lesson)" wire:navigate icon="pencil" />
                                <flux:button variant="ghost" size="sm"
                                    wire:click="togglePublish({{ $lesson->id }})"
                                    :icon="$lesson->is_published ? 'eye-slash' : 'eye'"
                                    :title="$lesson->is_published ? __('Unpublish') : __('Publish')" />
                                <flux:button variant="ghost" size="sm"
                                    wire:click="delete({{ $lesson->id }})"
                                    wire:confirm="{{ __('Are you sure you want to delete this lesson?') }}"
                                    icon="trash"
                                    class="text-red-600" />
                            </div>
                        </div>
                    </flux:card>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $this->lessons->links() }}
            </div>
        @else
            <flux:card class="text-center py-12">
                <flux:icon.book-open class="mx-auto size-12 text-zinc-400" />
                <flux:heading size="lg" class="mt-4">{{ __('No Lessons Found') }}</flux:heading>
                <flux:subheading>{{ __('Create your first lesson or generate one with AI.') }}</flux:subheading>
                <flux:button variant="primary" :href="route('mentorship.lessons.create')" wire:navigate class="mt-4">
                    {{ __('Create Lesson') }}
                </flux:button>
            </flux:card>
        @endif
    </div>

    @script
    <script>
        $wire.on('lesson-deleted', () => {
            Flux.toast({ text: '{{ __('Lesson deleted.') }}', heading: '{{ __('Success') }}', variant: 'success' })
        })
    </script>
    @endscript
</flux:main>
