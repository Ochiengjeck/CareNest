<?php

use App\Models\FinancialTransactionRecord;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Resident Financial Record')]
class extends Component {
    #[Locked]
    public int $recordId;

    public function mount(FinancialTransactionRecord $financialTransactionRecord): void
    {
        abort_unless(auth()->user()->can('view-residents'), 403);
        $this->recordId = $financialTransactionRecord->id;
    }

    #[Computed]
    public function record(): FinancialTransactionRecord
    {
        return FinancialTransactionRecord::with(['resident', 'recorder', 'signature'])->findOrFail($this->recordId);
    }

    #[Computed]
    public function signerNames(): array
    {
        $signers = $this->record->signers ?? [];

        if (empty($signers)) {
            return [];
        }

        return User::whereIn('id', $signers)->orderBy('name')->pluck('name')->toArray();
    }
}; ?>

<flux:main>
    @php
        $record   = $this->record;
        $entries  = $record->entries ?? [];
        $running  = 0;
        $balances = [];
        foreach ($entries as $entry) {
            $running   += (float)($entry['deposit'] ?? 0) - (float)($entry['money_spent'] ?? 0);
            $balances[] = $running;
        }
    @endphp

    <div class="max-w-4xl space-y-5">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                <flux:button variant="ghost" :href="route('residents.financial-transactions.index', $record->resident_id)" wire:navigate icon="arrow-left" />
                <div>
                    <flux:heading size="xl">{{ __('Resident Financial Record') }}</flux:heading>
                    <flux:subheading>{{ $record->resident->full_name }}</flux:subheading>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('financial-transactions.export.pdf', $record->id) }}" target="_blank">
                    <flux:button variant="outline" icon="arrow-down-tray">
                        {{ __('Download PDF') }}
                    </flux:button>
                </a>
            </div>
        </div>

        {{-- Info ribbon --}}
        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-zinc-200 bg-zinc-50 px-5 py-3 dark:border-zinc-700 dark:bg-zinc-800/40">
            <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $record->created_at->format('M d, Y') }}</span>
            <span class="ml-auto flex items-center gap-1.5 text-sm text-zinc-500 dark:text-zinc-400">
                <flux:icon name="user" class="size-4" />
                {{ $record->recorder?->name ?? '—' }}
                <span class="text-zinc-400">&bull;</span>
                {{ $record->created_at->diffForHumans() }}
            </span>
        </div>

        {{-- Resident bar --}}
        <div class="rounded-lg border border-blue-100 bg-blue-50/60 px-5 py-2.5 dark:border-blue-900/40 dark:bg-blue-950/20">
            <div class="flex flex-wrap gap-x-8 gap-y-1 text-sm">
                <div><span class="text-zinc-400">AHCCCS ID:</span> <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $record->resident->ahcccs_id ?? '—' }}</span></div>
                <div><span class="text-zinc-400">DOB:</span> <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $record->resident->date_of_birth->format('M d, Y') }}</span></div>
                <div><span class="text-zinc-400">Admitted:</span> <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $record->resident->admission_date->format('M d, Y') }}</span></div>
                @if ($record->resident->room_number)
                    <div><span class="text-zinc-400">Room:</span> <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $record->resident->room_number }}</span></div>
                @endif
            </div>
        </div>

        {{-- Diagnosis --}}
        <flux:card class="space-y-3">
            <div class="flex items-center gap-2">
                <flux:icon name="document-text" class="size-5 text-zinc-400" />
                <flux:heading size="sm">{{ __('Diagnosis') }}</flux:heading>
            </div>
            <flux:separator />
            @if ($record->diagnosis)
                <flux:text class="text-sm text-zinc-700 dark:text-zinc-300" style="white-space: pre-wrap;">{{ $record->diagnosis }}</flux:text>
            @else
                <flux:text class="text-sm text-zinc-400">{{ __('No diagnosis recorded.') }}</flux:text>
            @endif
        </flux:card>

        {{-- Transactions table --}}
        <flux:card class="space-y-4">
            <div class="flex items-center gap-2">
                <flux:icon name="banknotes" class="size-5 text-zinc-400" />
                <flux:heading size="sm">{{ __('Transactions') }}</flux:heading>
            </div>
            <flux:separator />

            @if (count($entries) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[640px] text-sm">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                <th class="py-2 pr-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Date') }}</th>
                                <th class="px-2 py-2 text-right text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Deposit ($)') }}</th>
                                <th class="px-2 py-2 text-right text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Money Spent ($)') }}</th>
                                <th class="px-2 py-2 text-right text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Balance ($)') }}</th>
                                <th class="py-2 pl-2 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Description') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach ($entries as $i => $entry)
                                @php $bal = $balances[$i]; @endphp
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                                    <td class="py-2.5 pr-3 text-zinc-700 dark:text-zinc-300">
                                        {{ \Carbon\Carbon::parse($entry['date'])->format('M d, Y') }}
                                    </td>
                                    <td class="px-2 py-2.5 text-right text-green-700 dark:text-green-400">
                                        ${{ number_format((float)($entry['deposit'] ?? 0), 2) }}
                                    </td>
                                    <td class="px-2 py-2.5 text-right text-red-700 dark:text-red-400">
                                        ${{ number_format((float)($entry['money_spent'] ?? 0), 2) }}
                                    </td>
                                    <td class="px-2 py-2.5 text-right">
                                        <span @class([
                                            'font-semibold',
                                            'text-green-700 dark:text-green-400' => $bal >= 0,
                                            'text-red-700 dark:text-red-400' => $bal < 0,
                                        ])>${{ number_format($bal, 2) }}</span>
                                    </td>
                                    <td class="py-2.5 pl-2 text-zinc-600 dark:text-zinc-400">{{ $entry['description'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <flux:text class="text-sm text-zinc-400">{{ __('No transaction entries recorded.') }}</flux:text>
            @endif
        </flux:card>

        {{-- Signers --}}
        <flux:card class="space-y-3">
            <div class="flex items-center gap-2">
                <flux:icon name="users" class="size-5 text-zinc-400" />
                <flux:heading size="sm">{{ __('Signers') }}</flux:heading>
            </div>
            <flux:separator />
            @if (count($this->signerNames) > 0)
                <div class="flex flex-wrap gap-2">
                    @foreach ($this->signerNames as $name)
                        <flux:badge color="blue">{{ $name }}</flux:badge>
                    @endforeach
                </div>
            @else
                <flux:text class="text-sm text-zinc-400">{{ __('No signers selected.') }}</flux:text>
            @endif
        </flux:card>

        {{-- Signature --}}
        <flux:card class="space-y-3">
            <div class="flex items-center gap-2">
                <flux:icon name="pencil" class="size-5 text-zinc-400" />
                <flux:heading size="sm">{{ __('Signature') }}</flux:heading>
            </div>
            <flux:separator />
            @php
                $sigUri = $record->signature?->getDataUri() ?? $record->raw_signature_data;
            @endphp
            @if ($sigUri)
                <div class="flex items-start gap-5">
                    <div>
                        <img
                            src="{{ $sigUri }}"
                            alt="Signature"
                            class="max-h-20 max-w-52 object-contain"
                        />
                    </div>
                    <div class="space-y-0.5">
                        <p class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $record->recorder?->name ?? '—' }}</p>
                        <p class="text-xs text-zinc-400 dark:text-zinc-500">{{ $record->created_at->format('M d, Y g:i A') }}</p>
                    </div>
                </div>
            @else
                <div class="flex items-center gap-2">
                    <flux:badge color="zinc">{{ __('Not signed') }}</flux:badge>
                    <flux:text class="text-sm text-zinc-400">{{ __('No digital signature was attached to this record.') }}</flux:text>
                </div>
            @endif
        </flux:card>

    </div>
</flux:main>
