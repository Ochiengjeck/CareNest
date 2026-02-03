<?php

use App\Concerns\ResidentValidationRules;
use App\Models\Resident;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new
#[Layout('layouts.app.sidebar')]
#[Title('Admit Resident')]
class extends Component {
    use ResidentValidationRules, WithFileUploads;

    public int $currentStep = 1;
    public int $totalSteps = 3;

    // Personal
    public string $first_name = '';
    public string $last_name = '';
    public string $date_of_birth = '';
    public string $gender = 'male';
    public $photo = null;
    public string $phone = '';
    public string $email = '';

    // Admission
    public string $admission_date = '';
    public string $room_number = '';
    public string $bed_number = '';
    public string $status = 'active';

    // Medical
    public string $blood_type = '';
    public string $allergies = '';
    public string $medical_conditions = '';
    public string $mobility_status = 'independent';
    public string $dietary_requirements = '';
    public string $fall_risk_level = 'low';
    public bool $dnr_status = false;

    // Emergency Contact
    public string $emergency_contact_name = '';
    public string $emergency_contact_phone = '';
    public string $emergency_contact_relationship = '';

    // Next of Kin
    public string $nok_name = '';
    public string $nok_phone = '';
    public string $nok_email = '';
    public string $nok_relationship = '';
    public string $nok_address = '';

    // Notes
    public string $notes = '';

    public function mount(): void
    {
        $this->admission_date = now()->format('Y-m-d');
    }

    public function nextStep(): void
    {
        $rules = match ($this->currentStep) {
            1 => [...$this->personalInfoRules()],
            2 => [...$this->admissionRules(), ...$this->medicalRules()],
            3 => [...$this->emergencyContactRules(), ...$this->nextOfKinRules(), ...$this->notesRules()],
            default => [],
        };

        $this->validate($rules);
        $this->currentStep = min($this->currentStep + 1, $this->totalSteps);
    }

    public function previousStep(): void
    {
        $this->currentStep = max($this->currentStep - 1, 1);
    }

    public function goToStep(int $step): void
    {
        if ($step < $this->currentStep) {
            $this->currentStep = $step;
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            ...$this->personalInfoRules(),
            ...$this->admissionRules(),
            ...$this->medicalRules(),
            ...$this->emergencyContactRules(),
            ...$this->nextOfKinRules(),
            ...$this->notesRules(),
        ]);

        $data = collect($validated)->except('photo')->toArray();
        $data['created_by'] = auth()->id();

        if ($this->photo) {
            $data['photo_path'] = $this->photo->store('residents/photos', 'public');
        }

        $resident = Resident::create($data);

        session()->flash('status', 'Resident admitted successfully.');
        $this->redirect(route('residents.show', $resident), navigate: true);
    }
}; ?>

