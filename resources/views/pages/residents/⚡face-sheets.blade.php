<?php

use App\Models\FaceSheet;
use App\Models\Resident;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new
#[Layout('layouts.app.sidebar')]
#[Title('Face Sheets')]
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
    public function resident(): Resident { return Resident::findOrFail($this->residentId); }

    #[Computed]
    public function records()
    {
        return FaceSheet::where('resident_id', $this->residentId)
            ->with(['recorder'])->latest()->paginate(15);
    }

    #[Computed]
    public function totalRecords(): int { return FaceSheet::where('resident_id', $this->residentId)->count(); }

    #[Computed]
    public function recordsThisMonth(): int
    {
        return FaceSheet::where('resident_id', $this->residentId)
            ->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
    }
}; ?>

<flux:main>
    <div class="max-w-5xl space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                <flux:button variant="ghost" :href="route('residents.reports', $this->residentId)" wire:navigate icon="arrow-left" />
                <div>
                    <flux:heading size="xl">{{ __('Face Sheets') }}</flux:heading>
                    <flux:subheading>{{ $this->resident->full_name }}</flux:subheading>
                </div>
            </div>
            @can('manage-residents')
                <flux:button variant="primary" icon="plus" :href="route('residents.face-sheets.create', $this->residentId)" wire:navigate>
                    {{ __('New Face Sheet') }}
                </flux:button>
            @endcan
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:card class="flex items-center gap-3 p-4">
                <div class="flex size-10 items-center justify-center rounded-full bg-blue-50 dark:bg-blue-900/20">
                    <flux:icon name="identification" class="size-5 text-blue-600 dark:text-blue-400" />
                </div>
                <div><div class="text-xl font-bold">{{ $this->totalRecords }}</div><div class="text-xs text-zinc-500">Total Face Sheets</div></div>
            </flux:card>
            <flux:card class="flex items-center gap-3 p-4">
                <div class="flex size-10 items-center justify-center rounded-full bg-violet-50 dark:bg-violet-900/20">
                    <flux:icon name="calendar-days" class="size-5 text-violet-600 dark:text-violet-400" />
                </div>
                <div><div class="text-xl font-bold">{{ $this->recordsThisMonth }}</div><div class="text-xs text-zinc-500">This Month</div></div>
            </flux:card>
        </div>

        @if ($this->records->isEmpty())
            <flux:card class="flex flex-col items-center py-16 text-center">
                <div class="mb-4 flex size-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon name="identification" class="size-8 text-zinc-400" />
                </div>
                <flux:heading size="sm">{{ __('No face sheets yet') }}</flux:heading>
                @can('manage-residents')
                    <flux:button class="mt-6" variant="primary" icon="plus" :href="route('residents.face-sheets.create', $this->residentId)" wire:navigate>
                        {{ __('Create Face Sheet') }}
                    </flux:button>
                @endcan
            </flux:card>
        @else
            <flux:card class="overflow-hidden p-0">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Created</flux:table.column>
                        <flux:table.column>PCP</flux:table.column>
                        <flux:table.column>Health Plan</flux:table.column>
                        <flux:table.column>Recorded By</flux:table.column>
                        <flux:table.column>Export</flux:table.column>
                        <flux:table.column class="w-10"></flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($this->records as $record)
                            <flux:table.row :key="$record->id">
                                <flux:table.cell>
                                    <div class="font-medium">{{ $record->created_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-zinc-400">{{ $record->created_at->diffForHumans() }}</div>
                                </flux:table.cell>
                                <flux:table.cell>{{ $record->pcp_name ?? '—' }}</flux:table.cell>
                                <flux:table.cell>{{ $record->health_plan ?? '—' }}</flux:table.cell>
                                <flux:table.cell>{{ $record->recorder?->name ?? '—' }}</flux:table.cell>
                                <flux:table.cell>
                                    <a href="{{ route('face-sheets.export.pdf', $record->id) }}" target="_blank">
                                        <flux:button variant="ghost" size="sm" icon="arrow-down-tray" />
                                    </a>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:button variant="ghost" size="sm" icon="arrow-top-right-on-square"
                                        :href="route('face-sheets.show', $record->id)" wire:navigate />
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
        @if (session('status')) Flux.toast({ text: '{{ session('status') }}', variant: 'success' }); @endif
    </script>
    @endscript
</flux:main>
