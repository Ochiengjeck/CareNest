<?php

use App\Concerns\SafetyPlanValidationRules;
use App\Models\Resident;
use App\Models\SafetyPlan;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('New Crisis Plan')]
class extends Component {
    use SafetyPlanValidationRules;

    #[Locked]
    public int $residentId;

    public string $diagnosis = '';
    public array $warning_signs = ['', '', ''];
    public array $coping_strategies = ['', '', ''];
    public array $distraction_people = [
        ['name' => '', 'phone' => '', 'relationship' => ''],
        ['name' => '', 'phone' => '', 'relationship' => ''],
    ];
    public array $distraction_places = ['', ''];
    public array $help_people = [
        ['name' => '', 'phone' => '', 'relationship' => ''],
    ];
    public array $crisis_professionals = [
        ['facility_name' => '', 'phone' => '', 'clinician_name' => '', 'relationship' => ''],
        ['facility_name' => '', 'phone' => '', 'clinician_name' => '', 'relationship' => ''],
    ];
    public string $environment_safety = '';
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
            'name'           => 'Crisis Plan — ' . now()->format('M d, Y'),
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
        $v = $this->validate($this->safetyPlanRules());
        SafetyPlan::create([
            'resident_id'         => $this->residentId,
            'diagnosis'           => $v['diagnosis'] ?? null,
            'warning_signs'       => $this->warning_signs,
            'coping_strategies'   => $this->coping_strategies,
            'distraction_people'  => $this->distraction_people,
            'distraction_places'  => $this->distraction_places,
            'help_people'         => $this->help_people,
            'crisis_professionals'=> $this->crisis_professionals,
            'environment_safety'  => $v['environment_safety'] ?? null,
            'signers'             => $v['signers'] ?? [],
            'signature_id'       => $this->signature_id,
            'raw_signature_data' => ($this->signature_id === null && $this->rawSignatureData !== '') ? $this->rawSignatureData : null,
            'recorded_by'        => auth()->id(),
        ]);
        session()->flash('status', 'Crisis plan saved successfully.');
        $this->redirect(route('residents.safety-plans.index', $this->residentId), navigate: true);
    }
}; ?>

