<?php

use App\Concerns\AdlFormValidationRules;
use App\Models\AdlTrackingForm;
use App\Models\Resident;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('New ADL Form')]
class extends Component {
    use AdlFormValidationRules;

    #[Locked]
    public int $residentId;

    public string $form_date = '';
    public array $entries = [];
    public ?int $signature_id = null;
    public string $rawSignatureData = '';

    public function mount(Resident $resident): void
    {
        abort_unless(auth()->user()->can('manage-residents'), 403);
        $this->residentId = $resident->id;
        $this->form_date  = now()->format('Y-m-d');

        foreach (array_keys(AdlTrackingForm::adlItems()) as $key) {
            $this->entries[$key] = ['level' => '', 'initials' => ''];
        }
    }

    #[Computed]
    public function resident(): Resident
    {
        return Resident::findOrFail($this->residentId);
    }

    #[Computed]
    public function mySignatures()
    {
        return auth()->user()->signatures()->orderByDesc('is_active')->orderBy('name')->get();
    }

    #[Computed]
    public function isSigned(): bool
    {
        return $this->signature_id !== null || $this->rawSignatureData !== '';
    }

    public function useSignatureOnly(string $dataUrl, string $penColor): void
    {
        if (! str_starts_with($dataUrl, 'data:image/')) {
            return;
        }

        $this->rawSignatureData = $dataUrl;
    }

    public function useAndSaveSignature(string $dataUrl, string $penColor): void
    {
        if (! str_starts_with($dataUrl, 'data:image/')) {
            return;
        }

        $user    = auth()->user();
        $isFirst = ! $user->signatures()->exists();

        $sig = $user->signatures()->create([
            'name'           => 'ADL Form — ' . now()->format('M d, Y'),
            'pen_color'      => $penColor,
            'signature_data' => $dataUrl,
            'is_active'      => $isFirst,
        ]);

        $this->signature_id   = $sig->id;
        $this->rawSignatureData = '';
        unset($this->mySignatures);
    }

    public function clearInlineSignature(): void
    {
        $this->rawSignatureData = '';
    }

    public function save(): void
    {
        if (! $this->isSigned) {
            $this->addError('signature', 'A signature is required to save this form.');
            return;
        }

        $validated = $this->validate($this->adlFormRules());

        AdlTrackingForm::create([
            'resident_id'        => $this->residentId,
            'form_date'          => $validated['form_date'],
            'entries'            => $validated['entries'] ?? [],
            'signature_id'       => $validated['signature_id'],
            'raw_signature_data' => ($this->signature_id === null && $this->rawSignatureData !== '')
                ? $this->rawSignatureData
                : null,
            'recorded_by'        => auth()->id(),
        ]);

        session()->flash('status', 'ADL form saved successfully.');
        $this->redirect(route('residents.adl.index', $this->residentId), navigate: true);
    }
}; ?>

