<?php

use App\Models\AdlTrackingForm;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('ADL Form')]
class extends Component {
    #[Locked]
    public int $formId;

    public function mount(AdlTrackingForm $adlForm): void
    {
        abort_unless(auth()->user()->can('view-residents'), 403);
        $this->formId = $adlForm->id;
    }

    #[Computed]
    public function form(): AdlTrackingForm
    {
        return AdlTrackingForm::with(['resident', 'recorder', 'signature'])->findOrFail($this->formId);
    }
}; ?>

<flux:main>
    @php
        $form    = $this->form;
        $items   = AdlTrackingForm::adlItems();
        $entries = $form->entries ?? [];

        $levelColors = [
            'no_assistance'       => 'green',
            'some_assistance'     => 'amber',
            'complete_assistance' => 'orange',
            'not_applicable'      => 'zinc',
            'refused'             => 'red',
        ];

        $levelLabels = AdlTrackingForm::levelLabels();
    @endphp

    <div class="max-w-4xl space-y-5">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                <flux:button variant="ghost" :href="route('residents.adl.index', $form->resident_id)" wire:navigate icon="arrow-left" />
                <div>
                    <flux:heading size="xl">{{ __('ADL Form') }}</flux:heading>
                    <flux:subheading>{{ $form->resident->full_name }}</flux:subheading>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('adl.export.pdf', $form->id) }}" target="_blank">
                    <flux:button variant="outline" icon="arrow-down-tray">
                        {{ __('Download PDF') }}
                    </flux:button>
                </a>
            </div>
        </div>

        {{-- Info ribbon --}}
        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-zinc-200 bg-zinc-50 px-5 py-3 dark:border-zinc-700 dark:bg-zinc-800/40">
            <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $form->form_date->format('M d, Y') }}</span>
            <span class="ml-auto flex items-center gap-1.5 text-sm text-zinc-500 dark:text-zinc-400">
                <flux:icon name="user" class="size-4" />
                {{ $form->recorder?->name ?? '—' }}
                <span class="text-zinc-400">&bull;</span>
                {{ $form->created_at->diffForHumans() }}
            </span>
        </div>

        {{-- Resident bar --}}
        <div class="rounded-lg border border-blue-100 bg-blue-50/60 px-5 py-2.5 dark:border-blue-900/40 dark:bg-blue-950/20">
            <div class="flex flex-wrap gap-x-8 gap-y-1 text-sm">
                <div><span class="text-zinc-400">DOB:</span> <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $form->resident->date_of_birth->format('M d, Y') }}</span></div>
                <div><span class="text-zinc-400">Admitted:</span> <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $form->resident->admission_date->format('M d, Y') }}</span></div>
                @if ($form->resident->room_number)
                    <div><span class="text-zinc-400">Room:</span> <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $form->resident->room_number }}</span></div>
                @endif
            </div>
        </div>

        {{-- ADL Table --}}
        <flux:card class="space-y-4">
            <div class="flex items-center gap-2">
                <flux:icon name="list-bullet" class="size-5 text-zinc-400" />
                <flux:heading size="sm">{{ __('Activities of Daily Living') }}</flux:heading>
            </div>
            <flux:separator />

            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700">
                            <th class="w-48 py-2 pr-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('ADL') }}</th>
                            <th class="py-2 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Level') }}</th>
                            <th class="w-24 py-2 pl-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Initials') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($items as $key => $label)
                            @php
                                $entry    = $entries[$key] ?? [];
                                $level    = $entry['level'] ?? '';
                                $initials = $entry['initials'] ?? '';
                                $color    = $levelColors[$level] ?? 'zinc';
                                $lvlLabel = $levelLabels[$level] ?? '—';
                            @endphp
                            <tr wire:key="row-{{ $key }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                                <td class="py-2.5 pr-3 font-medium text-zinc-700 dark:text-zinc-300">{{ $label }}</td>
                                <td class="py-2.5">
                                    @if ($level)
                                        <flux:badge size="sm" :color="$color">{{ $lvlLabel }}</flux:badge>
                                    @else
                                        <span class="text-sm text-zinc-400">—</span>
                                    @endif
                                </td>
                                <td class="py-2.5 pl-3 text-zinc-600 dark:text-zinc-400">{{ $initials ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="pt-3 text-xs italic text-zinc-400 dark:text-zinc-500">
                                {{ __('Staff members are to initial once ADLs is completed on each shift.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </flux:card>

        {{-- Signature --}}
        <flux:card class="space-y-3">
            <div class="flex items-center gap-2">
                <flux:icon name="pencil" class="size-5 text-zinc-400" />
                <flux:heading size="sm">{{ __('Signature') }}</flux:heading>
            </div>
            <flux:separator />
            @php
                $sigUri = $form->signature?->getDataUri() ?? $form->raw_signature_data;
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
                        <p class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $form->recorder?->name ?? '—' }}</p>
                        <p class="text-xs text-zinc-400 dark:text-zinc-500">{{ $form->created_at->format('M d, Y g:i A') }}</p>
                    </div>
                </div>
            @else
                <div class="flex items-center gap-2">
                    <flux:badge color="zinc">{{ __('Not signed') }}</flux:badge>
                    <flux:text class="text-sm text-zinc-400">{{ __('No digital signature was attached to this form.') }}</flux:text>
                </div>
            @endif
        </flux:card>

    </div>
</flux:main>
