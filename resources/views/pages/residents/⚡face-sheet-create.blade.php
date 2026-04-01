<?php

use App\Concerns\FaceSheetValidationRules;
use App\Models\FaceSheet;
use App\Models\Resident;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('New Face Sheet')]
class extends Component {
    use FaceSheetValidationRules;

    #[Locked]
    public int $residentId;

    public string $diagnosis = '';
    public string $facility_address = '';
    public string $facility_phone = '';
    public string $place_of_birth = '';
    public string $eye_color = '';
    public string $race = '';
    public string $height = '';
    public string $weight = '';
    public string $hair_color = '';
    public string $identifiable_marks = '';
    public string $primary_language = '';
    public string $court_ordered = '';
    public string $family_emergency_contact = '';
    public string $facility_emergency_contact = '';
    public string $medication_allergies = '';
    public string $other_allergies = '';
    public string $pcp_name = '';
    public string $pcp_phone = '';
    public string $pcp_address = '';
    public string $specialist_1_type = '';
    public string $specialist_1_name = '';
    public string $specialist_1_phone = '';
    public string $specialist_1_address = '';
    public string $psych_name = '';
    public string $psych_phone = '';
    public string $psych_address = '';
    public string $specialist_2_type = '';
    public string $specialist_2_name = '';
    public string $specialist_2_phone = '';
    public string $specialist_2_address = '';
    public string $preferred_hospital = '';
    public string $preferred_hospital_phone = '';
    public string $preferred_hospital_address = '';
    public string $health_plan = '';
    public string $health_plan_id = '';
    public string $case_manager_name = '';
    public string $case_manager_phone = '';
    public string $case_manager_email = '';
    public string $ss_rep_payee = '';
    public string $ss_rep_phone = '';
    public string $ss_rep_email = '';
    public string $mental_health_diagnoses = '';
    public string $medical_diagnoses = '';
    public string $past_surgeries = '';
    public array $signers = [];
    public ?int $signature_id = null;
    public string $rawSignatureData = '';

    public function mount(Resident $resident): void
    {
        abort_unless(auth()->user()->can('manage-residents'), 403);
        $this->residentId = $resident->id;
    }

    #[Computed] public function resident(): Resident { return Resident::findOrFail($this->residentId); }
    #[Computed] public function mySignatures() { return auth()->user()->signatures()->orderByDesc('is_active')->orderBy('name')->get(); }
    #[Computed] public function availableUsers() { return User::orderBy('name')->get(['id', 'name']); }

    public function useSignatureOnly(string $dataUrl, string $penColor): void
    {
        if (! str_starts_with($dataUrl, 'data:image/')) return;
        $this->rawSignatureData = $dataUrl;
    }

    public function useAndSaveSignature(string $dataUrl, string $penColor): void
    {
        if (! str_starts_with($dataUrl, 'data:image/')) return;
        $user    = auth()->user();
        $isFirst = ! $user->signatures()->exists();
        $sig = $user->signatures()->create([
            'name'           => 'Face Sheet — ' . now()->format('M d, Y'),
            'pen_color'      => $penColor,
            'signature_data' => $dataUrl,
            'is_active'      => $isFirst,
        ]);
        $this->signature_id    = $sig->id;
        $this->rawSignatureData = '';
        unset($this->mySignatures);
    }

    public function clearInlineSignature(): void { $this->rawSignatureData = ''; }

    public function save(): void
    {
        $v = $this->validate($this->faceSheetRules());
        FaceSheet::create(array_merge(
            ['resident_id' => $this->residentId, 'recorded_by' => auth()->id()],
            array_filter($v, fn($val) => $val !== null),
            [
                'court_ordered'      => $this->court_ordered !== '' ? (bool) $this->court_ordered : null,
                'signers'            => $v['signers'] ?? [],
                'signature_id'      => $this->signature_id,
                'raw_signature_data'=> ($this->signature_id === null && $this->rawSignatureData !== '') ? $this->rawSignatureData : null,
            ]
        ));
        session()->flash('status', 'Face sheet saved successfully.');
        $this->redirect(route('residents.face-sheets.index', $this->residentId), navigate: true);
    }
}; ?>

