<?php

use App\Models\MentalStatusExam;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Mental Status Exam')]
class extends Component {
    #[Locked]
    public int $recordId;

    public function mount(MentalStatusExam $mentalStatusExam): void
    {
        abort_unless(auth()->user()->can('view-residents'), 403);
        $this->recordId = $mentalStatusExam->id;
    }

    #[Computed]
    public function record(): MentalStatusExam
    {
        return MentalStatusExam::with(['resident', 'recorder', 'signature'])->findOrFail($this->recordId);
    }

    #[Computed]
    public function signerNames(): array
    {
        $signers = $this->record->signers ?? [];
        return empty($signers) ? [] : User::whereIn('id', $signers)->orderBy('name')->pluck('name')->toArray();
    }

    #[Computed]
    public function categories(): array { return MentalStatusExam::categories(); }
}; ?>

<flux:main>
    @php $record = $this->record; @endphp
    <div class="max-w-4xl space-y-5">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                <flux:button variant="ghost" :href="route('residents.mental-status.index', $record->resident_id)" wire:navigate icon="arrow-left" />
                <div>
                    <flux:heading size="xl">{{ __('Mental Status Exam') }}</flux:heading>
                    <flux:subheading>{{ $record->resident->full_name }}</flux:subheading>
                </div>
            </div>
            <a href="{{ route('mental-status.export.pdf', $record->id) }}" target="_blank">
                <flux:button variant="outline" icon="arrow-down-tray">{{ __('Download PDF') }}</flux:button>
            </a>
        </div>

        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-zinc-200 bg-zinc-50 px-5 py-3 dark:border-zinc-700 dark:bg-zinc-800/40">
            <span class="font-semibold text-zinc-800 dark:text-zinc-100">Exam: {{ $record->exam_date->format('M d, Y') }}</span>
            <span class="ml-auto flex items-center gap-1.5 text-sm text-zinc-500">
                <flux:icon name="user" class="size-4" />{{ $record->recorder?->name ?? '—' }}
                <span class="text-zinc-400">&bull;</span>{{ $record->created_at->diffForHumans() }}
            </span>
        </div>

        <div class="rounded-lg border border-blue-100 bg-blue-50/60 px-5 py-2.5 dark:border-blue-900/40 dark:bg-blue-950/20">
            <div class="flex flex-wrap gap-x-8 gap-y-1 text-sm">
                <div><span class="text-zinc-400">AHCCCS ID:</span> <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $record->resident->ahcccs_id ?? '—' }}</span></div>
                <div><span class="text-zinc-400">DOB:</span> <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $record->resident->date_of_birth->format('M d, Y') }}</span></div>
            </div>
        </div>

        @php
            $sections = [
                ['label' => 'Before Appointment', 'data' => $record->before_appointment ?? []],
                ['label' => 'After Appointment', 'data' => $record->after_appointment ?? []],
            ];
        @endphp

        @foreach ($sections as $section)
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="face-smile" class="size-5 text-zinc-400" />
                    <flux:heading size="sm">{{ __($section['label']) }}</flux:heading>
                </div>
                <flux:separator />
                <div class="space-y-4">
                    @foreach ($this->categories as $cat)
                        @php $selected = $section['data'][$cat['key']] ?? []; @endphp
                        @if (!empty($selected))
                            <div>
                                <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ $cat['label'] }}</div>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($selected as $item)
                                        <flux:badge color="blue">{{ $item }}</flux:badge>
                                    @endforeach
                                    @php $otherKey = $cat['key'] . '_other'; @endphp
                                    @if (!empty($section['data'][$otherKey]))
                                        <flux:badge color="zinc">Other: {{ $section['data'][$otherKey] }}</flux:badge>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </flux:card>
        @endforeach

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
            <div class="flex items-center gap-2"><flux:icon name="pencil" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Employee Signature') }}</flux:heading></div>
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
