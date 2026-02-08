<?php

use App\Models\MentorshipTopic;
use App\Models\MentorshipLesson;
use App\Models\MentorshipSession;
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
#[Title('Start Session')]
class extends Component {
    use MentorshipValidationRules;
    use WithFileUploads;

    #[Locked]
    public int $topicId;

    // Step tracking (starts at 2 since topic is pre-selected)
    public int $step = 2;

    // Lesson preparation
    public string $lessonSource = 'write'; // write, library, generate
    public ?int $selectedLessonId = null;
    public array $lessonContent = [];
    public bool $isGenerating = false;

    // File upload
    public $pendingUpload;

    // Session details
    public string $sessionDate = '';
    public string $startTime = '';
    public string $sessionNotes = '';

    // Save lesson options
    public bool $saveLessonToLibrary = false;
    public string $lessonTitle = '';
    public string $lessonVisibility = 'private';

    public function mount(MentorshipTopic $topic): void
    {
        $this->topicId = $topic->id;
        $this->sessionDate = today()->format('Y-m-d');
        $this->startTime = now()->format('H:i');
        $this->lessonTitle = $topic->title;
        $this->lessonContent = MentorshipLessonService::emptyContent();

        // Pre-fill lesson content if topic has AI lesson
        if ($topic->ai_lesson_content) {
            $this->lessonContent = MentorshipLessonService::wrapTextContent($topic->ai_lesson_content);
            $this->lessonSource = 'write';
        } elseif (app(MentorshipLessonService::class)->isAiAvailable()) {
            $this->lessonSource = 'generate';
        }
    }

    #[Computed]
    public function topic(): MentorshipTopic
    {
        return MentorshipTopic::with(['savedLesson'])->findOrFail($this->topicId);
    }

    #[Computed]
    public function availableLessons()
    {
        return MentorshipLesson::availableToMentor(auth()->id())
            ->published()
            ->where('category', $this->topic->category)
            ->orderBy('title')
            ->get();
    }

    #[Computed]
    public function aiAvailable(): bool
    {
        return app(MentorshipLessonService::class)->isAiAvailable();
    }

    public function setLessonSource(string $source): void
    {
        $this->lessonSource = $source;

        if ($source !== 'library') {
            $this->selectedLessonId = null;
        }

        $this->resetValidation();
    }

    public function goToStep(int $step): void
    {
        if ($step < $this->step) {
            $this->step = $step;
        }
    }

    public function generateLesson(): void
    {
        $this->isGenerating = true;

        try {
            $service = app(MentorshipLessonService::class);
            $generated = $service->generateLesson($this->topic);

            if ($generated) {
                $this->lessonContent = $generated;
                $this->dispatch('lesson-generated');
            } else {
                $this->dispatch('generation-failed');
            }
        } catch (\Exception) {
            $this->dispatch('generation-failed');
        }

        $this->isGenerating = false;
    }

    public function updatedSelectedLessonId($value): void
    {
        if ($value) {
            $lesson = MentorshipLesson::find($value);
            if ($lesson) {
                $this->lessonContent = $lesson->content;
                $this->lessonTitle = $lesson->title;
            }
        } else {
            $this->lessonContent = MentorshipLessonService::emptyContent();
        }
    }

