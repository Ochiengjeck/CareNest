<?php

use App\Models\MentorshipTopic;
use App\Models\MentorshipAttachment;
use App\Models\MentorshipLesson;
use App\Concerns\MentorshipValidationRules;
use App\Services\MentorshipLessonService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new
#[Layout('layouts.mentorship')]
#[Title('Create Topic')]
class extends Component {
    use MentorshipValidationRules, WithFileUploads;

    public string $topic_date = '';
    public string $day_of_week = '';
    public string $time_slot = '10:00';
    public string $title = '';
    public string $category = 'Mental Health';
    public string $description = '';
    public bool $is_published = true;

    public array $attachments = [];

    // AI Lesson
    public ?string $aiLessonContent = null;
    public bool $isGenerating = false;

    public function mount(): void
    {
        $this->topic_date = now()->format('Y-m-d');
        $this->day_of_week = now()->format('D');
    }

    #[Computed]
    public function aiAvailable(): bool
    {
        return app(MentorshipLessonService::class)->isAiAvailable();
    }

    public function generateLesson(): void
    {
        if (empty($this->title)) {
            $this->addError('title', __('Please enter a title before generating a lesson.'));
            return;
        }

        $this->isGenerating = true;

        $service = app(MentorshipLessonService::class);
        $generated = $service->generateLessonFromInput($this->title, $this->category, $this->description);

        if ($generated) {
            $this->aiLessonContent = $generated;
            $this->dispatch('lesson-generated');
        } else {
            $this->dispatch('generation-failed');
        }

        $this->isGenerating = false;
    }

    public function discardLesson(): void
    {
        $this->aiLessonContent = null;
    }

    public function updatedTopicDate(): void
    {
        if ($this->topic_date) {
            $this->day_of_week = \Carbon\Carbon::parse($this->topic_date)->format('D');
        }
    }

