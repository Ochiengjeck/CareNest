<?php

use App\Models\MentorshipLesson;
use App\Concerns\MentorshipValidationRules;
use App\Services\MentorshipLessonService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

new
#[Layout('layouts.mentorship')]
#[Title('Create Lesson')]
class extends Component {
    use MentorshipValidationRules;
    use WithFileUploads;

    public string $title = '';
    public string $category = 'Mental Health';
    public array $content = [];
    public string $summary = '';
    public bool $is_published = false;
    public string $visibility = 'private';

    public bool $isGenerating = false;

    // File upload
    public $pendingUpload;

    public function mount(): void
    {
        $this->content = MentorshipLessonService::emptyContent();
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

    public function generateWithAi(): void
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
            $this->dispatch('content-generated');
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

        // Generate summary if content is provided but summary is empty
        if (!empty($this->content['sections']) && empty($this->summary)) {
            $service = app(MentorshipLessonService::class);
            $this->summary = $service->generateSummary($this->content) ?? '';
        }

        $lesson = MentorshipLesson::create([
            'title' => $this->title,
            'category' => $this->category,
            'content' => $this->content,
            'summary' => $this->summary ?: null,
            'is_ai_generated' => false,
            'is_published' => $this->is_published,
            'visibility' => $this->visibility,
            'created_by' => auth()->id(),
        ]);

        session()->flash('status', __('Lesson created successfully!'));
        $this->redirect(route('mentorship.lessons.show', $lesson), navigate: true);
    }

    public function saveAsAiGenerated(): void
    {
        $this->validate($this->mentorshipLessonRules());

        // Generate summary if needed
        if (!empty($this->content['sections']) && empty($this->summary)) {
            $service = app(MentorshipLessonService::class);
            $this->summary = $service->generateSummary($this->content) ?? '';
        }

        $lesson = MentorshipLesson::create([
            'title' => $this->title,
            'category' => $this->category,
            'content' => $this->content,
            'summary' => $this->summary ?: null,
            'is_ai_generated' => true,
            'is_published' => $this->is_published,
            'visibility' => $this->visibility,
            'created_by' => auth()->id(),
        ]);

        session()->flash('status', __('AI-generated lesson saved successfully!'));
        $this->redirect(route('mentorship.lessons.show', $lesson), navigate: true);
    }
}; ?>

<flux:main>
    <div class="space-y-6 max-w-4xl">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('Create Lesson') }}</flux:heading>
                <flux:subheading>{{ __('Add a new lesson to the library') }}</flux:subheading>
            </div>

            <flux:button variant="ghost" :href="route('mentorship.lessons.index')" wire:navigate icon="arrow-left">
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
                        :placeholder="__('Understanding Emotional Regulation')"
                        required
                    />

                    <flux:select wire:model="category" :label="__('Category')" required>
                        @foreach($this->categoriesList as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </flux:select>
                </div>

                {{-- AI Generation --}}
                @if($this->aiAvailable)
                    <div class="p-4 rounded-lg bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 border border-purple-200 dark:border-purple-800">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <flux:icon.sparkles class="size-5 text-purple-600" />
                                <div>
                                    <p class="font-medium text-purple-900 dark:text-purple-100">{{ __('AI Lesson Generation') }}</p>
                                    <p class="text-sm text-purple-700 dark:text-purple-300">{{ __('Generate structured lesson content based on the title and category') }}</p>
                                </div>
                            </div>
                            <flux:button
                                type="button"
                                variant="primary"
                                wire:click="generateWithAi"
                                :disabled="$isGenerating || empty($title)"
                                icon="sparkles"
                            >
                                <span wire:loading.remove wire:target="generateWithAi">{{ __('Generate') }}</span>
                                <span wire:loading wire:target="generateWithAi">{{ __('Generating...') }}</span>
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
                    :description="__('A brief preview. Leave empty to auto-generate.')"
                    rows="3"
                />

                {{-- Options --}}
                <div class="grid gap-6 sm:grid-cols-2">
                    <flux:select wire:model="visibility" :label="__('Visibility')">
                        <option value="private">{{ __('Private (only you can use)') }}</option>
                        <option value="shared">{{ __('Shared (available to all mentors)') }}</option>
                    </flux:select>

                    <div class="flex items-end">
                        <flux:checkbox wire:model="is_published" :label="__('Publish immediately')" />
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button variant="ghost" type="button" :href="route('mentorship.lessons.index')" wire:navigate>
                        {{ __('Cancel') }}
                    </flux:button>
                    @if($this->aiAvailable && !empty($content['sections']))
                        <flux:button variant="filled" type="button" wire:click="saveAsAiGenerated" icon="sparkles">
                            {{ __('Save as AI Generated') }}
                        </flux:button>
                    @endif
                    <flux:button variant="primary" type="submit">
                        {{ __('Save Lesson') }}
                    </flux:button>
                </div>
            </flux:card>
        </form>
    </div>

    @script
    <script>
        $wire.on('content-generated', () => {
            Flux.toast({ text: '{{ __('Lesson content generated!') }}', heading: '{{ __('Success') }}', variant: 'success' })
        })
        $wire.on('generation-failed', () => {
            Flux.toast({ text: '{{ __('Failed to generate content. Please try again.') }}', heading: '{{ __('Error') }}', variant: 'danger' })
        })
    </script>
    @endscript
</flux:main>
