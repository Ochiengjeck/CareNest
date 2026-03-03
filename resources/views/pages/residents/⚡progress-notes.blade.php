<?php

use App\Models\Resident;
use App\Models\ShiftProgressNote;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new
#[Layout('layouts.app.sidebar')]
#[Title('Shift Progress Notes')]
class extends Component {
    use WithPagination;

    #[Locked]
    public int $residentId;

    public function mount(Resident $resident): void
    {
        abort_unless(auth()->user()->can('view-residents'), 403);
        $this->residentId = $resident->id;
    }

    #[Computed]
    public function resident(): Resident
    {
        return Resident::findOrFail($this->residentId);
    }

    #[Computed]
    public function notes()
    {
        return ShiftProgressNote::where('resident_id', $this->residentId)
            ->with(['recorder', 'signature'])
            ->latest('shift_date')
            ->latest('id')
            ->paginate(15);
    }

    #[Computed]
    public function totalNotes(): int
    {
        return ShiftProgressNote::where('resident_id', $this->residentId)->count();
    }

    #[Computed]
    public function notesThisMonth(): int
    {
        return ShiftProgressNote::where('resident_id', $this->residentId)
            ->whereMonth('shift_date', now()->month)
            ->whereYear('shift_date', now()->year)
            ->count();
    }

    #[Computed]
    public function signedCount(): int
    {
        return ShiftProgressNote::where('resident_id', $this->residentId)
            ->whereNotNull('signature_id')
            ->count();
    }

    #[Computed]
    public function lastNoteDate(): ?string
    {
        $latest = ShiftProgressNote::where('resident_id', $this->residentId)
            ->latest('shift_date')
            ->first();

        return $latest?->shift_date->format('M d, Y');
    }
}; ?>

