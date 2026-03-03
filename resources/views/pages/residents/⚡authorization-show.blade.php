<?php

use App\Models\Authorization;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Authorization for Release of Information')]
class extends Component {
    #[Locked]
    public int $recordId;

    public function mount(Authorization $authorization): void
    {
        abort_unless(auth()->user()->can('view-residents'), 403);
        $this->recordId = $authorization->id;
    }

    #[Computed]
    public function record(): Authorization
    {
        return Authorization::with(['resident', 'recorder', 'employeeSignature'])->findOrFail($this->recordId);
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
                <flux:button variant="ghost" :href="route('residents.authorizations.index', $record->resident_id)" wire:navigate icon="arrow-left" />
                <div>
                    <flux:heading size="xl">{{ __('Authorization for Release of Information') }}</flux:heading>
                    <flux:subheading>{{ $record->resident->full_name }}</flux:subheading>
                </div>
            </div>
            <a href="{{ route('authorizations.export.pdf', $record->id) }}" target="_blank">
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
            <div class="flex items-center gap-2"><flux:icon name="share" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Release To') }}</flux:heading></div>
            <flux:separator />
            <div class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
                @foreach (['recipient_person_agency' => 'Person / Agency', 'agency_name' => 'Agency Name', 'recipient_phone' => 'Phone', 'recipient_fax' => 'Fax', 'recipient_email' => 'Email'] as $field => $label)
                    <div><div class="text-xs text-zinc-400">{{ __($label) }}</div><div class="font-medium text-zinc-700 dark:text-zinc-300">{{ $record->$field ?? '—' }}</div></div>
                @endforeach
            </div>
            @if ($record->recipient_address)
                <div><div class="text-xs text-zinc-400">{{ __('Address') }}</div><div class="text-sm text-zinc-700 dark:text-zinc-300">{{ $record->recipient_address }}</div></div>
            @endif
        </flux:card>

        @if (!empty($record->information_released))
            <flux:card class="space-y-3">
                <div class="flex items-center gap-2"><flux:icon name="clipboard-document-list" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Information Released') }}</flux:heading></div>
                <flux:separator />
                <div class="flex flex-wrap gap-2">
                    @foreach ($record->information_released as $item)
                        <flux:badge color="blue">{{ $item }}</flux:badge>
                    @endforeach
                </div>
            </flux:card>
        @endif

        <flux:card class="space-y-3">
            <div class="flex items-center gap-2"><flux:icon name="clock" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Purpose & Expiration') }}</flux:heading></div>
            <flux:separator />
            @if ($record->purpose)
                <div><div class="mb-1 text-xs text-zinc-400">{{ __('Purpose') }}</div><flux:text class="text-sm text-zinc-700 dark:text-zinc-300" style="white-space: pre-wrap;">{{ $record->purpose }}</flux:text></div>
            @endif
            <div>
                <div class="text-xs text-zinc-400">{{ __('Expiration') }}</div>
                <div class="font-medium text-zinc-700 dark:text-zinc-300">
                    @match ($record->expiration_type)
                        'one_year' => __('One Year from Today'),
                        'sixty_days' => __('60 Days from Today'),
                        'specific_date' => __('Specific Date: ') . ($record->expiration_date?->format('M d, Y') ?? '—'),
                        'other' => __('Other: ') . ($record->expiration_other ?? '—'),
                        default => '—'
                    @endmatch
                </div>
            </div>
        </flux:card>

        <div class="grid gap-5 sm:grid-cols-2">
            <flux:card class="space-y-3">
                <div class="flex items-center gap-2"><flux:icon name="pencil" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Employee Signature') }}</flux:heading></div>
                <flux:separator />
                @php $empUri = $record->employeeSignature?->getDataUri() ?? $record->employee_raw_signature_data; @endphp
                @if ($empUri)
                    <div class="rounded-md bg-white p-3 dark:bg-zinc-900"><img src="{{ $empUri }}" alt="Employee Signature" class="max-h-20 max-w-full object-contain" /></div>
                    <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">{{ $record->recorder?->name ?? '—' }}</p>
                @else
                    <flux:badge color="zinc">{{ __('Not signed') }}</flux:badge>
                @endif
            </flux:card>

            <flux:card class="space-y-3">
                <div class="flex items-center gap-2"><flux:icon name="user" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Resident Signature') }}</flux:heading></div>
                <flux:separator />
                @if ($record->resident_raw_signature_data)
                    <div class="rounded-md bg-white p-3 dark:bg-zinc-900"><img src="{{ $record->resident_raw_signature_data }}" alt="Resident Signature" class="max-h-20 max-w-full object-contain" /></div>
                    <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">{{ $record->resident->full_name }}</p>
                @else
                    <flux:badge color="zinc">{{ __('Not signed') }}</flux:badge>
                @endif
            </flux:card>
        </div>

        @if ($record->witness)
            <flux:card class="space-y-3">
                <div class="flex items-center gap-2"><flux:icon name="eye" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Witness') }}</flux:heading></div>
                <flux:separator />
                <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $record->witness }}</p>
            </flux:card>
        @endif

        <flux:card class="space-y-3">
            <div class="flex items-center gap-2"><flux:icon name="users" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Signers') }}</flux:heading></div>
            <flux:separator />
            @if (count($this->signerNames) > 0)
                <div class="flex flex-wrap gap-2">@foreach ($this->signerNames as $name)<flux:badge color="blue">{{ $name }}</flux:badge>@endforeach</div>
            @else
                <flux:text class="text-sm text-zinc-400">{{ __('No signers selected.') }}</flux:text>
            @endif
        </flux:card>

    </div>
</flux:main>