<flux:main>
    <div class="max-w-4xl space-y-1">

        <div class="mb-6 flex items-center gap-3">
            <flux:button variant="ghost" :href="route('residents.safety-plans.index', $this->residentId)" wire:navigate icon="arrow-left" />
            <div>
                <flux:heading size="xl">{{ __('New Crisis Plan') }}</flux:heading>
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

            {{-- Step 1: Warning Signs --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <span class="flex size-6 items-center justify-center rounded-full bg-amber-100 text-xs font-bold text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">1</span>
                    <flux:heading size="sm">{{ __('Warning Signs') }}</flux:heading>
                </div>
                <flux:separator />
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('List warning signs that a crisis may be developing.') }}</flux:text>
                @foreach ($warning_signs as $i => $sign)
                    <flux:input wire:model="warning_signs.{{ $i }}" :label="__('Warning Sign :n', ['n' => $i + 1])" placeholder="{{ __('Describe warning sign...') }}" />
                @endforeach
            </flux:card>

            {{-- Step 2: Coping Strategies --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <span class="flex size-6 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">2</span>
                    <flux:heading size="sm">{{ __('Coping Strategies') }}</flux:heading>
                </div>
                <flux:separator />
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Internal coping strategies — things I can do to take my mind off problems.') }}</flux:text>
                @foreach ($coping_strategies as $i => $strategy)
                    <flux:input wire:model="coping_strategies.{{ $i }}" :label="__('Strategy :n', ['n' => $i + 1])" placeholder="{{ __('Describe coping strategy...') }}" />
                @endforeach
            </flux:card>

            {{-- Step 3: Distractions --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <span class="flex size-6 items-center justify-center rounded-full bg-green-100 text-xs font-bold text-green-700 dark:bg-green-900/30 dark:text-green-400">3</span>
                    <flux:heading size="sm">{{ __('People and Places for Distraction') }}</flux:heading>
                </div>
                <flux:separator />
                <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('People I can contact to distract myself:') }}</flux:text>
                @foreach ($distraction_people as $i => $person)
                    <div class="grid grid-cols-3 gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                        <flux:input wire:model="distraction_people.{{ $i }}.name" :label="__('Name')" placeholder="{{ __('Full name') }}" />
                        <flux:input wire:model="distraction_people.{{ $i }}.phone" :label="__('Phone')" placeholder="{{ __('Phone number') }}" />
                        <flux:input wire:model="distraction_people.{{ $i }}.relationship" :label="__('Relationship')" placeholder="{{ __('e.g. Friend, Family') }}" />
                    </div>
                @endforeach
                <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Places I can go to distract myself:') }}</flux:text>
                @foreach ($distraction_places as $i => $place)
                    <flux:input wire:model="distraction_places.{{ $i }}" :label="__('Place :n', ['n' => $i + 1])" placeholder="{{ __('Address or location...') }}" />
                @endforeach
            </flux:card>

            {{-- Step 4: Help --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <span class="flex size-6 items-center justify-center rounded-full bg-violet-100 text-xs font-bold text-violet-700 dark:bg-violet-900/30 dark:text-violet-400">4</span>
                    <flux:heading size="sm">{{ __('People and Professionals Who Can Help') }}</flux:heading>
                </div>
                <flux:separator />
                <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Personal contacts:') }}</flux:text>
                @foreach ($help_people as $i => $person)
                    <div class="grid grid-cols-3 gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                        <flux:input wire:model="help_people.{{ $i }}.name" :label="__('Name')" placeholder="{{ __('Full name') }}" />
                        <flux:input wire:model="help_people.{{ $i }}.phone" :label="__('Phone')" placeholder="{{ __('Phone number') }}" />
                        <flux:input wire:model="help_people.{{ $i }}.relationship" :label="__('Relationship')" placeholder="{{ __('e.g. Friend, Family') }}" />
                    </div>
                @endforeach
                <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Crisis professionals:') }}</flux:text>
                @foreach ($crisis_professionals as $i => $pro)
                    <div class="grid grid-cols-2 gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                        <flux:input wire:model="crisis_professionals.{{ $i }}.facility_name" :label="__('Facility / Clinician Name')" placeholder="{{ __('Facility or clinician name') }}" />
                        <flux:input wire:model="crisis_professionals.{{ $i }}.phone" :label="__('Phone')" placeholder="{{ __('Phone number') }}" />
                        <flux:input wire:model="crisis_professionals.{{ $i }}.clinician_name" :label="__('Clinician Name')" placeholder="{{ __('Name') }}" />
                        <flux:input wire:model="crisis_professionals.{{ $i }}.relationship" :label="__('Relationship')" placeholder="{{ __('e.g. Therapist') }}" />
                    </div>
                @endforeach
                <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-700/40 dark:bg-red-900/20">
                    <flux:text class="text-sm font-semibold text-red-800 dark:text-red-300">{{ __('Emergency Resources') }}</flux:text>
                    <div class="mt-1 space-y-0.5 text-sm text-red-700 dark:text-red-400">
                        <div>988 Suicide &amp; Crisis Lifeline: <strong>Call or Text 988</strong></div>
                        <div>Emergency: <strong>911</strong></div>
                    </div>
                </div>
            </flux:card>

            {{-- Environment Safety --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="shield-check" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Making the Environment Safe') }}</flux:heading>
                </div>
                <flux:separator />
                <flux:textarea wire:model="environment_safety" :label="__('Environment Safety Notes')" rows="4" placeholder="{{ __('Describe steps to make the environment safe...') }}" />
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
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('signature_id') <flux:text class="text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text> @enderror
                @endif
                @error('signature') <flux:text class="text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text> @enderror
            </flux:card>

            <div class="flex justify-end gap-3 pb-4">
                <flux:button variant="ghost" :href="route('residents.safety-plans.index', $this->residentId)" wire:navigate>{{ __('Cancel') }}</flux:button>
                <flux:button variant="primary" type="submit" icon="check">{{ __('Save Crisis Plan') }}</flux:button>
            </div>

        </form>
    </div>
</flux:main>
