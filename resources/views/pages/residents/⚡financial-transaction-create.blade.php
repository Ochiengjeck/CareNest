<?php

use App\Concerns\FinancialTransactionValidationRules;
use App\Models\FinancialTransactionRecord;
use App\Models\Resident;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('New Resident Financial Record')]
class extends Component {
    use FinancialTransactionValidationRules;

    #[Locked]
    public int $residentId;

    public string $diagnosis = '';
    public array $entries = [];
    public array $signers = [];
    public ?int $signature_id = null;
    public string $rawSignatureData = '';

    public function mount(Resident $resident): void
    {
        abort_unless(auth()->user()->can('manage-residents'), 403);
        $this->residentId = $resident->id;
        $this->addEntry();
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

    #[Computed]
    public function availableUsers()
    {
        return User::orderBy('name')->get(['id', 'name']);
    }

    #[Computed]
    public function runningBalances(): array
    {
        $running  = 0;
        $balances = [];

        foreach ($this->entries as $entry) {
            $running  += (float) ($entry['deposit'] ?? 0) - (float) ($entry['money_spent'] ?? 0);
            $balances[] = $running;
        }

        return $balances;
    }

    public function addEntry(): void
    {
        $this->entries[] = [
            'date'        => now()->format('Y-m-d'),
            'deposit'     => '',
            'money_spent' => '',
            'description' => '',
        ];
    }

    public function removeEntry(int $index): void
    {
        array_splice($this->entries, $index, 1);
        $this->entries = array_values($this->entries);
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
            'name'           => 'Resident Financial Record — ' . now()->format('M d, Y'),
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
            $this->addError('signature', 'A signature is required to save this record.');
            return;
        }

        $validated = $this->validate($this->financialTransactionRules());

        FinancialTransactionRecord::create([
            'resident_id'        => $this->residentId,
            'diagnosis'          => $validated['diagnosis'] ?? null,
            'entries'            => $validated['entries'] ?? [],
            'signers'            => $validated['signers'] ?? [],
            'signature_id'       => $validated['signature_id'],
            'raw_signature_data' => ($this->signature_id === null && $this->rawSignatureData !== '')
                ? $this->rawSignatureData
                : null,
            'recorded_by'        => auth()->id(),
        ]);

        session()->flash('status', 'Resident financial record saved successfully.');
        $this->redirect(route('residents.financial-transactions.index', $this->residentId), navigate: true);
    }
}; ?>

