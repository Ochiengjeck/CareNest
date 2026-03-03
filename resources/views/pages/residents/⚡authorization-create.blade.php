<?php

use App\Concerns\AuthorizationValidationRules;
use App\Models\Authorization;
use App\Models\Resident;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('New Authorization for Release of Information')]
class extends Component {
    use AuthorizationValidationRules;

    #[Locked]
    public int $residentId;

    public string $diagnosis = '';
    public string $recipient_person_agency = '';
    public string $recipient_address = '';
    public string $recipient_phone = '';
    public string $recipient_fax = '';
    public string $recipient_email = '';
    public string $agency_name = '';
    public array $information_released = [];
    public string $purpose = '';
    public string $expiration_type = 'one_year';
    public string $expiration_date = '';
    public string $expiration_other = '';
    public string $witness = '';
    public array $signers = [];

    // Employee signature
    public ?int $employee_signature_id = null;
    public string $employeeRawSignatureData = '';

    // Resident signature (inline only)
    public string $residentRawSignatureData = '';

    public function mount(Resident $resident): void
    {
        abort_unless(auth()->user()->can('manage-residents'), 403);
        $this->residentId = $resident->id;
    }

    #[Computed] public function resident(): Resident { return Resident::findOrFail($this->residentId); }
    #[Computed] public function mySignatures() { return auth()->user()->signatures()->orderByDesc('is_active')->orderBy('name')->get(); }
    #[Computed] public function availableUsers() { return User::orderBy('name')->get(['id', 'name']); }

    public function useEmployeeSignatureOnly(string $dataUrl, string $penColor): void
    {
        if (! str_starts_with($dataUrl, 'data:image/')) return;
        $this->employeeRawSignatureData = $dataUrl;
    }

    public function useEmployeeAndSaveSignature(string $dataUrl, string $penColor): void
    {
        if (! str_starts_with($dataUrl, 'data:image/')) return;
        $user    = auth()->user();
        $isFirst = ! $user->signatures()->exists();
        $sig = $user->signatures()->create([
            'name'           => 'Authorization — ' . now()->format('M d, Y'),
            'pen_color'      => $penColor,
            'signature_data' => $dataUrl,
            'is_active'      => $isFirst,
        ]);
        $this->employee_signature_id    = $sig->id;
        $this->employeeRawSignatureData = '';
        unset($this->mySignatures);
    }

    public function clearEmployeeSignature(): void { $this->employeeRawSignatureData = ''; }

    public function setResidentSignature(string $dataUrl): void
    {
        if (! str_starts_with($dataUrl, 'data:image/')) return;
        $this->residentRawSignatureData = $dataUrl;
    }

    public function clearResidentSignature(): void { $this->residentRawSignatureData = ''; }

    public function save(): void
    {
        $v = $this->validate($this->authorizationRules());
        Authorization::create([
            'resident_id'                  => $this->residentId,
            'diagnosis'                    => $v['diagnosis'] ?? null,
            'recipient_person_agency'      => $v['recipient_person_agency'] ?? null,
            'recipient_address'            => $v['recipient_address'] ?? null,
            'recipient_phone'              => $v['recipient_phone'] ?? null,
            'recipient_fax'                => $v['recipient_fax'] ?? null,
            'recipient_email'              => $v['recipient_email'] ?? null,
            'agency_name'                  => $v['agency_name'] ?? null,
            'information_released'         => $v['information_released'] ?? [],
            'purpose'                      => $v['purpose'] ?? null,
            'expiration_type'              => $v['expiration_type'],
            'expiration_date'              => $v['expiration_date'] ?? null,
            'expiration_other'             => $v['expiration_other'] ?? null,
            'employee_signature_id'        => $this->employee_signature_id,
            'employee_raw_signature_data'  => ($this->employee_signature_id === null && $this->employeeRawSignatureData !== '') ? $this->employeeRawSignatureData : null,
            'resident_raw_signature_data'  => $this->residentRawSignatureData ?: null,
            'witness'                      => $v['witness'] ?? null,
            'signers'                      => $v['signers'] ?? [],
            'recorded_by'                  => auth()->id(),
        ]);
        session()->flash('status', 'Authorization saved successfully.');
        $this->redirect(route('residents.authorizations.index', $this->residentId), navigate: true);
    }
}; ?>

