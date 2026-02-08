<?php

use App\Models\MentorshipLesson;
use App\Concerns\MentorshipValidationRules;
use App\Services\MentorshipLessonService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

new
#[Layout('layouts.mentorship')]
#[Title('Edit Lesson')]
class extends Component {
    use MentorshipValidationRules;
    use WithFileUploads;

    #[Locked]
    public int $lessonId;

    public string $title = '';
    public string $category = '';
    public array $content = [];
    public string $summary = '';
    public bool $is_published = false;
    public string $visibility = 'private';

    public bool $isGenerating = false;

    // File upload
    public $pendingUpload;

    public function mount(MentorshipLesson $lesson): void
    {
        $this->lessonId = $lesson->id;
        $this->title = $lesson->title;
        $this->category = $lesson->category;
        $this->content = is_array($lesson->content) ? $lesson->content : MentorshipLessonService::wrapTextContent($lesson->content ?? '');
        $this->summary = $lesson->summary ?? '';
        $this->is_published = $lesson->is_published;
        $this->visibility = $lesson->visibility ?? 'private';
    }

    #[Computed]
    public function lesson(): MentorshipLesson
    {
        return MentorshipLesson::findOrFail($this->lessonId);
    }

    #[Computed]
    public function categoriesList(): array
    {
        return [
            'Mental Health',
            'Substance Use Disorder',
            'Employment/Education',
            'Physical Health',
            'Financial/Housing',
            'Psycho-Social/Family',
            'Spirituality',
        ];
    }

    #[Computed]
    public function aiAvailable(): bool
    {
        return app(MentorshipLessonService::class)->isAiAvailable();
    }

    public function regenerateWithAi(): void
    {
        if (empty($this->title)) {
            $this->addError('title', __('Please enter a title before generating.'));
            return;
        }

        $this->isGenerating = true;

        $service = app(MentorshipLessonService::class);
        $generated = $service->generateLessonFromInput($this->title, $this->category, null);

        if ($generated) {
            $this->content = $generated;
            $this->dispatch('content-regenerated');
        } else {
            $this->dispatch('generation-failed');
        }

        $this->isGenerating = false;
    }

    public function storeUploadedMedia(string $tmpFilename, string $originalName, string $type): ?string
    {
        if (!$this->pendingUpload) {
            return null;
        }

        $path = $this->pendingUpload->store('mentorship/media', 'public');
        $this->pendingUpload = null;

        return $path;
    }

    public function removeUploadedMedia(string $path): void
    {
        Storage::disk('public')->delete($path);
    }

    public function save(): void
    {
        $this->validate($this->mentorshipLessonRules());

        // Regenerate summary if content changed significantly
        if (empty($this->summary) && !empty($this->content['sections'])) {
            $service = app(MentorshipLessonService::class);
            $this->summary = $service->generateSummary($this->content) ?? '';
        }

        $this->lesson->update([
            'title' => $this->title,
            'category' => $this->category,
            'content' => $this->content,
            'summary' => $this->summary ?: null,
            'is_published' => $this->is_published,
            'visibility' => $this->visibility,
            'updated_by' => auth()->id(),
        ]);

        session()->flash('status', __('Lesson updated successfully!'));
        $this->redirect(route('mentorship.lessons.show', $this->lessonId), navigate: true);
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
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('Edit Lesson') }}</flux:heading>
                <flux:subheading>{{ $this->lesson->title }}</flux:subheading>
            </div>

            <flux:button variant="ghost" :href="route('mentorship.lessons.show', $lessonId)" wire:navigate icon="arrow-left">
                {{ __('Back') }}
            </flux:button>
        </div>

        <form wire:submit="save">
            <flux:card class="space-y-6">
                {{-- Basic Info --}}
                <div class="grid gap-6 sm:grid-cols-2">
                    <flux:input
                        wire:model="title"
                        :label="__('Title')"
                        required
                    />

                    <flux:select wire:model="category" :label="__('Category')" required>
                        @foreach($this->categoriesList as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </flux:select>
                </div>

                {{-- AI Regeneration --}}
                @if($this->aiAvailable)
                    <div class="p-4 rounded-lg bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 border border-purple-200 dark:border-purple-800">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <flux:icon.sparkles class="size-5 text-purple-600" />
                                <div>
                                    <p class="font-medium text-purple-900 dark:text-purple-100">{{ __('Regenerate with AI') }}</p>
                                    <p class="text-sm text-purple-700 dark:text-purple-300">{{ __('Replace content with a new AI-generated lesson') }}</p>
                                </div>
                            </div>
                            <flux:button
                                type="button"
                                variant="ghost"
                                wire:click="regenerateWithAi"
                                wire:confirm="{{ __('This will replace the current content. Continue?') }}"
                                :disabled="$isGenerating"
                                icon="arrow-path"
                            >
                                <span wire:loading.remove wire:target="regenerateWithAi">{{ __('Regenerate') }}</span>
                                <span wire:loading wire:target="regenerateWithAi">{{ __('Generating...') }}</span>
                            </flux:button>
                        </div>
                    </div>
                @endif

                {{-- Content --}}
                <div>
                    <flux:heading size="sm" class="mb-3">{{ __('Lesson Content') }}</flux:heading>
                    <x-mentorship.structured-editor wire-model="content" />
                </div>

                {{-- Summary --}}
                <flux:textarea
                    wire:model="summary"
                    :label="__('Summary (Optional)')"
                    :description="__('A brief preview for listing pages.')"
                    rows="3"
                />

                {{-- Options --}}
                <div class="grid gap-6 sm:grid-cols-2">
                    <flux:select wire:model="visibility" :label="__('Visibility')">
                        <option value="private">{{ __('Private (only you can use)') }}</option>
                        <option value="shared">{{ __('Shared (available to all mentors)') }}</option>
                    </flux:select>

                    <div class="flex items-end">
                        <flux:checkbox wire:model="is_published" :label="__('Published')" />
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex justify-between pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button
                        variant="danger"
                        type="button"
                        wire:click="delete"
                        wire:confirm="{{ __('Are you sure you want to delete this lesson?') }}"
                        icon="trash"
                    >
                        {{ __('Delete') }}
                    </flux:button>

                    <div class="flex gap-3">
                        <flux:button variant="ghost" type="button" :href="route('mentorship.lessons.show', $lessonId)" wire:navigate>
                            {{ __('Cancel') }}
                        </flux:button>
                        <flux:button variant="primary" type="submit">
                            {{ __('Save Changes') }}
                        </flux:button>
                    </div>
                </div>
            </flux:card>
        </form>
    </div>

    @script
    <script>
        $wire.on('content-regenerated', () => {
            Flux.toast({ text: '{{ __('Content regenerated!') }}', heading: '{{ __('Success') }}', variant: 'success' })
        })
        $wire.on('generation-failed', () => {
            Flux.toast({ text: '{{ __('Failed to generate content. Please try again.') }}', heading: '{{ __('Error') }}', variant: 'danger' })
        })
    </script>
    @endscript
</flux:main>