<flux:main>
    <div class="max-w-5xl space-y-6">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                <flux:button variant="ghost" :href="route('residents.show', $this->residentId)" wire:navigate icon="arrow-left" />
                <div>
                    <flux:heading size="xl">{{ __('Shift Progress Notes') }}</flux:heading>
                    <div class="mt-0.5 flex flex-wrap items-center gap-2 text-sm">
                        <flux:subheading>{{ $this->resident->full_name }}</flux:subheading>
                        <flux:badge size="sm" :color="$this->resident->status === 'active' ? 'green' : ($this->resident->status === 'discharged' ? 'amber' : 'red')">
                            {{ ucfirst($this->resident->status) }}
                        </flux:badge>
                        @if ($this->resident->room_number)
                            <span class="text-zinc-400 dark:text-zinc-500">&bull;</span>
                            <span class="text-zinc-500 dark:text-zinc-400">Room {{ $this->resident->room_number }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                {{-- Export All PDF --}}
                @if ($this->totalNotes > 0)
                    <a
                        href="{{ route('residents.progress-notes.export.pdf', $this->residentId) }}"
                        target="_blank"
                    >
                        <flux:button variant="outline" icon="arrow-down-tray">
                            {{ __('Export All PDF') }}
                        </flux:button>
                    </a>
                @endif

                @can('manage-residents')
                    <flux:button
                        variant="primary"
                        icon="plus"
                        :href="route('residents.progress-notes.create', $this->residentId)"
                        wire:navigate
                    >
                        {{ __('New Note') }}
                    </flux:button>
                @endcan
            </div>
        </div>

        {{-- Stats bar --}}
        <div class="grid grid-cols-3 gap-4">
            <flux:card class="flex items-center gap-3 p-4">
                <div class="flex size-10 items-center justify-center rounded-full bg-blue-50 dark:bg-blue-900/20">
                    <flux:icon name="clipboard-document-list" class="size-5 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <div class="text-xl font-bold text-zinc-800 dark:text-zinc-100">{{ $this->totalNotes }}</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Total Notes') }}</div>
                </div>
            </flux:card>

            <flux:card class="flex items-center gap-3 p-4">
                <div class="flex size-10 items-center justify-center rounded-full bg-violet-50 dark:bg-violet-900/20">
                    <flux:icon name="calendar-days" class="size-5 text-violet-600 dark:text-violet-400" />
                </div>
                <div>
                    <div class="text-xl font-bold text-zinc-800 dark:text-zinc-100">{{ $this->notesThisMonth }}</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('This Month') }}</div>
                </div>
            </flux:card>

            <flux:card class="flex items-center gap-3 p-4">
                <div class="flex size-10 items-center justify-center rounded-full bg-green-50 dark:bg-green-900/20">
                    <flux:icon name="check-badge" class="size-5 text-green-600 dark:text-green-400" />
                </div>
                <div>
                    <div class="text-xl font-bold text-zinc-800 dark:text-zinc-100">{{ $this->signedCount }}</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Signed') }}</div>
                </div>
            </flux:card>
        </div>

        {{-- Notes table or empty state --}}
        @if ($this->notes->isEmpty())
            <flux:card class="flex flex-col items-center py-16 text-center">
                <div class="mb-4 flex size-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon name="clipboard-document-list" class="size-8 text-zinc-400 dark:text-zinc-500" />
                </div>
                <flux:heading size="sm">{{ __('No shift notes yet') }}</flux:heading>
                <flux:text class="mt-1 max-w-sm text-sm text-zinc-400">
                    {{ __('Shift progress notes documenting daily observations and care will appear here.') }}
                </flux:text>
                @can('manage-residents')
                    <flux:button
                        class="mt-6"
                        variant="primary"
                        icon="plus"
                        :href="route('residents.progress-notes.create', $this->residentId)"
                        wire:navigate
                    >
                        {{ __('Create First Note') }}
                    </flux:button>
                @endcan
            </flux:card>
        @else
            <flux:card class="overflow-hidden p-0">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Date & Shift') }}</flux:table.column>
                        <flux:table.column>{{ __('Mood') }}</flux:table.column>
                        <flux:table.column>{{ __('Medication') }}</flux:table.column>
                        <flux:table.column>{{ __('Recorded By') }}</flux:table.column>
                        <flux:table.column>{{ __('Signed') }}</flux:table.column>
                        <flux:table.column class="w-10"></flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($this->notes as $note)
                            @php
                                $shiftLabel = $note->shift_type_label;
                                $shiftColor = match(true) {
                                    str_contains($shiftLabel, 'Day')     => 'amber',
                                    str_contains($shiftLabel, 'Evening') => 'blue',
                                    str_contains($shiftLabel, 'Night')   => 'purple',
                                    default                               => 'zinc',
                                };
                                $moodLabels = ['appropriate'=>'Appropriate','anxious'=>'Anxious','worry'=>'Worry','sad'=>'Sad','depressed'=>'Depressed','irritable'=>'Irritable','angry'=>'Angry','fearful'=>'Fearful','other'=>'Other'];
                            @endphp
                            <flux:table.row :key="$note->id">
                                <flux:table.cell>
                                    <div class="font-medium">{{ $note->shift_date->format('M d, Y') }}</div>
                                    <div class="mt-0.5 flex items-center gap-1.5">
                                        <flux:badge size="sm" :color="$shiftColor">{{ $shiftLabel }}</flux:badge>
                                        @if ($note->shift_start_time)
                                            <span class="text-xs text-zinc-400 dark:text-zinc-500">
                                                {{ \Carbon\Carbon::parse($note->shift_start_time)->format('g:i A') }}
                                                @if ($note->shift_end_time)– {{ \Carbon\Carbon::parse($note->shift_end_time)->format('g:i A') }}@endif
                                            </span>
                                        @endif
                                    </div>
                                </flux:table.cell>

                                <flux:table.cell>
                                    <div class="flex flex-wrap gap-1">
                                        @php $moods = $note->mood ?? [] @endphp
                                        @foreach (array_slice($moods, 0, 2) as $mk)
                                            <flux:badge size="sm" color="zinc">{{ $moodLabels[$mk] ?? $mk }}</flux:badge>
                                        @endforeach
                                        @if (count($moods) > 2)
                                            <span class="text-xs text-zinc-400">+{{ count($moods) - 2 }}</span>
                                        @endif
                                        @if (empty($moods))
                                            <span class="text-sm text-zinc-400">—</span>
                                        @endif
                                    </div>
                                </flux:table.cell>

                                <flux:table.cell>
                                    @php
                                        $medColor = match($note->medication_administered) {
                                            'yes'     => 'green',
                                            'no'      => 'zinc',
                                            'refused' => 'red',
                                            default   => 'zinc',
                                        };
                                    @endphp
                                    @if ($note->medication_administered)
                                        <flux:badge size="sm" :color="$medColor">{{ ucfirst($note->medication_administered) }}</flux:badge>
                                    @else
                                        <span class="text-sm text-zinc-400">—</span>
                                    @endif
                                </flux:table.cell>

                                <flux:table.cell>
                                    <flux:text class="text-sm">{{ $note->recorder?->name ?? '—' }}</flux:text>
                                </flux:table.cell>

                                <flux:table.cell>
                                    @if ($note->signature_id)
                                        <flux:badge size="sm" color="green" icon="check-circle">{{ __('Signed') }}</flux:badge>
                                    @else
                                        <flux:badge size="sm" color="zinc">{{ __('Unsigned') }}</flux:badge>
                                    @endif
                                </flux:table.cell>

                                <flux:table.cell>
                                    <flux:button
                                        variant="ghost"
                                        size="sm"
                                        icon="arrow-top-right-on-square"
                                        :href="route('progress-notes.show', $note->id)"
                                        wire:navigate
                                        title="{{ __('View note') }}"
                                    />
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </flux:card>

            <div>{{ $this->notes->links() }}</div>
        @endif
    </div>
</flux:main>
