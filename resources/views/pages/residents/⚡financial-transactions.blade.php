<?php

use App\Models\FinancialTransactionRecord;
use App\Models\Resident;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new
#[Layout('layouts.app.sidebar')]
#[Title('Financial Transaction Records')]
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
        return FinancialTransactionRecord::where('resident_id', $this->residentId)
            ->with(['recorder', 'signature'])
            ->latest()
            ->paginate(15);
    }

    #[Computed]
    public function totalRecords(): int
    {
        return FinancialTransactionRecord::where('resident_id', $this->residentId)->count();
    }

    #[Computed]
    public function recordsThisMonth(): int
    {
        return FinancialTransactionRecord::where('resident_id', $this->residentId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }

    #[Computed]
    public function signedCount(): int
    {
        return FinancialTransactionRecord::where('resident_id', $this->residentId)
            ->where(function ($q) {
                $q->whereNotNull('signature_id')
                  ->orWhereNotNull('raw_signature_data');
            })
            ->count();
    }
}; ?>

<flux:main>
    <div class="max-w-5xl space-y-6">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                <flux:button variant="ghost" :href="route('residents.show', $this->residentId)" wire:navigate icon="arrow-left" />
                <div>
                    <flux:heading size="xl">{{ __('Financial Transaction Records') }}</flux:heading>
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

            @can('manage-residents')
                <flux:button
                    variant="primary"
                    icon="plus"
                    :href="route('residents.financial-transactions.create', $this->residentId)"
                    wire:navigate
                >
                    {{ __('New Record') }}
                </flux:button>
            @endcan
        </div>

        {{-- Stats bar --}}
        <div class="grid grid-cols-3 gap-4">
            <flux:card class="flex items-center gap-3 p-4">
                <div class="flex size-10 items-center justify-center rounded-full bg-blue-50 dark:bg-blue-900/20">
                    <flux:icon name="banknotes" class="size-5 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <div class="text-xl font-bold text-zinc-800 dark:text-zinc-100">{{ $this->totalRecords }}</div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Total Records') }}</div>
                </div>
            </flux:card>

            <flux:card class="flex items-center gap-3 p-4">
                <div class="flex size-10 items-center justify-center rounded-full bg-violet-50 dark:bg-violet-900/20">
                    <flux:icon name="calendar-days" class="size-5 text-violet-600 dark:text-violet-400" />
                </div>
                <div>
                    <div class="text-xl font-bold text-zinc-800 dark:text-zinc-100">{{ $this->recordsThisMonth }}</div>
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

        {{-- Table or empty state --}}
        @if ($this->records->isEmpty())
            <flux:card class="flex flex-col items-center py-16 text-center">
                <div class="mb-4 flex size-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon name="banknotes" class="size-8 text-zinc-400 dark:text-zinc-500" />
                </div>
                <flux:heading size="sm">{{ __('No financial records yet') }}</flux:heading>
                <flux:text class="mt-1 max-w-sm text-sm text-zinc-400">
                    {{ __('Financial transaction records for this resident will appear here.') }}
                </flux:text>
                @can('manage-residents')
                    <flux:button
                        class="mt-6"
                        variant="primary"
                        icon="plus"
                        :href="route('residents.financial-transactions.create', $this->residentId)"
                        wire:navigate
                    >
                        {{ __('Create First Record') }}
                    </flux:button>
                @endcan
            </flux:card>
        @else
            <flux:card class="overflow-hidden p-0">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Date Created') }}</flux:table.column>
                        <flux:table.column>{{ __('Entries') }}</flux:table.column>
                        <flux:table.column>{{ __('Total Deposits') }}</flux:table.column>
                        <flux:table.column>{{ __('Total Spent') }}</flux:table.column>
                        <flux:table.column>{{ __('Signed') }}</flux:table.column>
                        <flux:table.column>{{ __('Export') }}</flux:table.column>
                        <flux:table.column class="w-10"></flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($this->records as $record)
                            @php
                                $entries      = $record->entries ?? [];
                                $totalDeposit = collect($entries)->sum(fn($e) => (float)($e['deposit'] ?? 0));
                                $totalSpent   = collect($entries)->sum(fn($e) => (float)($e['money_spent'] ?? 0));
                            @endphp
                            <flux:table.row :key="$record->id">
                                <flux:table.cell>
                                    <div class="font-medium">{{ $record->created_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-zinc-400 dark:text-zinc-500">{{ $record->created_at->diffForHumans() }}</div>
                                </flux:table.cell>

                                <flux:table.cell>
                                    <flux:text class="text-sm">{{ count($entries) }}</flux:text>
                                </flux:table.cell>

                                <flux:table.cell>
                                    <flux:text class="text-sm text-green-700 dark:text-green-400">${{ number_format($totalDeposit, 2) }}</flux:text>
                                </flux:table.cell>

                                <flux:table.cell>
                                    <flux:text class="text-sm text-red-700 dark:text-red-400">${{ number_format($totalSpent, 2) }}</flux:text>
                                </flux:table.cell>

                                <flux:table.cell>
                                    @if ($record->signature_id || $record->raw_signature_data)
                                        <flux:badge size="sm" color="green" icon="check-circle">{{ __('Signed') }}</flux:badge>
                                    @else
                                        <flux:badge size="sm" color="zinc">{{ __('Unsigned') }}</flux:badge>
                                    @endif
                                </flux:table.cell>

                                <flux:table.cell>
                                    <a href="{{ route('financial-transactions.export.pdf', $record->id) }}" target="_blank">
                                        <flux:button variant="ghost" size="sm" icon="arrow-down-tray" title="{{ __('Download PDF') }}" />
                                    </a>
                                </flux:table.cell>

                                <flux:table.cell>
                                    <flux:button
                                        variant="ghost"
                                        size="sm"
                                        icon="arrow-top-right-on-square"
                                        :href="route('financial-transactions.show', $record->id)"
                                        wire:navigate
                                        title="{{ __('View record') }}"
                                    />
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </flux:card>

            <div>{{ $this->records->links() }}</div>
        @endif
    </div>
</flux:main>