<flux:main>
    <div class="max-w-4xl space-y-1">

        <div class="mb-6 flex items-center gap-3">
            <flux:button variant="ghost" :href="route('residents.face-sheets.index', $this->residentId)" wire:navigate icon="arrow-left" />
            <div>
                <flux:heading size="xl">{{ __('New Face Sheet') }}</flux:heading>
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
                <div class="flex items-center gap-1.5">
                    <flux:icon name="calendar" class="size-4 text-blue-400" />
                    <span class="text-zinc-500 dark:text-zinc-400">Admitted:</span>
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $this->resident->admission_date->format('M d, Y') }}</span>
                </div>
            </div>
        </div>

        <form wire:submit="save" class="space-y-4">

            <flux:card class="space-y-4">
                <div class="flex items-center gap-2"><flux:icon name="document-text" class="size-5 text-accent" /><flux:heading size="sm">{{ __('Diagnosis') }}</flux:heading></div>
                <flux:separator />
                <flux:textarea wire:model="diagnosis" :label="__('Diagnosis')" rows="3" />
            </flux:card>

            <flux:card class="space-y-4">
                <div class="flex items-center gap-2"><flux:icon name="building-office" class="size-5 text-accent" /><flux:heading size="sm">{{ __('Facility Information') }}</flux:heading></div>
                <flux:separator />
                <flux:textarea wire:model="facility_address" :label="__('Facility Address')" rows="2" />
                <flux:input wire:model="facility_phone" :label="__('Facility Phone')" type="tel" />
            </flux:card>

            <flux:card class="space-y-4">
                <div class="flex items-center gap-2"><flux:icon name="user" class="size-5 text-accent" /><flux:heading size="sm">{{ __('Physical Description') }}</flux:heading></div>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="place_of_birth" :label="__('Place of Birth')" />
                    <flux:input wire:model="primary_language" :label="__('Primary Language')" />
                    <flux:input wire:model="race" :label="__('Race / Ethnicity')" />
                    <flux:input wire:model="eye_color" :label="__('Eye Color')" />
                    <flux:input wire:model="hair_color" :label="__('Hair Color')" />
                    <flux:input wire:model="height" :label="__('Height')" placeholder="{{ __('e.g. 5\'10\"') }}" />
                    <flux:input wire:model="weight" :label="__('Weight')" placeholder="{{ __('e.g. 160 lbs') }}" />
                </div>
                <flux:textarea wire:model="identifiable_marks" :label="__('Identifiable Marks / Tattoos')" rows="2" />
                <div>
                    <flux:label>{{ __('Court Ordered') }}</flux:label>
                    <div class="mt-2 flex gap-2">
                        @foreach (['1' => 'Yes', '0' => 'No'] as $val => $label)
                            <label class="cursor-pointer select-none">
                                <input type="radio" wire:model.live="court_ordered" value="{{ $val }}" class="sr-only" />
                                <span @class(['inline-block rounded-full border px-4 py-1.5 text-sm font-medium transition', 'border-accent bg-accent/10 text-accent ring-1 ring-accent/30' => $court_ordered === $val, 'border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-400' => $court_ordered !== $val])>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </flux:card>

            <flux:card class="space-y-4">
                <div class="flex items-center gap-2"><flux:icon name="phone" class="size-5 text-accent" /><flux:heading size="sm">{{ __('Emergency Contacts') }}</flux:heading></div>
                <flux:separator />
                <flux:textarea wire:model="family_emergency_contact" :label="__('Family Emergency Contact')" rows="2" placeholder="{{ __('Name, relationship, phone...') }}" />
                <flux:input wire:model="facility_emergency_contact" :label="__('Facility Emergency Contact')" />
            </flux:card>

            <flux:card class="space-y-4">
                <div class="flex items-center gap-2"><flux:icon name="exclamation-triangle" class="size-5 text-accent" /><flux:heading size="sm">{{ __('Allergies') }}</flux:heading></div>
                <flux:separator />
                <flux:textarea wire:model="medication_allergies" :label="__('Medication Allergies')" rows="2" />
                <flux:textarea wire:model="other_allergies" :label="__('Other Allergies')" rows="2" />
            </flux:card>

            <flux:card class="space-y-4">
                <div class="flex items-center gap-2"><flux:icon name="heart" class="size-5 text-accent" /><flux:heading size="sm">{{ __('Primary Care Provider') }}</flux:heading></div>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="pcp_name" :label="__('PCP Name')" />
                    <flux:input wire:model="pcp_phone" :label="__('PCP Phone')" type="tel" />
                </div>
                <flux:textarea wire:model="pcp_address" :label="__('PCP Address')" rows="2" />
            </flux:card>

            <flux:card class="space-y-4">
                <div class="flex items-center gap-2"><flux:icon name="user-plus" class="size-5 text-accent" /><flux:heading size="sm">{{ __('Other Specialist 1') }}</flux:heading></div>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="specialist_1_type" :label="__('Specialty Type')" placeholder="{{ __('e.g. Cardiologist') }}" />
                    <flux:input wire:model="specialist_1_name" :label="__('Name')" />
                    <flux:input wire:model="specialist_1_phone" :label="__('Phone')" type="tel" />
                </div>
                <flux:textarea wire:model="specialist_1_address" :label="__('Address')" rows="2" />
            </flux:card>

            <flux:card class="space-y-4">
                <div class="flex items-center gap-2"><flux:icon name="sparkles" class="size-5 text-accent" /><flux:heading size="sm">{{ __('Psychiatric Provider') }}</flux:heading></div>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="psych_name" :label="__('Name')" />
                    <flux:input wire:model="psych_phone" :label="__('Phone')" type="tel" />
                </div>
                <flux:textarea wire:model="psych_address" :label="__('Address')" rows="2" />
            </flux:card>

            <flux:card class="space-y-4">
                <div class="flex items-center gap-2"><flux:icon name="user-plus" class="size-5 text-accent" /><flux:heading size="sm">{{ __('Other Specialist 2') }}</flux:heading></div>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="specialist_2_type" :label="__('Specialty Type')" />
                    <flux:input wire:model="specialist_2_name" :label="__('Name')" />
                    <flux:input wire:model="specialist_2_phone" :label="__('Phone')" type="tel" />
                </div>
                <flux:textarea wire:model="specialist_2_address" :label="__('Address')" rows="2" />
            </flux:card>

            <flux:card class="space-y-4">
                <div class="flex items-center gap-2"><flux:icon name="building-office-2" class="size-5 text-accent" /><flux:heading size="sm">{{ __('Preferred Hospital') }}</flux:heading></div>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="preferred_hospital" :label="__('Hospital Name')" />
                    <flux:input wire:model="preferred_hospital_phone" :label="__('Phone')" type="tel" />
                </div>
                <flux:textarea wire:model="preferred_hospital_address" :label="__('Address')" rows="2" />
            </flux:card>

            <flux:card class="space-y-4">
                <div class="flex items-center gap-2"><flux:icon name="credit-card" class="size-5 text-accent" /><flux:heading size="sm">{{ __('Health Plan') }}</flux:heading></div>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="health_plan" :label="__('Health Plan Name')" />
                    <flux:input wire:model="health_plan_id" :label="__('Health Plan ID / Member #')" />
                </div>
            </flux:card>

            <flux:card class="space-y-4">
                <div class="flex items-center gap-2"><flux:icon name="briefcase" class="size-5 text-accent" /><flux:heading size="sm">{{ __('Case Manager') }}</flux:heading></div>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-3">
                    <flux:input wire:model="case_manager_name" :label="__('Name')" />
                    <flux:input wire:model="case_manager_phone" :label="__('Phone')" type="tel" />
                    <flux:input wire:model="case_manager_email" :label="__('Email')" type="email" />
                </div>
            </flux:card>

            <flux:card class="space-y-4">
                <div class="flex items-center gap-2"><flux:icon name="banknotes" class="size-5 text-accent" /><flux:heading size="sm">{{ __('SS Rep Payee') }}</flux:heading></div>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-3">
                    <flux:input wire:model="ss_rep_payee" :label="__('Rep Payee Name')" />
                    <flux:input wire:model="ss_rep_phone" :label="__('Phone')" type="tel" />
                    <flux:input wire:model="ss_rep_email" :label="__('Email')" type="email" />
                </div>
            </flux:card>

            <flux:card class="space-y-4">
                <div class="flex items-center gap-2"><flux:icon name="clipboard-document-list" class="size-5 text-accent" /><flux:heading size="sm">{{ __('Diagnoses & Medical History') }}</flux:heading></div>
                <flux:separator />
                <flux:textarea wire:model="mental_health_diagnoses" :label="__('Mental Health Diagnoses')" rows="3" />
                <flux:textarea wire:model="medical_diagnoses" :label="__('Medical Diagnoses')" rows="3" />
                <flux:textarea wire:model="past_surgeries" :label="__('Past Surgeries')" rows="3" />
            </flux:card>

            {{-- Signers --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2"><flux:icon name="users" class="size-5 text-accent" /><flux:heading size="sm">{{ __('Signers') }}</flux:heading></div>
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

            {{-- Signature (optional) --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2"><flux:icon name="pencil" class="size-5 text-accent" /><flux:heading size="sm">{{ __('Signature') }} <span class="ml-1 text-xs font-normal text-zinc-400">({{ __('optional') }})</span></flux:heading></div>
                <flux:separator />
                @if ($this->mySignatures->isEmpty())
                    <div
                        x-data="{
                            drawnUri: @js($rawSignatureData),
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
                                    <flux:icon name="check-circle" class="size-5 shrink-0 text-green-600" />
                                    <p class="text-sm font-semibold text-green-800 dark:text-green-300">{{ __('Signature applied') }}</p>
                                </div>
                                <button type="button" class="shrink-0 text-xs text-green-700 underline dark:text-green-400" x-on:click="drawnUri = ''; clear(); $wire.call('clearInlineSignature')">{{ __('Redo') }}</button>
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
                                <canvas x-ref="padCanvas" style="width: 100%; height: 180px; cursor: crosshair; touch-action: none; display: block;"></canvas>
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
                                <input type="radio" wire:model.number="signature_id" value="{{ $sig->id }}" class="sr-only" />
                                <div class="rounded-xl border-2 p-3 transition" :class="$wire.signature_id === {{ $sig->id }} ? 'border-accent bg-accent/5 ring-2 ring-accent/20' : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700'">
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

            <div class="flex justify-end gap-3 pb-4">
                <flux:button variant="ghost" :href="route('residents.face-sheets.index', $this->residentId)" wire:navigate>{{ __('Cancel') }}</flux:button>
                <flux:button variant="primary" type="submit" icon="check">{{ __('Save Face Sheet') }}</flux:button>
            </div>

        </form>
    </div>
</flux:main>
