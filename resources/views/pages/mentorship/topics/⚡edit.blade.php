<?php

use App\Models\MentorshipTopic;
use App\Models\MentorshipAttachment;
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
#[Title('Edit Topic')]
class extends Component {
    use MentorshipValidationRules, WithFileUploads;

    #[Locked]
    public int $topicId;

    public string $topic_date = '';
    public string $day_of_week = '';
    public string $time_slot = '';
    public string $title = '';
    public string $category = '';
    public string $description = '';
    public bool $is_published = true;

    public array $newAttachments = [];

    // AI Lesson
    public ?string $aiLessonContent = null;
    public bool $isGenerating = false;

    public function mount(MentorshipTopic $topic): void
    {
        $this->topicId = $topic->id;
        $this->topic_date = $topic->topic_date->format('Y-m-d');
        $this->day_of_week = $topic->day_of_week;
        $this->time_slot = substr($topic->time_slot, 0, 5);
        $this->title = $topic->title;
        $this->category = $topic->category;
        $this->description = $topic->description ?? '';
        $this->is_published = $topic->is_published;
        $this->aiLessonContent = $topic->ai_lesson_content;
    }

    #[Computed]
    public function topic(): MentorshipTopic
    {
        return MentorshipTopic::with('attachments')->findOrFail($this->topicId);
    }