    public function proceedToSessionDetails(): void
    {
        $this->resetValidation();

        if ($this->lessonSource === 'library') {
            if (!$this->selectedLessonId) {
                $this->addError('selectedLessonId', __('Please select a lesson from the library.'));
                return;
            }
            if (empty($this->lessonContent['sections'] ?? [])) {
                $lesson = MentorshipLesson::find($this->selectedLessonId);
                if ($lesson) {
                    $this->lessonContent = $lesson->content;
                }
            }
        } else {
            if (empty($this->lessonContent['sections'] ?? [])) {
                $this->addError('lessonContent', __('Please prepare lesson content before proceeding.'));
                return;
            }
        }

        $this->step = 3;
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

    public function startSession(): void
    {
        $this->validate([
            'sessionDate' => ['required', 'date'],
            'startTime' => ['required', 'date_format:H:i'],
        ]);

        $session = MentorshipSession::create([
            'topic_id' => $this->topicId,
            'mentor_id' => auth()->id(),
            'lesson_id' => $this->selectedLessonId,
            'session_date' => $this->sessionDate,
            'start_time' => $this->startTime,
            'status' => 'in_progress',
            'session_notes' => $this->sessionNotes ?: null,
            'lesson_content_snapshot' => !empty($this->lessonContent['sections']) ? $this->lessonContent : null,
            'created_by' => auth()->id(),
        ]);

        if ($this->saveLessonToLibrary && !empty($this->lessonContent['sections']) && $this->lessonSource !== 'library') {
            $lesson = MentorshipLesson::create([
                'title' => $this->lessonTitle ?: $this->topic->title,
                'category' => $this->topic->category,
                'content' => $this->lessonContent,
                'source_topic_id' => $this->topicId,
                'is_ai_generated' => $this->lessonSource === 'generate',
                'is_published' => true,
                'visibility' => $this->lessonVisibility,
                'created_by' => auth()->id(),
            ]);

            $session->update(['lesson_id' => $lesson->id]);
        }

        session()->flash('status', __('Session started! Good luck with your teaching.'));
        $this->redirect(route('mentorship.sessions.show', $session), navigate: true);
    }

    public function planSession(): void
    {
        $this->validate([
            'sessionDate' => ['required', 'date'],
        ]);

        $session = MentorshipSession::create([
            'topic_id' => $this->topicId,
            'mentor_id' => auth()->id(),
            'lesson_id' => $this->selectedLessonId,
            'session_date' => $this->sessionDate,
            'start_time' => $this->startTime ?: null,
            'status' => 'planned',
            'session_notes' => $this->sessionNotes ?: null,
            'lesson_content_snapshot' => !empty($this->lessonContent['sections']) ? $this->lessonContent : null,
            'created_by' => auth()->id(),
        ]);

        if ($this->saveLessonToLibrary && !empty($this->lessonContent['sections']) && $this->lessonSource !== 'library') {
            $lesson = MentorshipLesson::create([
                'title' => $this->lessonTitle ?: $this->topic->title,
                'category' => $this->topic->category,
                'content' => $this->lessonContent,
                'source_topic_id' => $this->topicId,
                'is_ai_generated' => $this->lessonSource === 'generate',
                'is_published' => true,
                'visibility' => $this->lessonVisibility,
                'created_by' => auth()->id(),
            ]);

            $session->update(['lesson_id' => $lesson->id]);
        }

        session()->flash('status', __('Session planned successfully!'));
        $this->redirect(route('mentorship.sessions.show', $session), navigate: true);
    }
}; ?>

<flux:main>
    <div class="space-y-6 max-w-4xl">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('Prepare Session') }}</flux:heading>
                <flux:subheading>{{ $this->topic->title }}</flux:subheading>
            </div>

            <flux:button variant="ghost" :href="route('mentorship.topics.show', $topicId)" wire:navigate icon="arrow-left">
                {{ __('Back to Topic') }}
            </flux:button>
        </div>

        {{-- Topic Info --}}
        <flux:card class="bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 border-purple-200 dark:border-purple-800">
            <div class="flex items-center gap-4">
                <flux:icon.academic-cap class="size-10 text-purple-600" />
                <div>
                    <h3 class="text-lg font-semibold">{{ $this->topic->title }}</h3>
                    <div class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-400">
                        <flux:badge :color="$this->topic->category_color">{{ $this->topic->category }}</flux:badge>
                        <span>{{ $this->topic->topic_date->format('l, F d, Y') }}</span>
                        <span>{{ $this->topic->time_slot_label }}</span>
                    </div>
                </div>
            </div>
        </flux:card>

        {{-- Progress Steps --}}
        <div class="flex items-center justify-center gap-4">
            @foreach([2 => 'Prepare Lesson', 3 => 'Session Details'] as $num => $label)
                <div class="flex items-center gap-2">
                    <button
                        wire:click="goToStep({{ $num }})"
                        @class([
                            'flex items-center justify-center size-8 rounded-full text-sm font-medium transition',
                            'bg-purple-600 text-white' => $step === $num,
                            'bg-green-600 text-white cursor-pointer' => $step > $num,
                            'bg-zinc-200 dark:bg-zinc-700 text-zinc-500' => $step < $num,
                        ])
                        @disabled($step < $num)
                    >
                        @if($step > $num)
                            <flux:icon.check class="size-4" />
                        @else
                            {{ $num - 1 }}
                        @endif
                    </button>
                    <span @class([
                        'text-sm',
                        'font-medium text-zinc-900 dark:text-white' => $step >= $num,
                        'text-zinc-500' => $step < $num,
                    ])>{{ __($label) }}</span>
                </div>
                @if($num < 3)
                    <div @class([
                        'w-12 h-0.5',
                        'bg-green-600' => $step > $num,
                        'bg-zinc-200 dark:bg-zinc-700' => $step <= $num,
                    ])></div>
                @endif
            @endforeach
        </div>

        {{-- Step 2: Prepare Lesson --}}
        @if($step === 2)
            <flux:card>
                <flux:heading size="lg" class="mb-4">{{ __('Prepare Lesson') }}</flux:heading>

                {{-- Lesson Source Selection (Reordered: Write → Library → AI) --}}
                <div class="flex flex-wrap gap-3 mb-6">
                    <flux:button
                        :variant="$lessonSource === 'write' ? 'primary' : 'ghost'"
                        wire:click="setLessonSource('write')"
                        icon="pencil"
                    >
                        {{ __('Write Own') }}
                    </flux:button>

                    <flux:button
                        :variant="$lessonSource === 'library' ? 'primary' : 'ghost'"
                        wire:click="setLessonSource('library')"
                        icon="book-open"
                    >
                        {{ __('From Library') }}
                    </flux:button>

                    @if($this->aiAvailable)
                        <flux:button
                            :variant="$lessonSource === 'generate' ? 'primary' : 'ghost'"
                            wire:click="setLessonSource('generate')"
                            icon="sparkles"
                        >
                            {{ __('Generate with AI') }}
                        </flux:button>
                    @endif
                </div>

                {{-- Write Own --}}
                @if($lessonSource === 'write')
                    <div class="space-y-4">
                        <x-mentorship.structured-editor wire-model="lessonContent" />
                    </div>
                @endif

                {{-- Library Selection --}}
                @if($lessonSource === 'library')
                    <div class="space-y-4">
                        @if($this->availableLessons->isNotEmpty())
                            <flux:select wire:model.live="selectedLessonId" :label="__('Select a Lesson')">
                                <option value="">{{ __('Choose a lesson...') }}</option>
                                @foreach($this->availableLessons as $lesson)
                                    <option value="{{ $lesson->id }}">
                                        {{ $lesson->title }} ({{ $lesson->category }})
                                        @if($lesson->visibility === 'shared') - {{ __('Shared') }} @endif
                                    </option>
                                @endforeach
                            </flux:select>

                            @if(!empty($lessonContent['sections']) && $selectedLessonId)
                                <x-mentorship.formatted-content :content="$lessonContent" class="p-4 rounded-lg bg-zinc-50 dark:bg-zinc-800 max-h-[500px] overflow-y-auto" />
                            @endif
                        @else
                            <div class="p-4 rounded-lg bg-zinc-50 dark:bg-zinc-800 text-center py-8">
                                <flux:icon.book-open class="mx-auto size-10 text-zinc-400 mb-3" />
                                <p class="text-zinc-500">{{ __('No lessons available for this category.') }}</p>
                                <p class="text-sm text-zinc-400 mt-1">{{ __('Try writing your own or generating one with AI.') }}</p>
                            </div>
                        @endif

                        @error('selectedLessonId')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                {{-- AI Generation --}}
                @if($lessonSource === 'generate')
                    <div class="space-y-4">
                        @if($this->aiAvailable)
                            <div class="p-4 rounded-lg bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 border border-purple-200 dark:border-purple-800">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <flux:icon.sparkles class="size-5 text-purple-600" />
                                        <div>
                                            <p class="font-medium text-purple-900 dark:text-purple-100">{{ __('AI Lesson Generation') }}</p>
                                            <p class="text-sm text-purple-700 dark:text-purple-300">{{ __('Generate a structured lesson with sections, content, and media suggestions') }}</p>
                                        </div>
                                    </div>
                                    <flux:button
                                        variant="primary"
                                        wire:click="generateLesson"
                                        :disabled="$isGenerating"
                                        icon="sparkles"
                                    >
                                        <span wire:loading.remove wire:target="generateLesson">
                                            {{ !empty($lessonContent['sections']) ? __('Regenerate') : __('Generate') }}
                                        </span>
                                        <span wire:loading wire:target="generateLesson">{{ __('Generating...') }}</span>
                                    </flux:button>
                                </div>
                            </div>
                        @endif

                        @if(!empty($lessonContent['sections']))
                            {{-- Show generated content in the structured editor for editing --}}
                            <x-mentorship.structured-editor wire-model="lessonContent" />
                        @endif
                    </div>
                @endif

                {{-- Error display for content --}}
                @error('lessonContent')
                    <div class="mt-2 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                        <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    </div>
                @enderror

                {{-- Save to Library Option --}}
                @if(!empty($lessonContent['sections']) && $lessonSource !== 'library')
                    <div class="mt-6 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <flux:checkbox wire:model.live="saveLessonToLibrary" :label="__('Save this lesson to my library')" />

                        @if($saveLessonToLibrary)
                            <div class="grid gap-4 mt-4 sm:grid-cols-2">
                                <flux:input wire:model="lessonTitle" :label="__('Lesson Title')" :placeholder="$this->topic->title" />
                                <flux:select wire:model="lessonVisibility" :label="__('Visibility')">
                                    <option value="private">{{ __('Private (only you)') }}</option>
                                    <option value="shared">{{ __('Shared (all mentors)') }}</option>
                                </flux:select>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Actions --}}
                <div class="flex justify-between mt-6 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button variant="ghost" :href="route('mentorship.topics.show', $topicId)" wire:navigate icon="arrow-left">
                        {{ __('Back to Topic') }}
                    </flux:button>

                    <flux:button variant="primary" wire:click="proceedToSessionDetails" icon-trailing="arrow-right">
                        {{ __('Continue') }}
                    </flux:button>
                </div>
            </flux:card>
        @endif

        {{-- Step 3: Session Details --}}
        @if($step === 3)
            <flux:card>
                <flux:heading size="lg" class="mb-4">{{ __('Session Details') }}</flux:heading>

                <div class="space-y-6">
                    <div class="grid gap-6 sm:grid-cols-2">
                        <flux:input
                            type="date"
                            wire:model="sessionDate"
                            :label="__('Session Date')"
                            required
                        />

                        <flux:input
                            type="time"
                            wire:model="startTime"
                            :label="__('Start Time')"
                        />
                    </div>

                    <flux:textarea
                        wire:model="sessionNotes"
                        :label="__('Session Notes (Optional)')"
                        :placeholder="__('Any notes about objectives, target audience, or special considerations...')"
                        rows="4"
                    />
                </div>

                {{-- Actions --}}
                <div class="flex flex-col sm:flex-row justify-between gap-4 mt-6 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button variant="ghost" wire:click="goToStep(2)" icon="arrow-left">
                        {{ __('Back') }}
                    </flux:button>

                    <div class="flex gap-3">
                        <flux:button variant="ghost" wire:click="planSession" icon="calendar">
                            {{ __('Plan for Later') }}
                        </flux:button>

                        <flux:button variant="primary" wire:click="startSession" icon="play">
                            {{ __('Start Session Now') }}
                        </flux:button>
                    </div>
                </div>
            </flux:card>
        @endif
    </div>

    @script
    <script>
        $wire.on('lesson-generated', () => {
            Flux.toast({ text: '{{ __('Lesson generated successfully!') }}', heading: '{{ __('Success') }}', variant: 'success' })
        })
        $wire.on('generation-failed', () => {
            Flux.toast({ text: '{{ __('Failed to generate lesson. Please try again or use another method.') }}', heading: '{{ __('Error') }}', variant: 'danger' })
        })
    </script>
    @endscript
</flux:main>
