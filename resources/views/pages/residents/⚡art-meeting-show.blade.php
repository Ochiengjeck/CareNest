<?php

use App\Models\ArtMeeting;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('ART Meeting Note')]
class extends Component {
    #[Locked]
    public int $recordId;

    public function mount(ArtMeeting $artMeeting): void
    {
        abort_unless(auth()->user()->can('view-residents'), 403);
        $this->recordId = $artMeeting->id;
    }

    #[Computed]
    public function record(): ArtMeeting
    {
        return ArtMeeting::with(['resident', 'recorder'])->findOrFail($this->recordId);
    }
}; ?>

<flux:main>
    @php $record = $this->record; @endphp
    <div class="max-w-4xl space-y-5">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                <flux:button variant="ghost" :href="route('residents.art-meetings.index', $record->resident_id)" wire:navigate icon="arrow-left" />
                <div>
                    <flux:heading size="xl">{{ __('ART Meeting Note') }}</flux:heading>
                    <flux:subheading>{{ $record->resident->full_name }}</flux:subheading>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-zinc-200 bg-zinc-50 px-5 py-3 dark:border-zinc-700 dark:bg-zinc-800/40">
            <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $record->meeting_date->format('M d, Y') }}</span>
            <flux:badge :color="$record->meeting_type_color" size="sm">{{ $record->meeting_type_label }}</flux:badge>
            <span class="ml-auto flex items-center gap-1.5 text-sm text-zinc-500">
                <flux:icon name="user" class="size-4" />{{ $record->recorder?->name ?? '—' }}
                <span class="text-zinc-400">&bull;</span>{{ $record->created_at->diffForHumans() }}
            </span>
        </div>

        <div class="rounded-lg border border-blue-100 bg-blue-50/60 px-5 py-2.5 dark:border-blue-900/40 dark:bg-blue-950/20">
            <div class="flex flex-wrap gap-x-8 gap-y-1 text-sm">
                <div><span class="text-zinc-400">AHCCCS ID:</span> <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $record->resident->ahcccs_id ?? '—' }}</span></div>
                <div><span class="text-zinc-400">DOB:</span> <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $record->resident->date_of_birth->format('M d, Y') }}</span></div>
                <div><span class="text-zinc-400">Admitted:</span> <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $record->resident->admission_date->format('M d, Y') }}</span></div>
            </div>
        </div>

        {{-- Attendees --}}
        <flux:card class="space-y-3">
            <div class="flex items-center gap-2"><flux:icon name="users" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Attendees') }}</flux:heading></div>
            <flux:separator />
            @if (!empty($record->attendees))
                <div class="flex flex-wrap gap-2">
                    @foreach ($record->attendees as $attendee)
                        <flux:badge color="blue">{{ $attendee }}</flux:badge>
                    @endforeach
                </div>
            @else
                <flux:text class="text-sm text-zinc-400">{{ __('No attendees recorded.') }}</flux:text>
            @endif
        </flux:card>

        {{-- Discussion Notes --}}
        @if ($record->discussion_notes)
            <flux:card class="space-y-3">
                <div class="flex items-center gap-2"><flux:icon name="chat-bubble-left-right" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Discussion Notes') }}</flux:heading></div>
                <flux:separator />
                <flux:text class="text-sm text-zinc-700 dark:text-zinc-300" style="white-space: pre-wrap;">{{ $record->discussion_notes }}</flux:text>
            </flux:card>
        @endif

        {{-- Treatment Plan Revisions --}}
        @if ($record->treatment_plan_revisions)
            <flux:card class="space-y-3">
                <div class="flex items-center gap-2"><flux:icon name="document-magnifying-glass" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Treatment Plan Revisions') }}</flux:heading></div>
                <flux:separator />
                <flux:text class="text-sm text-zinc-700 dark:text-zinc-300" style="white-space: pre-wrap;">{{ $record->treatment_plan_revisions }}</flux:text>
            </flux:card>
        @endif

        {{-- Next Meeting --}}
        @if ($record->next_meeting_date)
            <flux:card class="space-y-3">
                <div class="flex items-center gap-2"><flux:icon name="arrow-right-circle" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Next Meeting') }}</flux:heading></div>
                <flux:separator />
                <div class="flex items-center gap-2">
                    <flux:icon name="calendar" class="size-4 text-zinc-400" />
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $record->next_meeting_date->format('M d, Y') }}</span>
                    <span class="text-sm text-zinc-400">({{ $record->next_meeting_date->diffForHumans() }})</span>
                </div>
            </flux:card>
        @endif

    </div>
</flux:main>