    public function updatedTopicDate(): void
    {
        if ($this->topic_date) {
            $this->day_of_week = \Carbon\Carbon::parse($this->topic_date)->format('D');
        }
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

    public function save(): void
    {
        $this->validate($this->mentorshipTopicRules());

        if (!empty($this->newAttachments)) {
            $this->validate([
                'newAttachments.*' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
            ]);
        }

        // Determine if AI lesson is being added/updated
        $hadAiLesson = $this->topic->ai_lesson_content !== null;
        $hasAiLesson = $this->aiLessonContent !== null;

        $this->topic->update([
            'topic_date' => $this->topic_date,
            'day_of_week' => $this->day_of_week,
            'time_slot' => $this->time_slot,
            'title' => $this->title,
            'category' => $this->category,
            'description' => $this->description ?: null,
            'is_published' => $this->is_published,
            'updated_by' => auth()->id(),
            'ai_lesson_content' => $this->aiLessonContent,
            'ai_lesson_generated_at' => $hasAiLesson && !$hadAiLesson ? now() : $this->topic->ai_lesson_generated_at,
        ]);

        // Handle new attachments
        foreach ($this->newAttachments as $file) {
            if ($file) {
                $path = $file->store('mentorship/attachments', 'public');

                MentorshipAttachment::create([
                    'topic_id' => $this->topicId,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getClientOriginalExtension(),
                    'file_size' => $file->getSize(),
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        unset($this->topic);

        session()->flash('status', __('Topic updated successfully!'));
        $this->redirect(route('mentorship.topics.show', $this->topicId), navigate: true);
    }

    public function deleteAttachment(int $attachmentId): void
    {
        $attachment = MentorshipAttachment::where('topic_id', $this->topicId)->findOrFail($attachmentId);

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        unset($this->topic);
        $this->dispatch('attachment-deleted');
    }

    public function addAttachment(): void
    {
        $this->newAttachments[] = null;
    }

    public function removeNewAttachment(int $index): void
    {
        unset($this->newAttachments[$index]);
        $this->newAttachments = array_values($this->newAttachments);
    }

    public function delete(): void
    {
        // Delete attachments from storage
        foreach ($this->topic->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $this->topic->delete();

        session()->flash('status', __('Topic deleted successfully!'));
        $this->redirect(route('mentorship.topics.index'), navigate: true);
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
                <flux:heading size="xl">{{ __('Edit Topic') }}</flux:heading>
                <flux:subheading>{{ $this->topic->title }}</flux:subheading>
            </div>

            <flux:button variant="ghost" :href="route('mentorship.topics.show', $topicId)" wire:navigate icon="arrow-left">
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
                                    <flux:heading size="lg">{{ __('AI Lesson') }}</flux:heading>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Generate or update educational content for this topic') }}</p>
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
                                            wire:confirm="{{ __('Are you sure you want to remove the AI lesson?') }}"
                                            icon="trash"
                                            class="text-red-600"
                                        >
                                            {{ __('Remove') }}
                                        </flux:button>
                                    </div>
                                </div>

                                <div class="prose prose-sm prose-zinc dark:prose-invert max-w-none max-h-96 overflow-y-auto">
                                    {!! nl2br(e($aiLessonContent)) !!}
                                </div>

                                @if($this->topic->ai_lesson_generated_at)
                                    <p class="text-xs text-purple-600 dark:text-purple-400 mt-3">
                                        {{ __('Generated') }} {{ $this->topic->ai_lesson_generated_at->diffForHumans() }}
                                    </p>
                                @endif
                            </div>
                        @elseif(empty($title))
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 italic">
                                {{ __('Enter a title to enable AI lesson generation.') }}
                            </p>
                        @endif
                    </div>
                @endif

                <flux:checkbox wire:model="is_published" :label="__('Published')" />

                <flux:separator />

                {{-- Existing Attachments --}}
                @if($this->topic->attachments->isNotEmpty())
                <div>
                    <flux:heading size="lg" class="mb-4">{{ __('Current Attachments') }}</flux:heading>
                    <div class="space-y-3">
                        @foreach($this->topic->attachments as $attachment)
                            <div class="flex items-center gap-4 p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800">
                                <flux:icon :name="$attachment->file_icon" class="size-6 text-zinc-500" />

                                <div class="flex-1 min-w-0">
                                    <p class="font-medium truncate">{{ $attachment->display_name_or_filename }}</p>
                                    <p class="text-xs text-zinc-500">{{ $attachment->file_size_formatted }}</p>
                                </div>

                                <flux:button
                                    variant="ghost"
                                    size="sm"
                                    wire:click="deleteAttachment({{ $attachment->id }})"
                                    wire:confirm="{{ __('Are you sure you want to delete this attachment?') }}"
                                    icon="trash"
                                    class="text-red-600"
                                />
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- New Attachments --}}
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <flux:heading size="lg">{{ __('Add New Attachments') }}</flux:heading>
                        <flux:button type="button" variant="ghost" size="sm" wire:click="addAttachment" icon="plus">
                            {{ __('Add Attachment') }}
                        </flux:button>
                    </div>

                    @if(empty($this->newAttachments))
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Click "Add Attachment" to upload new files.') }}</p>
                    @else
                        <div class="space-y-4">
                            @foreach($newAttachments as $index => $attachment)
                                <div class="flex items-start gap-4 p-4 rounded-lg bg-zinc-50 dark:bg-zinc-800">
                                    <div class="flex-1">
                                        <flux:label>{{ __('File') }} #{{ $index + 1 }}</flux:label>
                                        <input
                                            type="file"
                                            wire:model="newAttachments.{{ $index }}"
                                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                            class="mt-1 block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 dark:file:bg-zinc-700 dark:file:text-zinc-300"
                                        >
                                        @error("newAttachments.{$index}")
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                        <p class="text-xs text-zinc-500 mt-1">{{ __('Max 10MB. PDF, DOC, DOCX, JPG, PNG') }}</p>
                                    </div>

                                    <flux:button type="button" variant="ghost" size="sm" wire:click="removeNewAttachment({{ $index }})" icon="trash" class="text-red-600" />
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex justify-between gap-3 pt-4">
                    <flux:button
                        variant="danger"
                        type="button"
                        wire:click="delete"
                        wire:confirm="{{ __('Are you sure you want to delete this topic? This action cannot be undone.') }}"
                        icon="trash"
                    >
                        {{ __('Delete Topic') }}
                    </flux:button>

                    <div class="flex gap-3">
                        <flux:button variant="ghost" type="button" :href="route('mentorship.topics.show', $topicId)" wire:navigate>
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
        $wire.on('attachment-deleted', () => {
            Flux.toast({ text: '{{ __('Attachment deleted.') }}', heading: '{{ __('Success') }}', variant: 'success' })
        })

        $wire.on('lesson-generated', () => {
            Flux.toast({ text: '{{ __('Lesson content generated successfully!') }}', heading: '{{ __('Success') }}', variant: 'success' })
        })

        $wire.on('generation-failed', () => {
            Flux.toast({ text: '{{ __('Failed to generate lesson. Please try again.') }}', heading: '{{ __('Error') }}', variant: 'danger' })
        })
    </script>
    @endscript
</flux:main>