<flux:main>
    <div class="max-w-4xl space-y-1">

        <div class="mb-6 flex items-center gap-3">
            <flux:button variant="ghost" :href="route('residents.authorizations.index', $this->residentId)" wire:navigate icon="arrow-left" />
            <div>
                <flux:heading size="xl">{{ __('Authorization for Release of Information') }}</flux:heading>
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
                @endif
                <div class="flex items-center gap-1.5">
                    <flux:icon name="cake" class="size-4 text-blue-400" />
                    <span class="text-zinc-500 dark:text-zinc-400">DOB:</span>
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $this->resident->date_of_birth->format('M d, Y') }}</span>
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
                <flux:textarea wire:model="diagnosis" :label="__('Diagnosis')" rows="3" />
            </flux:card>

            {{-- Release To --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="share" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Release To') }}</flux:heading>
                </div>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="recipient_person_agency" :label="__('Person / Agency')" placeholder="{{ __('Name or organization...') }}" />
                    <flux:input wire:model="agency_name" :label="__('Agency Name')" placeholder="{{ __('Full agency name...') }}" />
                    <flux:input wire:model="recipient_phone" :label="__('Phone')" type="tel" />
                    <flux:input wire:model="recipient_fax" :label="__('Fax')" type="tel" />
                    <flux:input wire:model="recipient_email" :label="__('Email')" type="email" />
                </div>
                <flux:textarea wire:model="recipient_address" :label="__('Address')" rows="2" />
            </flux:card>

            {{-- Notice to Recipient --}}
            <flux:card class="space-y-3">
                <div class="flex items-center gap-2">
                    <flux:icon name="information-circle" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Notice to Recipient') }}</flux:heading>
                </div>
                <flux:separator />
                <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800/40 dark:bg-blue-950/20">
                    <flux:text class="text-sm leading-relaxed text-zinc-700 dark:text-zinc-300">
                        {{ __('This information has been disclosed to you from records whose confidentiality is protected by Federal law. Federal regulations (42 CFR Part 2) prohibit you from making any further disclosure of this information unless further disclosure is expressly permitted by the written consent of the person to whom it pertains or as otherwise permitted by 42 CFR Part 2. A general authorization for the release of medical or other information is NOT sufficient for this purpose. The Federal rules restrict any use of the information to criminally investigate or prosecute any alcohol or drug abuse patient.') }}
                    </flux:text>
                </div>
            </flux:card>

            {{-- Information to Release --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="clipboard-document-list" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Information to Release') }}</flux:heading>
                </div>
                <flux:separator />
                <div class="flex flex-wrap gap-2">
                    @foreach (['Substance Use Records', 'Mental Health Records', 'Medical Records', 'Medication Records', 'Discharge Summary', 'Treatment Plan', 'Progress Notes', 'Other'] as $option)
                        <label class="cursor-pointer select-none">
                            <input type="checkbox" wire:model="information_released" value="{{ $option }}" class="sr-only" />
                            <span class="inline-block rounded-full border px-3 py-1.5 text-sm font-medium transition"
                                :class="$wire.information_released.includes('{{ $option }}') ? 'border-accent bg-accent/10 text-accent ring-1 ring-accent/30' : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400'">
                                {{ $option }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </flux:card>

            {{-- Purpose & Expiration --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="clock" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Purpose & Expiration') }}</flux:heading>
                </div>
                <flux:separator />
                <flux:textarea wire:model="purpose" :label="__('Purpose of Disclosure')" rows="3" />
                <div>
                    <flux:label>{{ __('This authorization expires:') }}</flux:label>
                    <div class="mt-2 flex flex-wrap gap-3">
                        @foreach (['one_year' => 'One Year from Today', 'sixty_days' => '60 Days from Today', 'specific_date' => 'Specific Date', 'other' => 'Other'] as $val => $label)
                            <label class="cursor-pointer select-none">
                                <input type="radio" wire:model.live="expiration_type" value="{{ $val }}" class="sr-only" />
                                <span @class([
                                    'inline-block rounded-full border px-4 py-1.5 text-sm font-medium transition',
                                    'border-accent bg-accent/10 text-accent ring-1 ring-accent/30' => $expiration_type === $val,
                                    'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400' => $expiration_type !== $val,
                                ])>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @if ($expiration_type === 'specific_date')
                        <div class="mt-3">
                            <flux:input type="date" wire:model="expiration_date" :label="__('Expiration Date')" />
                        </div>
                    @elseif ($expiration_type === 'other')
                        <div class="mt-3">
                            <flux:input wire:model="expiration_other" :label="__('Specify Expiration')" placeholder="{{ __('Describe expiration terms...') }}" />
                        </div>
                    @endif
                </div>
            </flux:card>

            {{-- Employee Signature --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="pencil" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Employee Signature') }} <span class="ml-1 text-xs font-normal text-zinc-400">({{ __('optional') }})</span></flux:heading>
                </div>
                <flux:separator />
                @if ($this->mySignatures->isEmpty())
                    <div
                        x-data="{
                            drawnUri: @js($employeeRawSignatureData),
                            pad: null, isEmpty: true, penColor: '#000000',
                            get hasDrawing() { return this.drawnUri !== ''; },
                            initPad(canvas) {
                                if (this.pad) return;
                                canvas.width = canvas.offsetWidth; canvas.height = 180;
                                this.pad = new SignaturePad(canvas, { backgroundColor: 'rgb(255,255,255)', penColor: this.penColor, minWidth: 1, maxWidth: 3 });
                                this.pad.addEventListener('beginStroke', () => { this.isEmpty = false; });
                                this.$watch('penColor', c => { if (this.pad) this.pad.penColor = c; });
                            },
                            clear() { if (this.pad) { this.pad.clear(); this.isEmpty = true; } },
                            applyOnly() {
                                if (!this.pad || this.pad.isEmpty()) return;
                                const uri = this.pad.toDataURL('image/png');
                                this.drawnUri = uri;
                                $wire.call('useEmployeeSignatureOnly', uri, this.penColor);
                            },
                            applyAndSave() {
                                if (!this.pad || this.pad.isEmpty()) return;
                                const uri = this.pad.toDataURL('image/png');
                                this.drawnUri = uri;
                                $wire.call('useEmployeeAndSaveSignature', uri, this.penColor);
                            }
                        }"
                        x-init="$nextTick(() => { const c = $refs.empCanvas; if (c) initPad(c); })"
                        wire:ignore
                    >
                        <div x-show="hasDrawing" class="mb-3 rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-700/40 dark:bg-green-900/20">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-2">
                                    <flux:icon name="check-circle" class="size-5 shrink-0 text-green-600" />
                                    <p class="text-sm font-semibold text-green-800 dark:text-green-300">{{ __('Employee signature applied') }}</p>
                                </div>
                                <button type="button" class="shrink-0 text-xs text-green-700 underline dark:text-green-400" x-on:click="drawnUri = ''; clear(); $wire.call('clearEmployeeSignature')">{{ __('Redo') }}</button>
                            </div>
                            <div class="mt-3 rounded-md bg-white p-3 dark:bg-zinc-900">
                                <img :src="drawnUri" class="max-h-16 object-contain" />
                            </div>
                        </div>
                        <div x-show="!hasDrawing" class="space-y-3">
                            <div class="flex flex-wrap items-center gap-3">
                                <flux:label>{{ __('Pen Color') }}</flux:label>
                                <input type="color" x-model="penColor" class="h-8 w-10 cursor-pointer rounded border border-zinc-300 bg-white p-0.5" />
                                <div class="flex gap-1.5">
                                    @foreach (['#000000', '#1e40af', '#15803d', '#7c3aed', '#b91c1c'] as $preset)
                                        <button type="button" class="size-6 rounded-full border-2 border-white ring-1 ring-zinc-300 hover:ring-zinc-500" style="background-color: {{ $preset }}" x-on:click="penColor = '{{ $preset }}'"></button>
                                    @endforeach
                                </div>
                            </div>
                            <div class="overflow-hidden rounded-lg border-2 border-zinc-300 bg-white dark:border-zinc-600">
                                <canvas x-ref="empCanvas" style="width: 100%; height: 180px; cursor: crosshair; touch-action: none; display: block;"></canvas>
                            </div>
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <flux:button type="button" size="sm" variant="ghost" icon="arrow-path" x-on:click="clear()">{{ __('Clear') }}</flux:button>
                                <div class="flex gap-2">
                                    <flux:button type="button" size="sm" variant="outline" x-on:click="applyOnly()" x-bind:disabled="isEmpty">{{ __('Use for This Record') }}</flux:button>
                                    <flux:button type="button" size="sm" variant="primary" x-on:click="applyAndSave()" x-bind:disabled="isEmpty">{{ __('Save to Profile & Use') }}</flux:button>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach ($this->mySignatures as $sig)
                            <label class="cursor-pointer" wire:key="sig-{{ $sig->id }}">
                                <input type="radio" wire:model.number="employee_signature_id" value="{{ $sig->id }}" class="sr-only" />
                                <div class="rounded-xl border-2 p-3 transition" :class="$wire.employee_signature_id === {{ $sig->id }} ? 'border-accent bg-accent/5 ring-2 ring-accent/20' : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700'">
                                    <div class="flex h-16 items-center justify-center rounded-lg bg-white p-2 dark:bg-zinc-900">
                                        <img src="{{ $sig->getDataUri() }}" alt="{{ $sig->name }}" class="max-h-full max-w-full object-contain" />
                                    </div>
                                    <span class="mt-2 block truncate text-xs font-medium text-zinc-700 dark:text-zinc-300">{{ $sig->name }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @endif
            </flux:card>

            {{-- Resident Signature (inline only) --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="user" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Resident Signature') }} <span class="ml-1 text-xs font-normal text-zinc-400">({{ __('optional') }})</span></flux:heading>
                </div>
                <flux:separator />
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('The resident may sign below to authorize the release of information.') }}</flux:text>
                <div
                    x-data="{
                        drawnUri: @js($residentRawSignatureData),
                        pad: null, isEmpty: true,
                        get hasDrawing() { return this.drawnUri !== ''; },
                        initPad(canvas) {
                            if (this.pad) return;
                            canvas.width = canvas.offsetWidth; canvas.height = 160;
                            this.pad = new SignaturePad(canvas, { backgroundColor: 'rgb(255,255,255)', penColor: '#000000', minWidth: 1, maxWidth: 3 });
                            this.pad.addEventListener('beginStroke', () => { this.isEmpty = false; });
                        },
                        clear() { if (this.pad) { this.pad.clear(); this.isEmpty = true; this.drawnUri = ''; $wire.call('clearResidentSignature'); } },
                        apply() {
                            if (!this.pad || this.pad.isEmpty()) return;
                            const uri = this.pad.toDataURL('image/png');
                            this.drawnUri = uri;
                            $wire.call('setResidentSignature', uri);
                        }
                    }"
                    x-init="$nextTick(() => { const c = $refs.resCanvas; if (c) initPad(c); })"
                    wire:ignore
                >
                    <div x-show="hasDrawing" class="mb-3 rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-700/40 dark:bg-green-900/20">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-2">
                                <flux:icon name="check-circle" class="size-5 shrink-0 text-green-600" />
                                <p class="text-sm font-semibold text-green-800 dark:text-green-300">{{ __('Resident signature applied') }}</p>
                            </div>
                            <button type="button" class="shrink-0 text-xs text-green-700 underline dark:text-green-400" x-on:click="clear()">{{ __('Redo') }}</button>
                        </div>
                        <div class="mt-3 rounded-md bg-white p-3 dark:bg-zinc-900">
                            <img :src="drawnUri" class="max-h-16 object-contain" />
                        </div>
                    </div>
                    <div x-show="!hasDrawing" class="space-y-3">
                        <div class="overflow-hidden rounded-lg border-2 border-zinc-300 bg-white dark:border-zinc-600">
                            <canvas x-ref="resCanvas" style="width: 100%; height: 160px; cursor: crosshair; touch-action: none; display: block;"></canvas>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <flux:button type="button" size="sm" variant="ghost" icon="arrow-path" x-on:click="clear()">{{ __('Clear') }}</flux:button>
                            <flux:button type="button" size="sm" variant="outline" x-on:click="apply()" x-bind:disabled="isEmpty">{{ __('Apply Resident Signature') }}</flux:button>
                        </div>
                    </div>
                </div>
            </flux:card>

            {{-- Witness --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="eye" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Witness') }}</flux:heading>
                </div>
                <flux:separator />
                <flux:input wire:model="witness" :label="__('Witness Name')" placeholder="{{ __('Full name of witness...') }}" />
            </flux:card>

            {{-- Signers --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="users" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Signers') }}</flux:heading>
                </div>
                <flux:separator />
                <div class="flex flex-wrap gap-2">
                    @foreach ($this->availableUsers as $user)
                        <label wire:key="signer-{{ $user->id }}" class="cursor-pointer select-none">
                            <input type="checkbox" wire:model="signers" value="{{ $user->id }}" class="sr-only" />
                            <span class="inline-block rounded-full border px-3 py-1.5 text-sm font-medium transition"
                                :class="$wire.signers.includes({{ $user->id }}) ? 'border-accent bg-accent/10 text-accent ring-1 ring-accent/30' : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-zinc-600'">
                                {{ $user->name }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </flux:card>

            <div class="flex justify-end gap-3 pb-4">
                <flux:button variant="ghost" :href="route('residents.authorizations.index', $this->residentId)" wire:navigate>{{ __('Cancel') }}</flux:button>
                <flux:button variant="primary" type="submit" icon="check">{{ __('Save Authorization') }}</flux:button>
            </div>

        </form>
    </div>
</flux:main>
