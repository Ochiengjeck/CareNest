<?php

use App\Models\FaceSheet;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Face Sheet')]
class extends Component {
    #[Locked]
    public int $recordId;

    public function mount(FaceSheet $faceSheet): void
    {
        abort_unless(auth()->user()->can('view-residents'), 403);
        $this->recordId = $faceSheet->id;
    }

    #[Computed]
    public function record(): FaceSheet
    {
        return FaceSheet::with(['resident', 'recorder', 'signature'])->findOrFail($this->recordId);
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
                <flux:button variant="ghost" :href="route('residents.face-sheets.index', $record->resident_id)" wire:navigate icon="arrow-left" />
                <div>
                    <flux:heading size="xl">{{ __('Face Sheet') }}</flux:heading>
                    <flux:subheading>{{ $record->resident->full_name }}</flux:subheading>
                </div>
            </div>
            <a href="{{ route('face-sheets.export.pdf', $record->id) }}" target="_blank">
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

        @php
            $field = fn($v) => $v ?? '—';
        @endphp

        @if ($record->diagnosis)
            <flux:card class="space-y-3">
                <div class="flex items-center gap-2"><flux:icon name="document-text" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Diagnosis') }}</flux:heading></div>
                <flux:separator />
                <flux:text class="text-sm text-zinc-700 dark:text-zinc-300" style="white-space: pre-wrap;">{{ $record->diagnosis }}</flux:text>
            </flux:card>
        @endif

        <flux:card class="space-y-3">
            <div class="flex items-center gap-2"><flux:icon name="user" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Physical Description') }}</flux:heading></div>
            <flux:separator />
            <div class="grid grid-cols-2 gap-3 text-sm sm:grid-cols-3">
                @foreach (['place_of_birth' => 'Place of Birth', 'primary_language' => 'Primary Language', 'race' => 'Race', 'eye_color' => 'Eye Color', 'hair_color' => 'Hair Color', 'height' => 'Height', 'weight' => 'Weight'] as $key => $label)
                    <div><div class="text-xs text-zinc-400">{{ __($label) }}</div><div class="font-medium text-zinc-700 dark:text-zinc-300">{{ $field($record->$key) }}</div></div>
                @endforeach
                @if ($record->identifiable_marks)
                    <div class="col-span-full"><div class="text-xs text-zinc-400">{{ __('Identifiable Marks') }}</div><div class="text-sm text-zinc-700 dark:text-zinc-300">{{ $record->identifiable_marks }}</div></div>
                @endif
                <div><div class="text-xs text-zinc-400">{{ __('Court Ordered') }}</div><div class="font-medium text-zinc-700 dark:text-zinc-300">{{ $record->court_ordered === true ? 'Yes' : ($record->court_ordered === false ? 'No' : '—') }}</div></div>
            </div>
        </flux:card>

        <flux:card class="space-y-3">
            <div class="flex items-center gap-2"><flux:icon name="phone" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Emergency Contacts') }}</flux:heading></div>
            <flux:separator />
            <div class="grid gap-3 text-sm sm:grid-cols-2">
                <div><div class="text-xs text-zinc-400">{{ __('Family') }}</div><div class="text-zinc-700 dark:text-zinc-300">{{ $field($record->family_emergency_contact) }}</div></div>
                <div><div class="text-xs text-zinc-400">{{ __('Facility') }}</div><div class="text-zinc-700 dark:text-zinc-300">{{ $field($record->facility_emergency_contact) }}</div></div>
            </div>
        </flux:card>

        @foreach ([
            ['icon' => 'heart', 'title' => 'Primary Care Provider', 'fields' => ['pcp_name' => 'Name', 'pcp_phone' => 'Phone', 'pcp_address' => 'Address']],
            ['icon' => 'brain', 'title' => 'Psychiatric Provider', 'fields' => ['psych_name' => 'Name', 'psych_phone' => 'Phone', 'psych_address' => 'Address']],
            ['icon' => 'building-office-2', 'title' => 'Preferred Hospital', 'fields' => ['preferred_hospital' => 'Name', 'preferred_hospital_phone' => 'Phone', 'preferred_hospital_address' => 'Address']],
        ] as $section)
            <flux:card class="space-y-3">
                <div class="flex items-center gap-2"><flux:icon name="{{ $section['icon'] }}" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __($section['title']) }}</flux:heading></div>
                <flux:separator />
                <div class="grid gap-3 text-sm sm:grid-cols-3">
                    @foreach ($section['fields'] as $key => $label)
                        @if ($record->$key)
                            <div><div class="text-xs text-zinc-400">{{ __($label) }}</div><div class="text-zinc-700 dark:text-zinc-300">{{ $record->$key }}</div></div>
                        @endif
                    @endforeach
                </div>
            </flux:card>
        @endforeach

        <flux:card class="space-y-3">
            <div class="flex items-center gap-2"><flux:icon name="credit-card" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Health Plan & Case Manager') }}</flux:heading></div>
            <flux:separator />
            <div class="grid gap-3 text-sm sm:grid-cols-3">
                @foreach (['health_plan' => 'Health Plan', 'health_plan_id' => 'Plan ID', 'case_manager_name' => 'Case Manager', 'case_manager_phone' => 'CM Phone', 'case_manager_email' => 'CM Email', 'ss_rep_payee' => 'SS Rep Payee', 'ss_rep_phone' => 'Rep Phone', 'ss_rep_email' => 'Rep Email'] as $key => $label)
                    @if ($record->$key)
                        <div><div class="text-xs text-zinc-400">{{ __($label) }}</div><div class="text-zinc-700 dark:text-zinc-300">{{ $record->$key }}</div></div>
                    @endif
                @endforeach
            </div>
        </flux:card>

        <flux:card class="space-y-4">
            <div class="flex items-center gap-2"><flux:icon name="clipboard-document-list" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Diagnoses & History') }}</flux:heading></div>
            <flux:separator />
            @foreach (['mental_health_diagnoses' => 'Mental Health Diagnoses', 'medical_diagnoses' => 'Medical Diagnoses', 'past_surgeries' => 'Past Surgeries'] as $key => $label)
                @if ($record->$key)
                    <div><div class="mb-1 text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ __($label) }}</div><flux:text class="text-sm text-zinc-700 dark:text-zinc-300" style="white-space: pre-wrap;">{{ $record->$key }}</flux:text></div>
                @endif
            @endforeach
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
