<?php

use App\Models\AsamChecklist;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('ASAM Criteria Checklist')]
class extends Component {
    #[Locked]
    public int $recordId;

    public function mount(AsamChecklist $asamChecklist): void
    {
        abort_unless(auth()->user()->can('view-residents'), 403);
        $this->recordId = $asamChecklist->id;
    }

    #[Computed]
    public function record(): AsamChecklist
    {
        return AsamChecklist::with(['resident', 'recorder', 'signature'])->findOrFail($this->recordId);
    }

    #[Computed]
    public function signerNames(): array
    {
        $signers = $this->record->signers ?? [];
        return empty($signers) ? [] : User::whereIn('id', $signers)->orderBy('name')->pluck('name')->toArray();
    }

    #[Computed]
    public function dimensions(): array { return AsamChecklist::dimensions(); }
}; ?>

<flux:main>
    @php $record = $this->record; @endphp
    <div class="max-w-4xl space-y-5">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                <flux:button variant="ghost" :href="route('residents.asam-checklists.index', $record->resident_id)" wire:navigate icon="arrow-left" />
                <div>
                    <flux:heading size="xl">{{ __('ASAM Criteria Checklist') }}</flux:heading>
                    <flux:subheading>{{ $record->resident->full_name }}</flux:subheading>
                </div>
            </div>
            <a href="{{ route('asam-checklists.export.pdf', $record->id) }}" target="_blank">
                <flux:button variant="outline" icon="arrow-down-tray">{{ __('Download PDF') }}</flux:button>
            </a>
        </div>

        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-zinc-200 bg-zinc-50 px-5 py-3 dark:border-zinc-700 dark:bg-zinc-800/40">
            <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $record->created_at->format('M d, Y') }}</span>
            @if ($record->residential)
                <flux:badge color="violet">Residential {{ $record->residential }}</flux:badge>
            @endif
            <span class="ml-auto flex items-center gap-1.5 text-sm text-zinc-500">
                <flux:icon name="user" class="size-4" />{{ $record->recorder?->name ?? '—' }}
                <span class="text-zinc-400">&bull;</span>{{ $record->created_at->diffForHumans() }}
            </span>
        </div>

        <div class="rounded-lg border border-blue-100 bg-blue-50/60 px-5 py-2.5 dark:border-blue-900/40 dark:bg-blue-950/20">
            <div class="flex flex-wrap gap-x-8 gap-y-1 text-sm">
                <div><span class="text-zinc-400">AHCCCS ID:</span> <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $record->resident->ahcccs_id ?? '—' }}</span></div>
                <div><span class="text-zinc-400">Admitted:</span> <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $record->resident->admission_date->format('M d, Y') }}</span></div>
                @if ($record->discharge_date)
                    <div><span class="text-zinc-400">Discharge:</span> <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $record->discharge_date->format('M d, Y') }}</span></div>
                @endif
            </div>
        </div>

        @if ($record->diagnosis)
            <flux:card class="space-y-3">
                <div class="flex items-center gap-2"><flux:icon name="document-text" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Diagnosis') }}</flux:heading></div>
                <flux:separator />
                <flux:text class="text-sm text-zinc-700 dark:text-zinc-300" style="white-space: pre-wrap;">{{ $record->diagnosis }}</flux:text>
            </flux:card>
        @endif

        @foreach ($this->dimensions as $i => $dim)
            @php
                $dimKey = 'dimension_' . ($i + 1);
                $data   = $record->$dimKey ?? [];
            @endphp
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <span class="flex size-6 items-center justify-center rounded-full bg-accent/10 text-xs font-bold text-accent">{{ $i + 1 }}</span>
                    <flux:heading size="sm">{{ $dim['label'] }}</flux:heading>
                </div>
                <flux:separator />
                <div class="space-y-4">
                    @foreach ($dim['questions'] as $qi => $question)
                        @php $qKey = 'q' . ($qi + 1); @endphp
                        <div>
                            <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ $question }}</div>
                            <flux:text class="text-sm text-zinc-700 dark:text-zinc-300">{{ $data[$qKey] ?? '—' }}</flux:text>
                        </div>
                    @endforeach
                </div>
            </flux:card>
        @endforeach

        <flux:card class="space-y-3">
            <div class="flex items-center gap-2"><flux:icon name="chart-bar" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Assessment') }}</flux:heading></div>
            <flux:separator />
            @if ($record->asam_score)
                <div><div class="mb-1 text-xs text-zinc-400">{{ __('ASAM Score') }}</div><flux:text class="text-sm text-zinc-700 dark:text-zinc-300" style="white-space: pre-wrap;">{{ $record->asam_score }}</flux:text></div>
            @endif
            @if ($record->level_of_care)
                <div><div class="mb-1 text-xs text-zinc-400">{{ __('Level of Care') }}</div><flux:text class="text-sm text-zinc-700 dark:text-zinc-300" style="white-space: pre-wrap;">{{ $record->level_of_care }}</flux:text></div>
            @endif
            @if ($record->comment)
                <div><div class="mb-1 text-xs text-zinc-400">{{ __('Comment') }}</div><flux:text class="text-sm text-zinc-700 dark:text-zinc-300" style="white-space: pre-wrap;">{{ $record->comment }}</flux:text></div>
            @endif
        </flux:card>

        <flux:card class="space-y-3">
            <div class="flex items-center gap-2"><flux:icon name="users" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Signers') }}</flux:heading></div>
            <flux:separator />
            @if (count($this->signerNames) > 0)
                <div class="flex flex-wrap gap-2">@foreach ($this->signerNames as $name)<flux:badge color="blue">{{ $name }}</flux:badge>@endforeach</div>
            @else
                <flux:text class="text-sm text-zinc-400">{{ __('No signers.') }}</flux:text>
            @endif
        </flux:card>

        <flux:card class="space-y-3">
            <div class="flex items-center gap-2"><flux:icon name="pencil" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Signature') }}</flux:heading></div>
            <flux:separator />
            @php $sigUri = $record->signature?->getDataUri() ?? $record->raw_signature_data; @endphp
            @if ($sigUri)
                <div class="flex items-start gap-5">
                    <div class="rounded-md bg-white p-3 dark:bg-zinc-900"><img src="{{ $sigUri }}" alt="Signature" class="max-h-20 max-w-52 object-contain" /></div>
                    <div><p class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $record->recorder?->name ?? '—' }}</p><p class="text-xs text-zinc-400">{{ $record->created_at->format('M d, Y g:i A') }}</p></div>
                </div>
            @else
                <flux:badge color="zinc">{{ __('Not signed') }}</flux:badge>
            @endif
        </flux:card>

    </div>
</flux:main>
