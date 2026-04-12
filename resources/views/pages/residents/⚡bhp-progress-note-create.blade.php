<?php

use App\Concerns\BhpProgressNoteValidationRules;
use App\Models\BhpProgressNote;
use App\Models\Resident;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('New BHP Progress Report')]
class extends Component {
    use BhpProgressNoteValidationRules;

    #[Locked]
    public int $residentId;

    public string $diagnosis = '';
    public string $discharge_date = '';
    public string $progress_note = '';
    public string $treatment_goals_progress = '';
    public string $sobriety_physical_health = '';
    public string $cognitive_emotional = '';
    public string $therapeutic_support = '';
    public string $progress_towards_goals = '';
    public string $barriers = '';
    public string $summary_continued_stay = '';
    public string $bhp_name_credential = '';
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
    #[Computed] public function isSigned(): bool { return $this->signature_id !== null || $this->rawSignatureData !== ''; }
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
            'name'           => 'BHP Progress Report — ' . now()->format('M d, Y'),
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
        if (! $this->isSigned) {
            $this->addError('signature', 'A signature is required to save this record.');
            return;
        }
        $v = $this->validate($this->bhpProgressNoteRules());
        BhpProgressNote::create([
            'resident_id'            => $this->residentId,
            'diagnosis'              => $v['diagnosis'] ?? null,
            'discharge_date'         => $v['discharge_date'] ?? null,
            'progress_note'          => $v['progress_note'] ?? null,
            'treatment_goals_progress' => $v['treatment_goals_progress'] ?? null,
            'sobriety_physical_health' => $v['sobriety_physical_health'] ?? null,
            'cognitive_emotional'    => $v['cognitive_emotional'] ?? null,
            'therapeutic_support'    => $v['therapeutic_support'] ?? null,
            'progress_towards_goals' => $v['progress_towards_goals'] ?? null,
            'barriers'               => $v['barriers'] ?? null,
            'summary_continued_stay' => $v['summary_continued_stay'] ?? null,
            'bhp_name_credential'    => $v['bhp_name_credential'] ?? null,
            'signers'                => $v['signers'] ?? [],
            'signature_id'          => $this->signature_id,
            'raw_signature_data'    => ($this->signature_id === null && $this->rawSignatureData !== '') ? $this->rawSignatureData : null,
            'recorded_by'           => auth()->id(),
        ]);
        session()->flash('status', 'BHP progress report saved successfully.');
        $this->redirect(route('residents.bhp-progress-notes.index', $this->residentId), navigate: true);
    }
}; ?>

<flux:main>
    <div class="max-w-4xl space-y-1">

        <div class="mb-6 flex items-center gap-3">
            <flux:button variant="ghost" :href="route('residents.bhp-progress-notes.index', $this->residentId)" wire:navigate icon="arrow-left" />
            <div>
                <flux:heading size="xl">{{ __('New BHP Progress Report') }}</flux:heading>
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
                    <flux:icon name="calendar" class="size-4 text-blue-400" />
                    <span class="text-zinc-500 dark:text-zinc-400">Admitted:</span>
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $this->resident->admission_date->format('M d, Y') }}</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <flux:icon name="arrow-right-start-on-rectangle" class="size-4 text-blue-400" />
                    <span class="text-zinc-500 dark:text-zinc-400">Discharge (if identified):</span>
                    <div class="ml-1">
                        <input type="date" wire:model="discharge_date" class="rounded-md border border-zinc-300 bg-white px-2 py-0.5 text-sm text-zinc-800 focus:border-accent focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200" />
                    </div>
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

            {{-- Progress Note --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="pencil-square" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Progress Note') }}</flux:heading>
                </div>
                <flux:separator />
                <flux:textarea wire:model="progress_note" :label="__('Progress Note')" rows="10" />
            </flux:card>

            {{-- Treatment Plan Goals --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="clipboard-document-list" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Treatment Plan Goals') }}</flux:heading>
                </div>
                <flux:separator />
                <flux:textarea wire:model="treatment_goals_progress" :label="__('Treatment Goals Progress')" rows="5" />
            </flux:card>

            {{-- Resident Progress Made --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="chart-bar" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Resident Progress Made') }}</flux:heading>
                </div>
                <flux:separator />
                <flux:textarea wire:model="sobriety_physical_health" :label="__('Sustaining Sobriety and Managing Physical Health')" rows="4" />
                <flux:textarea wire:model="cognitive_emotional" :label="__('Addressing Cognitive and Emotional Challenges')" rows="4" />
                <flux:textarea wire:model="therapeutic_support" :label="__('Continued Therapeutic Support')" rows="4" />
                <flux:textarea wire:model="progress_towards_goals" :label="__('Progress Towards Treatment Goals')" rows="4" />
                <flux:textarea wire:model="barriers" :label="__('Barriers Towards Treatment')" rows="4" />
            </flux:card>

            {{-- Summary --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="check-badge" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Summary') }}</flux:heading>
                </div>
                <flux:separator />
                <flux:textarea wire:model="summary_continued_stay" :label="__('Summary / Continued Stay Justification')" rows="5" />
                <flux:input wire:model="bhp_name_credential" :label="__('BHP Name & Credential')" placeholder="{{ __('e.g. Jane Doe, LCSW') }}" />
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

            {{-- Signature --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="pencil" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Signature') }}</flux:heading>
                </div>
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
                                    @if ($sig->is_active)
                                        <div class="mt-1"><flux:badge size="sm" color="blue">{{ __('Default') }}</flux:badge></div>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('signature_id') <flux:text class="text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text> @enderror
                @endif
                @error('signature') <flux:text class="text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text> @enderror
            </flux:card>

            <div class="flex justify-end gap-3 pb-4">
                <flux:button variant="ghost" :href="route('residents.bhp-progress-notes.index', $this->residentId)" wire:navigate>{{ __('Cancel') }}</flux:button>
                <flux:button variant="primary" type="submit" icon="check">{{ __('Save Record') }}</flux:button>
            </div>

        </form>
    </div>
</flux:main>
