<?php

use App\Concerns\VitalValidationRules;
use App\Models\Resident;
use App\Models\Vital;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Record Vitals')]
class extends Component {
    use VitalValidationRules;

    public string $resident_id = '';
    public string $recorded_at = '';
    public ?int $blood_pressure_systolic = null;
    public ?int $blood_pressure_diastolic = null;
    public ?int $heart_rate = null;
    public ?string $temperature = null;
    public ?int $respiratory_rate = null;
    public ?int $oxygen_saturation = null;
    public ?string $blood_sugar = null;
    public ?string $weight = null;
    public ?int $pain_level = null;
    public ?string $consciousness_level = null;
    public string $notes = '';

    public function mount(): void
    {
        $this->recorded_at = now()->format('Y-m-d\TH:i');
    }

    #[Computed]
    public function residents(): array
    {
        return Resident::active()
            ->orderBy('first_name')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->id => $r->full_name . ' (Room ' . $r->room_number . ')'])
            ->toArray();
    }

    public function save(): void
    {
        $validated = $this->validate($this->vitalRules());

        $resident = Resident::findOrFail($validated['resident_id']);
        if ($resident->isInactive()) {
            $this->addError('resident_id', __('Cannot record vitals for a :status resident.', ['status' => $resident->status]));
            return;
        }

        $validated['recorded_by'] = auth()->id();

        $vital = Vital::create($validated);

        session()->flash('status', 'Vitals recorded successfully.');
        $this->redirect(route('vitals.show', $vital), navigate: true);
    }
}; ?>

<flux:main>
    <div class="max-w-3xl space-y-6">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" :href="route('vitals.index')" wire:navigate icon="arrow-left" />
            <div>
                <flux:heading size="xl">{{ __('Record Vitals') }}</flux:heading>
                <flux:subheading>{{ __('Record vital signs for a resident') }}</flux:subheading>
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            {{-- Resident & Time --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Resident & Time') }}</flux:heading>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <flux:select wire:model="resident_id" :label="__('Resident')" required>
                            <flux:select.option value="">{{ __('Select a resident...') }}</flux:select.option>
                            @foreach($this->residents as $id => $residentName)
                                <flux:select.option value="{{ $id }}">{{ $residentName }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        @error('resident_id')
                            <flux:text class="mt-1 text-sm text-red-500">{{ $message }}</flux:text>
                        @enderror
                    </div>

                    <flux:input wire:model="recorded_at" :label="__('Recorded At')" type="datetime-local" required />
                </div>
            </flux:card>

            {{-- Vital Signs --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Vital Signs') }}</flux:heading>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <flux:input wire:model="blood_pressure_systolic" :label="__('BP Systolic (mmHg)')" type="number" min="40" max="300" placeholder="120" />
                    <flux:input wire:model="blood_pressure_diastolic" :label="__('BP Diastolic (mmHg)')" type="number" min="20" max="200" placeholder="80" />
                    <flux:input wire:model="heart_rate" :label="__('Heart Rate (bpm)')" type="number" min="20" max="250" placeholder="72" />
                    <flux:input wire:model="temperature" :label="__('Temperature (°F)')" type="number" step="0.1" min="86" max="113" placeholder="98.6" />
                    <flux:input wire:model="respiratory_rate" :label="__('Respiratory Rate (/min)')" type="number" min="5" max="60" placeholder="16" />
                    <flux:input wire:model="oxygen_saturation" :label="__('SpO2 (%)')" type="number" min="0" max="100" placeholder="98" />
                    <flux:input wire:model="blood_sugar" :label="__('Blood Sugar (mmol/L)')" type="number" step="0.1" min="0" max="50" placeholder="5.5" />
                    <flux:input wire:model="weight" :label="__('Weight (kg)')" type="number" step="0.1" min="0" max="300" placeholder="70.0" />

                    <div>
                        <flux:select wire:model="pain_level" :label="__('Pain Level (0-10)')">
                            <flux:select.option value="">{{ __('Not assessed') }}</flux:select.option>
                            @for($i = 0; $i <= 10; $i++)
                                <flux:select.option value="{{ $i }}">{{ $i }} {{ $i === 0 ? '- No pain' : ($i === 10 ? '- Worst pain' : '') }}</flux:select.option>
                            @endfor
                        </flux:select>
                    </div>

                    <div>
                        <flux:select wire:model="consciousness_level" :label="__('Consciousness (AVPU)')">
                            <flux:select.option value="">{{ __('Not assessed') }}</flux:select.option>
                            <flux:select.option value="alert">{{ __('Alert') }}</flux:select.option>
                            <flux:select.option value="verbal">{{ __('Verbal') }}</flux:select.option>
                            <flux:select.option value="pain">{{ __('Pain') }}</flux:select.option>
                            <flux:select.option value="unresponsive">{{ __('Unresponsive') }}</flux:select.option>
                        </flux:select>
                    </div>
                </div>
            </flux:card>

            {{-- Notes --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Notes') }}</flux:heading>
                <flux:separator />
                <flux:textarea wire:model="notes" :label="__('Observations & Notes')" rows="3" placeholder="Any observations, concerns, or context..." />
            </flux:card>

            {{-- Actions --}}
            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" :href="route('vitals.index')" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="primary" type="submit">
                    {{ __('Record Vitals') }}
                </flux:button>
            </div>
        </form>
    </div>
</flux:main>
