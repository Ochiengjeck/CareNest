<?php

use App\Models\MentorshipSession;
use App\Models\MentorshipTopic;
use App\Models\MentorshipLesson;
use App\Concerns\MentorshipValidationRules;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.mentorship')]
#[Title('Edit Session')]
class extends Component {
    use MentorshipValidationRules;

    #[Locked]
    public int $sessionId;

    public ?int $topic_id = null;
    public string $session_date = '';
    public string $start_time = '';
    public string $end_time = '';
    public string $session_notes = '';
    public string $lesson_content_snapshot = '';

    public function mount(MentorshipSession $session): void
    {
        // Only the mentor can edit their own session
        if ($session->mentor_id !== auth()->id()) {
            abort(403);
        }

        // Can only edit planned or in_progress sessions
        if (!in_array($session->status, ['planned', 'in_progress'])) {
            abort(403, __('Only planned or in-progress sessions can be edited.'));
        }

        $this->sessionId = $session->id;
        $this->topic_id = $session->topic_id;
        $this->session_date = $session->session_date->format('Y-m-d');
        $this->start_time = $session->start_time ? $session->start_time->format('H:i') : '';
        $this->end_time = $session->end_time ? $session->end_time->format('H:i') : '';
        $this->session_notes = $session->session_notes ?? '';
        $this->lesson_content_snapshot = $session->lesson_content_snapshot ?? '';
    }

    #[Computed]
    public function session(): MentorshipSession
    {
        return MentorshipSession::with(['topic', 'lesson'])->findOrFail($this->sessionId);
    }

    #[Computed]
    public function topics()
    {
        return MentorshipTopic::published()->orderBy('title')->get();
    }

    public function save(): void
    {
        $this->validate([
            'topic_id' => ['required', 'exists:mentorship_topics,id'],
            'session_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'session_notes' => ['nullable', 'string', 'max:5000'],
            'lesson_content_snapshot' => ['nullable', 'string'],
        ]);

        $this->session->update([
            'topic_id' => $this->topic_id,
            'session_date' => $this->session_date,
            'start_time' => $this->start_time ?: null,
            'end_time' => $this->end_time ?: null,
            'session_notes' => $this->session_notes ?: null,
            'lesson_content_snapshot' => $this->lesson_content_snapshot ?: null,
            'updated_by' => auth()->id(),
        ]);

        session()->flash('status', __('Session updated successfully!'));
        $this->redirect(route('mentorship.sessions.show', $this->sessionId), navigate: true);
    }

    public function delete(): void
    {
        $this->session->delete();

        session()->flash('status', __('Session deleted successfully!'));
        $this->redirect(route('mentorship.sessions.index'), navigate: true);
    }
}; ?>

<flux:main>
    <div class="space-y-6 max-w-4xl">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('Edit Session') }}</flux:heading>
                <flux:subheading>{{ $this->session->topic?->title }}</flux:subheading>
            </div>

            <flux:button variant="ghost" :href="route('mentorship.sessions.show', $sessionId)" wire:navigate icon="arrow-left">
                {{ __('Back') }}
            </flux:button>
        </div>

        <form wire:submit="save">
            <flux:card class="space-y-6">
                {{-- Topic --}}
                <flux:select wire:model="topic_id" :label="__('Topic')" required>
                    <option value="">{{ __('Select a topic...') }}</option>
                    @foreach($this->topics as $topic)
                        <option value="{{ $topic->id }}">{{ $topic->title }} ({{ $topic->category }})</option>
                    @endforeach
                </flux:select>

                {{-- Date & Time --}}
                <div class="grid gap-6 sm:grid-cols-3">
                    <flux:input
                        type="date"
                        wire:model="session_date"
                        :label="__('Session Date')"
                        required
                    />

                    <flux:input
                        type="time"
                        wire:model="start_time"
                        :label="__('Start Time')"
                    />

                    <flux:input
                        type="time"
                        wire:model="end_time"
                        :label="__('End Time')"
                    />
                </div>

                {{-- Session Notes --}}
                <flux:textarea
                    wire:model="session_notes"
                    :label="__('Session Notes')"
                    :placeholder="__('Notes about objectives, target audience, or observations...')"
                    rows="4"
                />

                {{-- Lesson Content --}}
                <flux:textarea
                    wire:model="lesson_content_snapshot"
                    :label="__('Lesson Content')"
                    :description="__('The lesson content for this session')"
                    rows="12"
                />

                {{-- Actions --}}
                <div class="flex justify-between pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button
                        variant="danger"
                        type="button"
                        wire:click="delete"
                        wire:confirm="{{ __('Are you sure you want to delete this session?') }}"
                        icon="trash"
                    >
                        {{ __('Delete') }}
                    </flux:button>

                    <div class="flex gap-3">
                        <flux:button variant="ghost" type="button" :href="route('mentorship.sessions.show', $sessionId)" wire:navigate>
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
</flux:main>
