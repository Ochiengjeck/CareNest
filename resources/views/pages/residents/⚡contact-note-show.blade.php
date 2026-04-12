<?php

use App\Models\ContactNote;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Contact Report')]
class extends Component {
    #[Locked]
    public int $recordId;

    public function mount(ContactNote $contactNote): void
    {
        abort_unless(auth()->user()->can('view-residents'), 403);
        $this->recordId = $contactNote->id;
    }

    #[Computed]
    public function record(): ContactNote
    {
        return ContactNote::with(['resident', 'recorder', 'signature'])->findOrFail($this->recordId);
    }

    #[Computed]
    public function signerNames(): array
    {
        $signers = $this->record->signers ?? [];
        return empty($signers) ? [] : User::whereIn('id', $signers)->orderBy('name')->pluck('name')->toArray();
    }
}; ?>

<flux:main>
    @php $record = $this->record; @endphp
    <div class="max-w-4xl space-y-5">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                <flux:button variant="ghost" :href="route('residents.contact-notes.index', $record->resident_id)" wire:navigate icon="arrow-left" />
                <div>
                    <flux:heading size="xl">{{ __('Contact Report') }}</flux:heading>
                    <flux:subheading>{{ $record->resident->full_name }}</flux:subheading>
                </div>
            </div>
            <a href="{{ route('contact-notes.export.pdf', $record->id) }}" target="_blank">
                <flux:button variant="outline" icon="arrow-down-tray">{{ __('Download PDF') }}</flux:button>
            </a>
        </div>

        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-zinc-200 bg-zinc-50 px-5 py-3 dark:border-zinc-700 dark:bg-zinc-800/40">
            <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $record->created_at->format('M d, Y') }}</span>
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

        @if ($record->diagnosis)
            <flux:card class="space-y-3">
                <div class="flex items-center gap-2"><flux:icon name="document-text" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Diagnosis') }}</flux:heading></div>
                <flux:separator />
                <flux:text class="text-sm text-zinc-700 dark:text-zinc-300" style="white-space: pre-wrap;">{{ $record->diagnosis }}</flux:text>
            </flux:card>
        @endif

        <flux:card class="space-y-3">
            <div class="flex items-center gap-2"><flux:icon name="phone" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Contact Details') }}</flux:heading></div>
            <flux:separator />
            <div class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
                <div><div class="text-xs text-zinc-400">{{ __('Date') }}</div><div class="font-medium text-zinc-700 dark:text-zinc-300">{{ $record->contact_date->format('M d, Y') }}</div></div>
                <div><div class="text-xs text-zinc-400">{{ __('Time') }}</div><div class="font-medium text-zinc-700 dark:text-zinc-300">{{ $record->contact_time ?? '—' }}</div></div>
                <div><div class="text-xs text-zinc-400">{{ __('Contact Name') }}</div><div class="font-medium text-zinc-700 dark:text-zinc-300">{{ $record->contact_name ?? '—' }}</div></div>
            </div>
            @if (!empty($record->person_contacted))
                <div>
                    <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ __('Person Contacted') }}</div>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($record->person_contacted as $person)
                            <flux:badge color="blue">{{ $person }}</flux:badge>
                        @endforeach
                    </div>
                </div>
            @endif
            @if (!empty($record->mode_of_contact))
                <div>
                    <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ __('Mode of Contact') }}</div>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($record->mode_of_contact as $mode)
                            <flux:badge color="violet">{{ $mode }}</flux:badge>
                        @endforeach
                    </div>
                </div>
            @endif
            @if ($record->mode_other)
                <div class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Other:') }} {{ $record->mode_other }}</div>
            @endif
        </flux:card>

        <flux:card class="space-y-3">
            <div class="flex items-center gap-2"><flux:icon name="pencil-square" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Summary') }}</flux:heading></div>
            <flux:separator />
            @if ($record->contact_summary)
                <flux:text class="text-sm text-zinc-700 dark:text-zinc-300" style="white-space: pre-wrap;">{{ $record->contact_summary }}</flux:text>
            @endif
            <div class="flex items-center gap-2">
                <span class="text-sm text-zinc-500">{{ __('Emergency Issue:') }}</span>
                @if ($record->emergency_issue === true)
                    <flux:badge color="red">Yes</flux:badge>
                @elseif ($record->emergency_issue === false)
                    <flux:badge color="zinc">No</flux:badge>
                @else
                    <flux:badge color="zinc">Not specified</flux:badge>
                @endif
            </div>
        </flux:card>

        <flux:card class="space-y-3">
            <div class="flex items-center gap-2"><flux:icon name="users" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Signers') }}</flux:heading></div>
            <flux:separator />
            @if (count($this->signerNames) > 0)
                <div class="flex flex-wrap gap-2">@foreach ($this->signerNames as $name)<flux:badge color="blue">{{ $name }}</flux:badge>@endforeach</div>
            @else
                <flux:text class="text-sm text-zinc-400">{{ __('No signers selected.') }}</flux:text>
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