<flux:main>
    <div class="max-w-4xl space-y-1">

        {{-- Header --}}
        <div class="mb-6 flex items-center gap-3">
            <flux:button variant="ghost" :href="route('residents.financial-transactions.index', $this->residentId)" wire:navigate icon="arrow-left" />
            <div>
                <flux:heading size="xl">{{ __('New Resident Financial Record') }}</flux:heading>
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
                @if ($this->resident->ahcccs_id)
                    <div class="flex items-center gap-1.5">
                        <flux:icon name="identification" class="size-4 text-blue-400" />
                        <span class="text-zinc-500 dark:text-zinc-400">AHCCCS ID:</span>
                        <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $this->resident->ahcccs_id }}</span>
                    </div>
                @else
                    <div class="flex items-center gap-1.5">
                        <flux:icon name="identification" class="size-4 text-blue-400" />
                        <span class="text-zinc-500 dark:text-zinc-400">AHCCCS ID:</span>
                        <span class="font-medium text-zinc-400 dark:text-zinc-500">—</span>
                    </div>
                @endif
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
            </div>
        </div>

        <form wire:submit="save" class="space-y-4">

            {{-- Diagnosis --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="document-text" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Diagnosis') }}</flux:heading>
                </div>
                <flux:separator />
                <flux:textarea
                    wire:model="diagnosis"
                    :label="__('Diagnosis (specify if new or continuing)')"
                    rows="3"
                    placeholder="{{ __('Enter diagnosis information...') }}"
                />
            </flux:card>

            {{-- Transactions table --}}
            <flux:card class="space-y-4">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <flux:icon name="banknotes" class="size-5 text-accent" />
                        <flux:heading size="sm">{{ __('Transactions') }}</flux:heading>
                    </div>
                </div>
                <flux:separator />

                @error('entries')
                    <flux:text class="text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                @enderror

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px] text-sm">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                <th class="w-32 py-2 pr-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Date') }}</th>
                                <th class="w-28 px-2 py-2 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Deposit ($)') }}</th>
                                <th class="w-28 px-2 py-2 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Money Spent ($)') }}</th>
                                <th class="w-28 px-2 py-2 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Balance ($)') }}</th>
                                <th class="py-2 pl-2 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Description') }}</th>
                                <th class="w-10 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach ($entries as $i => $entry)
                                @php $balance = $this->runningBalances[$i] ?? 0; @endphp
                                <tr wire:key="entry-{{ $i }}">
                                    <td class="py-2 pr-2">
                                        <input
                                            type="date"
                                            wire:model="entries.{{ $i }}.date"
                                            class="w-full rounded-md border border-zinc-300 bg-white px-2 py-1.5 text-sm text-zinc-800 focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200"
                                        />
                                        @error("entries.{$i}.date")
                                            <div class="mt-0.5 text-xs text-red-500">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td class="px-2 py-2">
                                        <input
                                            type="number"
                                            wire:model.live="entries.{{ $i }}.deposit"
                                            min="0"
                                            step="0.01"
                                            placeholder="0.00"
                                            class="w-full rounded-md border border-zinc-300 bg-white px-2 py-1.5 text-sm text-zinc-800 focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200"
                                        />
                                    </td>
                                    <td class="px-2 py-2">
                                        <input
                                            type="number"
                                            wire:model.live="entries.{{ $i }}.money_spent"
                                            min="0"
                                            step="0.01"
                                            placeholder="0.00"
                                            class="w-full rounded-md border border-zinc-300 bg-white px-2 py-1.5 text-sm text-zinc-800 focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200"
                                        />
                                    </td>
                                    <td class="px-2 py-2">
                                        <span @class([
                                            'block rounded-md px-2 py-1.5 text-sm font-semibold text-center',
                                            'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400' => $balance >= 0,
                                            'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400' => $balance < 0,
                                        ])>
                                            ${{ number_format($balance, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-2 py-2">
                                        <input
                                            type="text"
                                            wire:model="entries.{{ $i }}.description"
                                            placeholder="{{ __('Enter description...') }}"
                                            class="w-full rounded-md border border-zinc-300 bg-white px-2 py-1.5 text-sm text-zinc-800 placeholder-zinc-400 focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:placeholder-zinc-500"
                                        />
                                    </td>
                                    <td class="py-2 pl-2">
                                        @if (count($entries) > 1)
                                            <button
                                                type="button"
                                                wire:click="removeEntry({{ $i }})"
                                                class="flex size-7 items-center justify-center rounded-md text-zinc-400 hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-900/20 dark:hover:text-red-400"
                                                title="{{ __('Remove row') }}"
                                            >
                                                <flux:icon name="x-mark" class="size-4" />
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <flux:button type="button" variant="outline" size="sm" icon="plus" wire:click="addEntry">
                    {{ __('Add Row') }}
                </flux:button>
            </flux:card>

            {{-- Signers --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="users" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Signers') }}</flux:heading>
                </div>
                <flux:separator />
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Select staff members who are signers on this record.') }}
                </flux:text>
                <div class="flex flex-wrap gap-2">
                    @foreach ($this->availableUsers as $user)
                        <label wire:key="signer-{{ $user->id }}" class="cursor-pointer select-none">
                            <input type="checkbox" wire:model="signers" value="{{ $user->id }}" class="sr-only" />
                            <span
                                class="inline-block rounded-full border px-3 py-1.5 text-sm font-medium transition"
                                :class="$wire.signers.includes({{ $user->id }})
                                    ? 'border-accent bg-accent/10 text-accent ring-1 ring-accent/30'
                                    : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600'"
                            >{{ $user->name }}</span>
                        </label>
                    @endforeach
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
                        {{ __('Draw your signature below. You can apply it to this record only, or save it to your profile for future use.') }}
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
                                        <p class="text-xs text-green-700 dark:text-green-400">{{ __('This signature will be used for this record only.') }}</p>
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
                                        {{ __('Use for This Record') }}
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
                                {{ __('"Use for This Record" applies only to this record. "Save to Profile & Use" also adds it to your signature library.') }}
                            </flux:text>
                        </div>
                    </div>
                @else
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Select your signature for this record. You can manage signatures in Profile Settings.') }}
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
                <flux:button variant="ghost" :href="route('residents.financial-transactions.index', $this->residentId)" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="primary" type="submit" icon="check">
                    {{ __('Save Record') }}
                </flux:button>
            </div>

        </form>
    </div>
</flux:main>
