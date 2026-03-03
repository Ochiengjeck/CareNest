<?php

use App\Models\SafetyPlan;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Safety Plan')]
class extends Component {
    #[Locked]
    public int $recordId;

    public function mount(SafetyPlan $safetyPlan): void
    {
        abort_unless(auth()->user()->can('view-residents'), 403);
        $this->recordId = $safetyPlan->id;
    }

    #[Computed]
    public function record(): SafetyPlan
    {
        return SafetyPlan::with(['resident', 'recorder', 'signature'])->findOrFail($this->recordId);
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
                <flux:button variant="ghost" :href="route('residents.safety-plans.index', $record->resident_id)" wire:navigate icon="arrow-left" />
                <div>
                    <flux:heading size="xl">{{ __('Safety Plan') }}</flux:heading>
                    <flux:subheading>{{ $record->resident->full_name }}</flux:subheading>
                </div>
            </div>
            <a href="{{ route('safety-plans.export.pdf', $record->id) }}" target="_blank">
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

        @php
            $steps = [
                ['n' => 1, 'label' => 'Warning Signs', 'data' => $record->warning_signs ?? [], 'type' => 'strings'],
                ['n' => 2, 'label' => 'Coping Strategies', 'data' => $record->coping_strategies ?? [], 'type' => 'strings'],
                ['n' => 3, 'label' => 'Distracting People', 'data' => $record->distraction_people ?? [], 'type' => 'people'],
                ['n' => 4, 'label' => 'Help Contacts', 'data' => $record->help_people ?? [], 'type' => 'people'],
            ];
        @endphp

        @foreach ($steps as $step)
            @if (!empty($step['data']))
                <flux:card class="space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="flex size-6 items-center justify-center rounded-full bg-zinc-100 text-xs font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">{{ $step['n'] }}</span>
                        <flux:heading size="sm">{{ __($step['label']) }}</flux:heading>
                    </div>
                    <flux:separator />
                    @if ($step['type'] === 'strings')
                        <ol class="space-y-1 pl-4 text-sm text-zinc-700 dark:text-zinc-300 list-decimal">
                            @foreach ($step['data'] as $item)
                                @if ($item) <li>{{ $item }}</li> @endif
                            @endforeach
                        </ol>
                    @else
                        <div class="space-y-2">
                            @foreach ($step['data'] as $person)
                                @if (!empty($person['name']) || !empty($person['phone']))
                                    <div class="flex flex-wrap gap-x-6 gap-y-1 rounded-lg border border-zinc-200 p-3 text-sm dark:border-zinc-700">
                                        @if (!empty($person['name']))<div><span class="text-zinc-400">Name:</span> <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $person['name'] }}</span></div>@endif
                                        @if (!empty($person['phone']))<div><span class="text-zinc-400">Phone:</span> <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $person['phone'] }}</span></div>@endif
                                        @if (!empty($person['relationship']))<div><span class="text-zinc-400">Relationship:</span> <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $person['relationship'] }}</span></div>@endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </flux:card>
            @endif
        @endforeach

        @if (!empty($record->distraction_places))
            <flux:card class="space-y-3">
                <div class="flex items-center gap-2"><flux:icon name="map-pin" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Distracting Places') }}</flux:heading></div>
                <flux:separator />
                <ol class="space-y-1 pl-4 text-sm text-zinc-700 dark:text-zinc-300 list-decimal">
                    @foreach ($record->distraction_places as $place)
                        @if ($place) <li>{{ $place }}</li> @endif
                    @endforeach
                </ol>
            </flux:card>
        @endif

        @if (!empty($record->crisis_professionals))
            <flux:card class="space-y-3">
                <div class="flex items-center gap-2"><flux:icon name="phone" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Crisis Professionals') }}</flux:heading></div>
                <flux:separator />
                <div class="space-y-2">
                    @foreach ($record->crisis_professionals as $pro)
                        @if (!empty($pro['facility_name']) || !empty($pro['clinician_name']))
                            <div class="flex flex-wrap gap-x-6 gap-y-1 rounded-lg border border-zinc-200 p-3 text-sm dark:border-zinc-700">
                                @if (!empty($pro['facility_name']))<div><span class="text-zinc-400">Facility:</span> <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $pro['facility_name'] }}</span></div>@endif
                                @if (!empty($pro['phone']))<div><span class="text-zinc-400">Phone:</span> <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $pro['phone'] }}</span></div>@endif
                                @if (!empty($pro['clinician_name']))<div><span class="text-zinc-400">Clinician:</span> <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $pro['clinician_name'] }}</span></div>@endif
                                @if (!empty($pro['relationship']))<div><span class="text-zinc-400">Role:</span> <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $pro['relationship'] }}</span></div>@endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </flux:card>
        @endif

        @if ($record->environment_safety)
            <flux:card class="space-y-3">
                <div class="flex items-center gap-2"><flux:icon name="shield-check" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Environment Safety') }}</flux:heading></div>
                <flux:separator />
                <flux:text class="text-sm text-zinc-700 dark:text-zinc-300" style="white-space: pre-wrap;">{{ $record->environment_safety }}</flux:text>
            </flux:card>
        @endif

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
