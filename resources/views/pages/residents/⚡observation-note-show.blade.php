<?php

use App\Models\ObservationNote;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Observation Note')]
class extends Component {
    #[Locked]
    public int $recordId;

    public function mount(ObservationNote $observationNote): void
    {
        abort_unless(auth()->user()->can('view-residents'), 403);
        $this->recordId = $observationNote->id;
    }

    #[Computed]
    public function record(): ObservationNote
    {
        return ObservationNote::with(['resident', 'observer'])->findOrFail($this->recordId);
    }
}; ?>

<flux:main>
    @php $record = $this->record; @endphp
    <div class="max-w-4xl space-y-5">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                <flux:button variant="ghost" :href="route('residents.observation-notes.index', $record->resident_id)" wire:navigate icon="arrow-left" />
                <div>
                    <flux:heading size="xl">{{ __('Observation Note') }}</flux:heading>
                    <flux:subheading>{{ $record->resident->full_name }}</flux:subheading>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-zinc-200 bg-zinc-50 px-5 py-3 dark:border-zinc-700 dark:bg-zinc-800/40">
            <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $record->observed_at->format('M d, Y g:i A') }}</span>
            <flux:badge color="zinc" size="sm">{{ $record->observation_type_label }}</flux:badge>
            <flux:badge :color="$record->safety_status_color" size="sm">{{ $record->safety_status_label }}</flux:badge>
            <span class="ml-auto flex items-center gap-1.5 text-sm text-zinc-500">
                <flux:icon name="user" class="size-4" />{{ $record->observer?->name ?? '—' }}
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

        {{-- Summary --}}
        <flux:card class="space-y-3">
            <div class="flex items-center gap-2"><flux:icon name="eye" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Observation Summary') }}</flux:heading></div>
            <flux:separator />
            <div class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
                <div>
                    <div class="text-xs text-zinc-400">{{ __('Safety Status') }}</div>
                    <flux:badge :color="$record->safety_status_color">{{ $record->safety_status_label }}</flux:badge>
                </div>
                <div>
                    <div class="text-xs text-zinc-400">{{ __('Observation Type') }}</div>
                    <div class="font-medium text-zinc-700 dark:text-zinc-300">{{ $record->observation_type_label }}</div>
                </div>
                @if ($record->mood_affect)
                    <div>
                        <div class="text-xs text-zinc-400">{{ __('Mood / Affect') }}</div>
                        <div class="font-medium text-zinc-700 dark:text-zinc-300">{{ $record->mood_affect }}</div>
                    </div>
                @endif
                @if ($record->location)
                    <div>
                        <div class="text-xs text-zinc-400">{{ __('Location') }}</div>
                        <div class="font-medium text-zinc-700 dark:text-zinc-300">{{ $record->location }}</div>
                    </div>
                @endif
            </div>
        </flux:card>

        @if ($record->behavior)
            <flux:card class="space-y-3">
                <div class="flex items-center gap-2"><flux:icon name="clipboard-document" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Behavior') }}</flux:heading></div>
                <flux:separator />
                <flux:text class="text-sm text-zinc-700 dark:text-zinc-300" style="white-space: pre-wrap;">{{ $record->behavior }}</flux:text>
            </flux:card>
        @endif

        @if ($record->notes)
            <flux:card class="space-y-3">
                <div class="flex items-center gap-2"><flux:icon name="document-text" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Additional Notes') }}</flux:heading></div>
                <flux:separator />
                <flux:text class="text-sm text-zinc-700 dark:text-zinc-300" style="white-space: pre-wrap;">{{ $record->notes }}</flux:text>
            </flux:card>
        @endif

    </div>
</flux:main>