<flux:main>
    <div class="max-w-4xl space-y-1">

        {{-- Header --}}
        <div class="mb-6 flex items-center gap-3">
            <flux:button variant="ghost" :href="route('residents.adl.index', $this->residentId)" wire:navigate icon="arrow-left" />
            <div>
                <flux:heading size="xl">{{ __('New ADL Form') }}</flux:heading>
                <flux:subheading>{{ $this->resident->full_name }}</flux:subheading>
            </div>
        </div>

        {{-- Resident info bar --}}
        <div class="mb-6 rounded-xl border border-blue-100 bg-blue-50/60 px-5 py-3 dark:border-blue-900/40 dark:bg-blue-950/20">
            <div class="flex flex-wrap gap-x-8 gap-y-1 text-sm">
                <div class="flex items-center gap-1.5">
                    <flux:icon name="user" class="size-4 text-blue-400" />
                    <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $this->resident->full_name }}</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <flux:icon name="cake" class="size-4 text-blue-400" />
                    <span class="text-zinc-500 dark:text-zinc-400">DOB:</span>
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $this->resident->date_of_birth->format('M d, Y') }}</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <flux:icon name="calendar" class="size-4 text-blue-400" />
                    <span class="text-zinc-500 dark:text-zinc-400">Admitted:</span>
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $this->resident->admission_date->format('M d, Y') }}</span>
                </div>
                @if ($this->resident->room_number)
                    <div class="flex items-center gap-1.5">
                        <flux:icon name="home" class="size-4 text-blue-400" />
                        <span class="text-zinc-500 dark:text-zinc-400">Room:</span>
                        <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $this->resident->room_number }}</span>
                    </div>
                @endif
                <div class="flex items-center gap-1.5">
                    <flux:icon name="clock" class="size-4 text-blue-400" />
                    <span class="text-zinc-500 dark:text-zinc-400">Today:</span>
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ now()->format('m/d/Y') }}</span>
                </div>
            </div>
        </div>

        <form wire:submit="save" class="space-y-4">

            {{-- Date --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="calendar" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Form Date') }}</flux:heading>
                </div>
                <flux:separator />
                <flux:input wire:model="form_date" :label="__('Date')" type="date" required class="max-w-xs" />
            </flux:card>

            {{-- ADL Table --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="list-bullet" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Activities of Daily Living') }}</flux:heading>
                </div>
                <flux:separator />

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px] text-sm">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                <th class="w-48 py-2 pr-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('ADL') }}</th>
                                @foreach (['no_assistance' => 'No Assistance', 'some_assistance' => 'Some Assistance', 'complete_assistance' => 'Complete Assistance', 'not_applicable' => 'Not Applicable', 'refused' => 'Refused'] as $val => $col)
                                    <th class="px-2 py-2 text-center text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ $col }}</th>
                                @endforeach
                                <th class="w-24 py-2 pl-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Initials') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach (AdlTrackingForm::adlItems() as $key => $label)
                                <tr wire:key="adl-{{ $key }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                                    <td class="py-2.5 pr-3 font-medium text-zinc-700 dark:text-zinc-300">{{ $label }}</td>
                                    @foreach (['no_assistance', 'some_assistance', 'complete_assistance', 'not_applicable', 'refused'] as $level)
                                        <td class="px-2 py-2.5 text-center">
                                            <label class="cursor-pointer">
                                                <input
                                                    type="radio"
                                                    wire:model="entries.{{ $key }}.level"
                                                    value="{{ $level }}"
                                                    class="sr-only"
                                                />
                                                <span
                                                    class="inline-flex size-5 items-center justify-center rounded-full border-2 transition"
                                                    :class="$wire.entries['{{ $key }}']?.level === '{{ $level }}'
                                                        ? 'border-accent bg-accent'
                                                        : 'border-zinc-300 hover:border-zinc-400 dark:border-zinc-600'"
                                                >
                                                    <span
                                                        class="size-2 rounded-full bg-white transition"
                                                        :class="$wire.entries['{{ $key }}']?.level === '{{ $level }}' ? 'opacity-100' : 'opacity-0'"
                                                    ></span>
                                                </span>
                                            </label>
                                        </td>
                                    @endforeach
                                    <td class="py-2.5 pl-3">
                                        <input
                                            type="text"
                                            wire:model="entries.{{ $key }}.initials"
                                            maxlength="10"
                                            placeholder="e.g. JD"
                                            class="w-20 rounded-md border border-zinc-300 bg-white px-2 py-1 text-sm text-zinc-800 placeholder-zinc-400 focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:placeholder-zinc-500"
                                        />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7" class="pt-3 text-xs italic text-zinc-400 dark:text-zinc-500">
                                    {{ __('Staff members are to initial once ADLs is completed on each shift.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </flux:card>

            {{-- Signature --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="pencil" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Signature') }}</flux:heading>
                </div>
                <flux:separator />

                @if ($this->mySignatures->isEmpty())
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Draw your signature below. You can apply it to this form only, or save it to your profile for future use.') }}
                    </flux:text>

                    <div
                        x-data="{
                            drawnUri: @js($rawSignatureData),
                            pad: null,
                            isEmpty: true,
                            penColor: '#000000',
                            get hasDrawing() { return this.drawnUri !== ''; },
                            initPad(canvas) {
                                if (this.pad) return;
                                canvas.width = canvas.offsetWidth;
                                canvas.height = 180;
                                this.pad = new SignaturePad(canvas, {
                                    backgroundColor: 'rgb(255, 255, 255)',
                                    penColor: this.penColor,
                                    minWidth: 1,
                                    maxWidth: 3,
                                });
                                this.pad.addEventListener('beginStroke', () => { this.isEmpty = false; });
                                this.$watch('penColor', c => { if (this.pad) this.pad.penColor = c; });
                            },
                            clear() { if (this.pad) { this.pad.clear(); this.isEmpty = true; } },
                            applyOnly() {
                                if (!this.pad || this.pad.isEmpty()) return;
                                const uri = this.pad.toDataURL('image/png');
                                this.drawnUri = uri;
                                $wire.call('useSignatureOnly', uri, this.penColor);
                            },
                            applyAndSave() {
                                if (!this.pad || this.pad.isEmpty()) return;
                                const uri = this.pad.toDataURL('image/png');
                                this.drawnUri = uri;
                                $wire.call('useAndSaveSignature', uri, this.penColor);
                            }
                        }"
                        x-init="$nextTick(() => { const c = $refs.padCanvas; if (c) initPad(c); })"
                        wire:ignore
                    >
                        <div x-show="hasDrawing" class="mb-3 rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-700/40 dark:bg-green-900/20">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-2">
                                    <flux:icon name="check-circle" class="size-5 shrink-0 text-green-600 dark:text-green-400" />
                                    <div>
                                        <p class="text-sm font-semibold text-green-800 dark:text-green-300">{{ __('Signature applied') }}</p>
                                        <p class="text-xs text-green-700 dark:text-green-400">{{ __('This signature will be used for this form only.') }}</p>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    class="shrink-0 text-xs text-green-700 underline hover:no-underline dark:text-green-400"
                                    x-on:click="drawnUri = ''; clear(); $wire.call('clearInlineSignature')"
                                >{{ __('Redo') }}</button>
                            </div>
                            <div class="mt-3 rounded-md bg-white p-3 dark:bg-zinc-900">
                                <img :src="drawnUri" class="max-h-16 object-contain" alt="{{ __('Signature preview') }}" />
                            </div>
                        </div>

                        <div x-show="!hasDrawing" class="space-y-3">
                            <div class="flex flex-wrap items-center gap-3">
                                <flux:label>{{ __('Pen Color') }}</flux:label>
                                <input
                                    type="color"
                                    x-model="penColor"
                                    class="h-8 w-10 cursor-pointer rounded border border-zinc-300 bg-white p-0.5 dark:border-zinc-600 dark:bg-zinc-800"
                                    title="{{ __('Pick pen color') }}"
                                />
                                <div class="flex gap-1.5">
                                    @foreach (['#000000', '#1e40af', '#15803d', '#7c3aed', '#b91c1c'] as $preset)
                                        <button
                                            type="button"
                                            class="size-6 rounded-full border-2 border-white ring-1 ring-zinc-300 transition-shadow hover:ring-zinc-500"
                                            style="background-color: {{ $preset }}"
                                            x-on:click="penColor = '{{ $preset }}'"
                                            title="{{ $preset }}"
                                        ></button>
                                    @endforeach
                                </div>
                            </div>

                            <div class="overflow-hidden rounded-lg border-2 border-zinc-300 bg-white dark:border-zinc-600">
                                <canvas
                                    x-ref="padCanvas"
                                    style="width: 100%; height: 180px; cursor: crosshair; touch-action: none; display: block;"
                                ></canvas>
                            </div>
                            <flux:text class="text-xs text-zinc-400">{{ __('Draw your signature in the box above.') }}</flux:text>

                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <flux:button type="button" size="sm" variant="ghost" icon="arrow-path" x-on:click="clear()">
                                    {{ __('Clear') }}
                                </flux:button>
                                <div class="flex gap-2">
                                    <flux:button
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        x-on:click="applyOnly()"
                                        x-bind:disabled="isEmpty"
                                    >
                                        {{ __('Use for This Form') }}
                                    </flux:button>
                                    <flux:button
                                        type="button"
                                        size="sm"
                                        variant="primary"
                                        x-on:click="applyAndSave()"
                                        x-bind:disabled="isEmpty"
                                    >
                                        {{ __('Save to Profile & Use') }}
                                    </flux:button>
                                </div>
                            </div>
                            <flux:text class="text-xs text-zinc-400">
                                {{ __('"Use for This Form" applies only to this record. "Save to Profile & Use" also adds it to your signature library.') }}
                            </flux:text>
                        </div>
                    </div>
                @else
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Select your signature for this form. You can manage signatures in Profile Settings.') }}
                    </flux:text>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach ($this->mySignatures as $sig)
                            <label class="cursor-pointer" wire:key="sig-{{ $sig->id }}">
                                <input type="radio" wire:model.number="signature_id" value="{{ $sig->id }}" class="sr-only" />
                                <div
                                    class="rounded-xl border-2 p-3 transition"
                                    :class="$wire.signature_id === {{ $sig->id }}
                                        ? 'border-accent bg-accent/5 ring-2 ring-accent/20'
                                        : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600'"
                                >
                                    <div class="flex h-16 items-center justify-center rounded-lg bg-white p-2 dark:bg-zinc-900">
                                        <img
                                            src="{{ $sig->getDataUri() }}"
                                            alt="{{ $sig->name }}"
                                            class="max-h-full max-w-full object-contain"
                                        />
                                    </div>
                                    <div class="mt-2 flex items-center gap-1.5">
                                        <span
                                            class="size-3 shrink-0 rounded-full border border-zinc-200 dark:border-zinc-600"
                                            style="background-color: {{ $sig->pen_color }}"
                                        ></span>
                                        <span class="flex-1 truncate text-xs font-medium text-zinc-700 dark:text-zinc-300">{{ $sig->name }}</span>
                                    </div>
                                    @if ($sig->is_active)
                                        <div class="mt-1.5">
                                            <flux:badge size="sm" color="blue">{{ __('Default') }}</flux:badge>
                                        </div>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('signature_id')
                        <flux:text class="text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                    @enderror
                @endif

                @error('signature')
                    <flux:text class="text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                @enderror
            </flux:card>

            {{-- Actions --}}
            <div class="flex justify-end gap-3 pb-4">
                <flux:button variant="ghost" :href="route('residents.adl.index', $this->residentId)" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="primary" type="submit" icon="check">
                    {{ __('Save Form') }}
                </flux:button>
            </div>

        </form>
    </div>
</flux:main>
