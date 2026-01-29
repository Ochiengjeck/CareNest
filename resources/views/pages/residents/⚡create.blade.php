<?php

use App\Concerns\ResidentValidationRules;
use App\Models\Resident;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new
#[Layout('layouts.app.sidebar')]
#[Title('Add Resident')]
class extends Component {
    use ResidentValidationRules, WithFileUploads;

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

        session()->flash('status', 'Resident added successfully.');
        $this->redirect(route('residents.show', $resident), navigate: true);
    }
}; ?>

<flux:main>
    <div class="max-w-3xl space-y-6">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" :href="route('residents.index')" wire:navigate icon="arrow-left" />
            <div>
                <flux:heading size="xl">{{ __('Add Resident') }}</flux:heading>
                <flux:subheading>{{ __('Register a new resident in the care home') }}</flux:subheading>
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            {{-- Personal Information --}}
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
                <div>
                    <flux:input wire:model="photo" :label="__('Photo')" type="file" accept="image/*" />
                    @if($photo)
                        <img src="{{ $photo->temporaryUrl() }}" alt="Preview" class="mt-2 size-20 rounded-full object-cover" />
                    @endif
                </div>
            </flux:card>

            {{-- Admission Details --}}
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

            {{-- Medical Information --}}
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

            {{-- Emergency Contact --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Emergency Contact') }}</flux:heading>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-3">
                    <flux:input wire:model="emergency_contact_name" :label="__('Name')" />
                    <flux:input wire:model="emergency_contact_phone" :label="__('Phone')" type="tel" />
                    <flux:input wire:model="emergency_contact_relationship" :label="__('Relationship')" />
                </div>
            </flux:card>

            {{-- Next of Kin --}}
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

            {{-- Notes --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Additional Notes') }}</flux:heading>
                <flux:separator />
                <flux:textarea wire:model="notes" rows="3" :placeholder="__('Any additional notes about this resident...')" />
            </flux:card>

            {{-- Actions --}}
            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" :href="route('residents.index')" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="primary" type="submit">
                    {{ __('Add Resident') }}
                </flux:button>
            </div>
        </form>
    </div>
</flux:main>
