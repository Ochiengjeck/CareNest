<?php

use App\Models\ObservationNote;
use App\Models\Resident;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new
#[Layout('layouts.app.sidebar')]
#[Title('Observation Notes (RON)')]
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
    public function records()
    {
        return ObservationNote::where('resident_id', $this->residentId)
            ->with(['observer'])
            ->latest('observed_at')
            ->paginate(20);
    }

    #[Computed]
    public function totalRecords(): int
    {
        return ObservationNote::where('resident_id', $this->residentId)->count();
    }

    #[Computed]
    public function recordsToday(): int
    {
        return ObservationNote::where('resident_id', $this->residentId)
            ->whereDate('observed_at', now()->toDateString())
            ->count();
    }

    #[Computed]
    public function atRiskCount(): int
    {
        return ObservationNote::where('resident_id', $this->residentId)
            ->whereIn('safety_status', ['at_risk', 'unsafe'])
            ->where('observed_at', '>=', now()->subDays(7))
            ->count();
    }
}; ?>

<flux:main>
    <div class="max-w-5xl space-y-6">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                <flux:button variant="ghost" :href="route('residents.show', $this->residentId)" wire:navigate icon="arrow-left" />
                <div>
                    <flux:heading size="xl">{{ __('Observation Notes') }}</flux:heading>
                    <flux:subheading>{{ __('RON — Record of Observation — ') }}{{ $this->resident->full_name }}</flux:subheading>
                </div>
            </div>
            @can('manage-residents')
                <flux:button variant="primary" icon="plus" :href="route('residents.observation-notes.create', $this->residentId)" wire:navigate>
                    {{ __('New Observation') }}
                </flux:button>
            @endcan
        </div>

        <div class="grid grid-cols-3 gap-4">
            <flux:card class="flex items-center gap-3 p-4">
                <div class="flex size-10 items-center justify-center rounded-full bg-blue-50 dark:bg-blue-900/20">
                    <flux:icon name="eye" class="size-5 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <div class="text-xl font-bold text-zinc-800 dark:text-zinc-100">{{ $this->totalRecords }}</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Total Observations') }}</div>
                </div>
            </flux:card>
            <flux:card class="flex items-center gap-3 p-4">
                <div class="flex size-10 items-center justify-center rounded-full bg-violet-50 dark:bg-violet-900/20">
                    <flux:icon name="sun" class="size-5 text-violet-600 dark:text-violet-400" />
                </div>
                <div>
                    <div class="text-xl font-bold text-zinc-800 dark:text-zinc-100">{{ $this->recordsToday }}</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Today') }}</div>
                </div>
            </flux:card>
            <flux:card class="flex items-center gap-3 p-4">
                <div class="flex size-10 items-center justify-center rounded-full bg-amber-50 dark:bg-amber-900/20">
                    <flux:icon name="exclamation-circle" class="size-5 text-amber-600 dark:text-amber-400" />
                </div>
                <div>
                    <div class="text-xl font-bold text-zinc-800 dark:text-zinc-100">{{ $this->atRiskCount }}</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('At Risk (7 days)') }}</div>
                </div>
            </flux:card>
        </div>

        @if ($this->records->isEmpty())
            <flux:card class="flex flex-col items-center py-16 text-center">
                <div class="mb-4 flex size-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon name="eye" class="size-8 text-zinc-400" />
                </div>
                <flux:heading size="sm">{{ __('No observation notes yet') }}</flux:heading>
                <flux:text class="mt-1 max-w-sm text-sm text-zinc-400">
                    {{ __('Observation (RON) records for this resident will appear here.') }}
                </flux:text>
                @can('manage-residents')
                    <flux:button class="mt-6" variant="primary" icon="plus" :href="route('residents.observation-notes.create', $this->residentId)" wire:navigate>
                        {{ __('Log First Observation') }}
                    </flux:button>
                @endcan
            </flux:card>
        @else
            <flux:card class="overflow-hidden p-0">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Observed At') }}</flux:table.column>
                        <flux:table.column>{{ __('Type') }}</flux:table.column>
                        <flux:table.column>{{ __('Safety Status') }}</flux:table.column>
                        <flux:table.column>{{ __('Mood/Affect') }}</flux:table.column>
                        <flux:table.column>{{ __('Location') }}</flux:table.column>
                        <flux:table.column>{{ __('Observer') }}</flux:table.column>
                        <flux:table.column class="w-10"></flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($this->records as $record)
                            <flux:table.row :key="$record->id">
                                <flux:table.cell>
                                    <div class="font-medium">{{ $record->observed_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-zinc-400">{{ $record->observed_at->format('g:i A') }}</div>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" color="zinc">{{ $record->observation_type_label }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" :color="$record->safety_status_color">{{ $record->safety_status_label }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>{{ $record->mood_affect ?? '—' }}</flux:table.cell>
                                <flux:table.cell>{{ $record->location ?? '—' }}</flux:table.cell>
                                <flux:table.cell>{{ $record->observer?->name ?? '—' }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:button variant="ghost" size="sm" icon="arrow-top-right-on-square"
                                        :href="route('observation-notes.show', $record->id)" wire:navigate />
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </flux:card>
            <div>{{ $this->records->links() }}</div>
        @endif
    </div>

    @script
    <script>
        @if (session('status'))
            Flux.toast({ text: '{{ session('status') }}', variant: 'success' });
        @endif
    </script>
    @endscript
</flux:main>
