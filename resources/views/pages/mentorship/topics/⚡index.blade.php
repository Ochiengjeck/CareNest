<?php

use App\Models\MentorshipTopic;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new
#[Layout('layouts.mentorship')]
#[Title('Manage Topics')]
class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $categoryFilter = '';

    #[Url]
    public string $publishFilter = '';

    #[Computed]
    public function topics()
    {
        return MentorshipTopic::query()
            ->with(['attachments', 'creator'])
            ->when($this->search, fn($q) => $q->where(function($query) {
                $query->where('title', 'like', "%{$this->search}%")
                    ->orWhere('category', 'like', "%{$this->search}%");
            }))
            ->when($this->categoryFilter, fn($q) => $q->byCategory($this->categoryFilter))
            ->when($this->publishFilter === 'published', fn($q) => $q->published())
            ->when($this->publishFilter === 'unpublished', fn($q) => $q->unpublished())
            ->latest('topic_date')
            ->latest('time_slot')
            ->paginate(20);
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

    public function togglePublish(int $topicId): void
    {
        $topic = MentorshipTopic::findOrFail($topicId);
        $topic->update([
            'is_published' => !$topic->is_published,
            'updated_by' => auth()->id(),
        ]);

        unset($this->topics);

        $this->dispatch('topic-updated');
    }

    public function delete(int $topicId): void
    {
        MentorshipTopic::findOrFail($topicId)->delete();
        unset($this->topics);
        $this->dispatch('topic-deleted');
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
                <flux:heading size="xl">{{ __('Manage Topics') }}</flux:heading>
                <flux:subheading>{{ __('Create, edit, and organize mentorship topics') }}</flux:subheading>
            </div>

            <div class="flex gap-2">
                <flux:button variant="ghost" :href="route('mentorship.import.csv')" wire:navigate icon="arrow-up-tray">
                    {{ __('Import CSV') }}
                </flux:button>
                <flux:button variant="primary" :href="route('mentorship.topics.create')" wire:navigate icon="plus">
                    {{ __('Add Topic') }}
                </flux:button>
            </div>
        </div>

        {{-- Filters --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
            <flux:input
                wire:model.live.debounce.300ms="search"
                :placeholder="__('Search topics...')"
                icon="magnifying-glass"
                class="sm:max-w-xs"
            />

            <flux:select wire:model.live="categoryFilter" class="sm:max-w-xs">
                <option value="">{{ __('All Categories') }}</option>
                @foreach($this->categories as $category)
                    <option value="{{ $category }}">{{ $category }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="publishFilter" class="sm:max-w-xs">
                <option value="">{{ __('All Statuses') }}</option>
                <option value="published">{{ __('Published') }}</option>
                <option value="unpublished">{{ __('Unpublished') }}</option>
            </flux:select>
        </div>

        {{-- Topics List --}}
        @if($this->topics->isEmpty())
            <flux:card class="py-12">
                <div class="text-center">
                    <flux:icon.clipboard-document-list class="mx-auto h-12 w-12 text-zinc-300 dark:text-zinc-600" />
                    <h3 class="mt-4 text-lg font-medium">{{ __('No topics found') }}</h3>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Get started by creating a topic or importing from CSV.') }}
                    </p>
                </div>
            </flux:card>
        @else
            <flux:card>
                <div class="space-y-3">
                    @foreach($this->topics as $topic)
                        <div class="flex items-start gap-4 p-4 rounded-lg bg-zinc-50 dark:bg-zinc-800 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">
                            <div class="flex-shrink-0 text-center min-w-[60px]">
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $topic->day_of_week }}</div>
                                <div class="text-lg font-bold">{{ $topic->topic_date->format('M d') }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $topic->time_slot_label }}</div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-start gap-2 mb-2">
                                    <h4 class="font-semibold">{{ $topic->title }}</h4>
                                </div>

                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    <flux:badge :color="$topic->category_color" size="sm">{{ $topic->category }}</flux:badge>

                                    @if(!$topic->is_published)
                                        <flux:badge color="zinc" size="sm">{{ __('Unpublished') }}</flux:badge>
                                    @endif

                                    @if($topic->attachments->isNotEmpty())
                                        <div class="flex items-center gap-1 text-xs text-zinc-500">
                                            <flux:icon.paper-clip class="size-3" />
                                            {{ $topic->attachments->count() }}
                                        </div>
                                    @endif
                                </div>

                                @if($topic->description)
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400 line-clamp-2">
                                        {{ Str::limit($topic->description, 150) }}
                                    </p>
                                @endif
                            </div>

                            <div class="flex items-center gap-1">
                                <flux:button variant="ghost" size="sm" :href="route('mentorship.topics.show', $topic)" wire:navigate icon="eye" />
                                <flux:button variant="ghost" size="sm" :href="route('mentorship.topics.edit', $topic)" wire:navigate icon="pencil" />
                                <flux:button
                                    variant="ghost"
                                    size="sm"
                                    wire:click="togglePublish({{ $topic->id }})"
                                    :icon="$topic->is_published ? 'eye-slash' : 'eye'"
                                    :title="$topic->is_published ? __('Unpublish') : __('Publish')"
                                />
                                <flux:button
                                    variant="ghost"
                                    size="sm"
                                    wire:click="delete({{ $topic->id }})"
                                    wire:confirm="{{ __('Are you sure you want to delete this topic?') }}"
                                    icon="trash"
                                    class="text-red-600"
                                />
                            </div>
                        </div>
                    @endforeach
                </div>
            </flux:card>

            <div class="mt-6">
                {{ $this->topics->links() }}
            </div>
        @endif
    </div>

    @script
    <script>
        $wire.on('topic-updated', () => {
            Flux.toast({ text: '{{ __('Topic has been updated.') }}', heading: '{{ __('Success') }}', variant: 'success' })
        })

        $wire.on('topic-deleted', () => {
            Flux.toast({ text: '{{ __('Topic has been deleted.') }}', heading: '{{ __('Success') }}', variant: 'success' })
        })
    </script>
    @endscript
</flux:main>