<flux:main>
    <div class="max-w-3xl space-y-6">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" :href="route('residents.index')" wire:navigate icon="arrow-left" />
            <div>
                <flux:heading size="xl">{{ __('Admit Resident') }}</flux:heading>
                <flux:subheading>{{ __('Admit a new resident to the care home') }}</flux:subheading>
            </div>
        </div>

        {{-- Step Indicator --}}
        <div class="flex items-center">
            @foreach([1 => 'Personal Info', 2 => 'Admission & Medical', 3 => 'Contacts & Notes'] as $step => $label)
                <div class="flex items-center {{ $step < $totalSteps ? 'flex-1' : '' }}">
                    <button
                        type="button"
                        wire:click="goToStep({{ $step }})"
                        @class([
                            'flex items-center justify-center size-9 rounded-full text-sm font-medium shrink-0 transition-colors',
                            'bg-zinc-800 text-white dark:bg-white dark:text-zinc-900' => $currentStep === $step,
                            'bg-green-500 text-white' => $currentStep > $step,
                            'bg-zinc-200 text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400' => $currentStep < $step,
                            'cursor-pointer hover:opacity-80' => $step < $currentStep,
                            'cursor-default' => $step >= $currentStep,
                        ])
                        @if($step > $currentStep) disabled @endif
                    >
                        @if($currentStep > $step)
                            <flux:icon name="check" variant="mini" class="size-4" />
                        @else
                            {{ $step }}
                        @endif
                    </button>
                    <span @class([
                        'ml-2 text-sm font-medium hidden sm:block',
                        'text-zinc-900 dark:text-zinc-100' => $currentStep >= $step,
                        'text-zinc-400 dark:text-zinc-500' => $currentStep < $step,
                    ])>{{ __($label) }}</span>
                    @if($step < $totalSteps)
                        <div @class([
                            'flex-1 h-px mx-4',
                            'bg-green-500' => $currentStep > $step,
                            'bg-zinc-200 dark:bg-zinc-700' => $currentStep <= $step,
                        ])></div>
                    @endif
                </div>
            @endforeach
        </div>

        <form wire:submit="save" class="space-y-6">
            {{-- Step 1: Personal Information --}}
            @if($currentStep === 1)
                <flux:card class="space-y-4">
                    <flux:heading size="sm">{{ __('Personal Information') }}</flux:heading>
                    <flux:separator />
                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:input wire:model="first_name" :label="__('First Name')" required />
                        <flux:input wire:model="last_name" :label="__('Last Name')" required />
                        <flux:input wire:model="date_of_birth" :label="__('Date of Birth')" type="date" required />
                        <flux:select wire:model="gender" :label="__('Gender')" required>
                            <flux:select.option value="male">{{ __('Male') }}</flux:select.option>
                            <flux:select.option value="female">{{ __('Female') }}</flux:select.option>
                            <flux:select.option value="other">{{ __('Other') }}</flux:select.option>
                        </flux:select>
                        <flux:input wire:model="phone" :label="__('Phone')" type="tel" />
                        <flux:input wire:model="email" :label="__('Email')" type="email" />
                    </div>

                    {{-- Photo Upload --}}
                    <div>
                        <flux:label>{{ __('Photo') }}</flux:label>
                        <div class="mt-2 flex items-center gap-6">
                            <div class="shrink-0">
                                @if($photo)
                                    <img src="{{ $photo->temporaryUrl() }}" alt="Preview" class="size-20 rounded-full object-cover" />
                                @else
                                    <div class="flex size-20 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                        <flux:icon name="camera" class="size-8 text-zinc-400" />
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <label class="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-zinc-300 dark:border-zinc-600 p-4 hover:border-zinc-400 dark:hover:border-zinc-500 transition-colors">
                                    <flux:icon name="arrow-up-tray" class="size-6 text-zinc-400 mb-1" />
                                    <span class="text-sm text-zinc-500">{{ __('Click to upload') }}</span>
                                    <span class="text-xs text-zinc-400">{{ __('JPG, PNG up to 1MB') }}</span>
                                    <input wire:model="photo" type="file" accept="image/*" class="hidden" />
                                </label>
                            </div>
                        </div>
                        @error('photo') <div class="mt-1 text-sm text-red-500">{{ $message }}</div> @enderror
                    </div>
                </flux:card>
            @endif

            {{-- Step 2: Admission & Medical --}}
            @if($currentStep === 2)
                <flux:card class="space-y-4">
                    <flux:heading size="sm">{{ __('Admission Details') }}</flux:heading>
                    <flux:separator />
                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:input wire:model="admission_date" :label="__('Admission Date')" type="date" required />
                        <flux:select wire:model="status" :label="__('Status')" required>
                            <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                            <flux:select.option value="discharged">{{ __('Discharged') }}</flux:select.option>
                            <flux:select.option value="deceased">{{ __('Deceased') }}</flux:select.option>
                            <flux:select.option value="on_leave">{{ __('On Leave') }}</flux:select.option>
                        </flux:select>
                        <flux:input wire:model="room_number" :label="__('Room Number')" />
                        <flux:input wire:model="bed_number" :label="__('Bed Number')" />
                    </div>
                </flux:card>

                <flux:card class="space-y-4">
                    <flux:heading size="sm">{{ __('Medical Information') }}</flux:heading>
                    <flux:separator />
                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:select wire:model="blood_type" :label="__('Blood Type')">
                            <flux:select.option value="">{{ __('Unknown') }}</flux:select.option>
                            @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bt)
                                <flux:select.option value="{{ $bt }}">{{ $bt }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:select wire:model="mobility_status" :label="__('Mobility Status')" required>
                            <flux:select.option value="independent">{{ __('Independent') }}</flux:select.option>
                            <flux:select.option value="assisted">{{ __('Assisted') }}</flux:select.option>
                            <flux:select.option value="wheelchair">{{ __('Wheelchair') }}</flux:select.option>
                            <flux:select.option value="bedridden">{{ __('Bedridden') }}</flux:select.option>
                        </flux:select>
                        <flux:select wire:model="fall_risk_level" :label="__('Fall Risk Level')" required>
                            <flux:select.option value="low">{{ __('Low') }}</flux:select.option>
                            <flux:select.option value="medium">{{ __('Medium') }}</flux:select.option>
                            <flux:select.option value="high">{{ __('High') }}</flux:select.option>
                        </flux:select>
                        <div class="flex items-end pb-1">
                            <flux:checkbox wire:model="dnr_status" :label="__('Do Not Resuscitate (DNR)')" />
                        </div>
                    </div>
                    <flux:textarea wire:model="allergies" :label="__('Allergies')" rows="2" :placeholder="__('List known allergies...')" />
                    <flux:textarea wire:model="medical_conditions" :label="__('Medical Conditions')" rows="2" :placeholder="__('List medical conditions...')" />
                    <flux:textarea wire:model="dietary_requirements" :label="__('Dietary Requirements')" rows="2" :placeholder="__('Special dietary needs...')" />
                </flux:card>
            @endif

            {{-- Step 3: Contacts & Notes --}}
            @if($currentStep === 3)
                <flux:card class="space-y-4">
                    <flux:heading size="sm">{{ __('Emergency Contact') }}</flux:heading>
                    <flux:separator />
                    <div class="grid gap-4 sm:grid-cols-3">
                        <flux:input wire:model="emergency_contact_name" :label="__('Name')" />
                        <flux:input wire:model="emergency_contact_phone" :label="__('Phone')" type="tel" />
                        <flux:input wire:model="emergency_contact_relationship" :label="__('Relationship')" />
                    </div>
                </flux:card>

                <flux:card class="space-y-4">
                    <flux:heading size="sm">{{ __('Next of Kin') }}</flux:heading>
                    <flux:separator />
                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:input wire:model="nok_name" :label="__('Name')" />
                        <flux:input wire:model="nok_phone" :label="__('Phone')" type="tel" />
                        <flux:input wire:model="nok_email" :label="__('Email')" type="email" />
                        <flux:input wire:model="nok_relationship" :label="__('Relationship')" />
                    </div>
                    <flux:textarea wire:model="nok_address" :label="__('Address')" rows="2" />
                </flux:card>

                <flux:card class="space-y-4">
                    <flux:heading size="sm">{{ __('Additional Notes') }}</flux:heading>
                    <flux:separator />
                    <flux:textarea wire:model="notes" rows="3" :placeholder="__('Any additional notes about this resident...')" />
                </flux:card>
            @endif

            {{-- Navigation Buttons --}}
            <div class="flex justify-between">
                <div>
                    @if($currentStep > 1)
                        <flux:button variant="ghost" wire:click="previousStep" type="button" icon="arrow-left">
                            {{ __('Previous') }}
                        </flux:button>
                    @else
                        <flux:button variant="ghost" :href="route('residents.index')" wire:navigate>
                            {{ __('Cancel') }}
                        </flux:button>
                    @endif
                </div>
                <div>
                    @if($currentStep < $totalSteps)
                        <flux:button variant="primary" wire:click="nextStep" type="button" icon-trailing="arrow-right">
                            {{ __('Next') }}
                        </flux:button>
                    @else
                        <flux:button variant="primary" type="submit" icon="check">
                            {{ __('Admit Resident') }}
                        </flux:button>
                    @endif
                </div>
            </div>
        </form>
    </div>
</flux:main>