    public function save(): void
    {
        $this->validate($this->mentorshipTopicRules());

        if (!empty($this->attachments)) {
            $this->validate($this->mentorshipAttachmentRules());
        }

        $topic = MentorshipTopic::create([
            'topic_date' => $this->topic_date,
            'day_of_week' => $this->day_of_week,
            'time_slot' => $this->time_slot,
            'title' => $this->title,
            'category' => $this->category,
            'description' => $this->description ?: null,
            'is_published' => $this->is_published,
            'created_by' => auth()->id(),
            'ai_lesson_content' => $this->aiLessonContent,
            'ai_lesson_generated_at' => $this->aiLessonContent ? now() : null,
        ]);

        // Handle attachments
        foreach ($this->attachments as $file) {
            if ($file) {
                $path = $file->store('mentorship/attachments', 'public');

                MentorshipAttachment::create([
                    'topic_id' => $topic->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getClientOriginalExtension(),
                    'file_size' => $file->getSize(),
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        session()->flash('status', __('Topic created successfully!'));
        $this->redirect(route('mentorship.topics.show', $topic), navigate: true);
    }

    public function addAttachment(): void
    {
        $this->attachments[] = null;
    }

    public function removeAttachment(int $index): void
    {
        unset($this->attachments[$index]);
        $this->attachments = array_values($this->attachments);
    }

    #[\Livewire\Attributes\Computed]
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

    #[\Livewire\Attributes\Computed]
    public function timeSlotsList(): array
    {
        return [
            '10:00' => '10:00 AM',
            '14:00' => '2:00 PM',
            '18:00' => '6:00 PM',
        ];
    }
}; ?>

<flux:main>
    <div class="space-y-6 max-w-3xl">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('Create Topic') }}</flux:heading>
                <flux:subheading>{{ __('Add a new mentorship topic') }}</flux:subheading>
            </div>

            <flux:button variant="ghost" :href="route('mentorship.topics.index')" wire:navigate icon="arrow-left">
                {{ __('Back') }}
            </flux:button>
        </div>

        <form wire:submit="save">
            <flux:card class="space-y-6">
                {{-- Basic Info --}}
                <div class="grid gap-6 sm:grid-cols-2">
                    <flux:input
                        wire:model.live="topic_date"
                        :label="__('Date')"
                        type="date"
                        required
                    />

                    <flux:input
                        wire:model="day_of_week"
                        :label="__('Day of Week')"
                        readonly
                        disabled
                    />

                    <flux:select wire:model="time_slot" :label="__('Time Slot')" required>
                        @foreach($this->timeSlotsList as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model="category" :label="__('Category')" required>
                        @foreach($this->categoriesList as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </flux:select>
                </div>

                <flux:input
                    wire:model="title"
                    :label="__('Title')"
                    :placeholder="__('DBT-INFORMED– Mindfulness of Thoughts')"
                    required
                />

                <flux:textarea
                    wire:model="description"
                    :label="__('Description')"
                    :description="__('Optional detailed description of the topic')"
                    rows="6"
                    :placeholder="__('Provide context, learning objectives, discussion points...')"
                />

                {{-- AI Lesson Generation --}}
                @if($this->aiAvailable)
                    <flux:separator />

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <flux:icon.sparkles class="size-5 text-purple-600" />
                                <div>
                                    <flux:heading size="lg">{{ __('AI Lesson Generation') }}</flux:heading>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Generate educational content for this topic') }}</p>
                                </div>
                            </div>

                            @if(!$aiLessonContent)
                                <flux:button
                                    type="button"
                                    variant="primary"
                                    wire:click="generateLesson"
                                    :disabled="$isGenerating || empty($title)"
                                    icon="sparkles"
                                >
                                    <span wire:loading.remove wire:target="generateLesson">{{ __('Generate Lesson') }}</span>
                                    <span wire:loading wire:target="generateLesson">{{ __('Generating...') }}</span>
                                </flux:button>
                            @endif
                        </div>

                        @if($aiLessonContent)
                            <div class="p-4 rounded-lg bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 border border-purple-200 dark:border-purple-800">
                                <div class="flex items-center justify-between mb-3">
                                    <flux:badge color="purple" icon="sparkles">{{ __('AI Generated') }}</flux:badge>
                                    <div class="flex gap-2">
                                        <flux:button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            wire:click="generateLesson"
                                            wire:confirm="{{ __('This will replace the current content. Continue?') }}"
                                            :disabled="$isGenerating"
                                            icon="arrow-path"
                                        >
                                            {{ __('Regenerate') }}
                                        </flux:button>
                                        <flux:button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            wire:click="discardLesson"
                                            icon="trash"
                                            class="text-red-600"
                                        >
                                            {{ __('Discard') }}
                                        </flux:button>
                                    </div>
                                </div>

                                <div class="prose prose-sm prose-zinc dark:prose-invert max-w-none max-h-96 overflow-y-auto">
                                    {!! nl2br(e($aiLessonContent)) !!}
                                </div>

                                <p class="text-xs text-purple-600 dark:text-purple-400 mt-3">
                                    {{ __('This lesson will be saved with the topic when you click "Create Topic".') }}
                                </p>
                            </div>
                        @elseif(empty($title))
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 italic">
                                {{ __('Enter a title to enable AI lesson generation.') }}
                            </p>
                        @endif
                    </div>
                @endif

                <flux:checkbox wire:model="is_published" :label="__('Publish immediately')" />

                <flux:separator />

                {{-- Attachments --}}
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <flux:heading size="lg">{{ __('Attachments') }}</flux:heading>
                        <flux:button type="button" variant="ghost" size="sm" wire:click="addAttachment" icon="plus">
                            {{ __('Add Attachment') }}
                        </flux:button>
                    </div>

                    @if(empty($this->attachments))
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('No attachments yet. Click "Add Attachment" to upload files.') }}</p>
                    @else
                        <div class="space-y-4">
                            @foreach($attachments as $index => $attachment)
                                <div class="flex items-start gap-4 p-4 rounded-lg bg-zinc-50 dark:bg-zinc-800">
                                    <div class="flex-1">
                                        <flux:label>{{ __('File') }} #{{ $index + 1 }}</flux:label>
                                        <input
                                            type="file"
                                            wire:model="attachments.{{ $index }}"
                                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                            class="mt-1 block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 dark:file:bg-zinc-700 dark:file:text-zinc-300"
                                        >
                                        @error("attachments.{$index}")
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                        <p class="text-xs text-zinc-500 mt-1">{{ __('Max 10MB. PDF, DOC, DOCX, JPG, PNG') }}</p>
                                    </div>

                                    <flux:button type="button" variant="ghost" size="sm" wire:click="removeAttachment({{ $index }})" icon="trash" class="text-red-600" />
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex justify-end gap-3 pt-4">
                    <flux:button variant="ghost" type="button" :href="route('mentorship.topics.index')" wire:navigate>
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button variant="primary" type="submit">
                        {{ __('Create Topic') }}
                    </flux:button>
                </div>
            </flux:card>
        </form>
    </div>

    @script
    <script>
        $wire.on('lesson-generated', () => {
            Flux.toast({ text: '{{ __('Lesson content generated successfully!') }}', heading: '{{ __('Success') }}', variant: 'success' })
        })

        $wire.on('generation-failed', () => {
            Flux.toast({ text: '{{ __('Failed to generate lesson. Please try again.') }}', heading: '{{ __('Error') }}', variant: 'danger' })
        })
    </script>
    @endscript
</flux:main>
