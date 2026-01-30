<?php

use App\Concerns\QualificationValidationRules;
use App\Concerns\StaffProfileValidationRules;
use App\Models\Qualification;
use App\Models\StaffProfile;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Edit Staff Profile')]
class extends Component {
    use QualificationValidationRules, StaffProfileValidationRules;

    #[Locked]
    public int $userId;

    // Staff profile fields
    public string $employee_id = '';
    public string $department = '';
    public string $position = '';
    public string $hire_date = '';
    public string $phone = '';
    public string $address = '';
    public string $employment_status = 'active';
    public string $emergency_contact_name = '';
    public string $emergency_contact_phone = '';
    public string $emergency_contact_relationship = '';
    public string $notes = '';

    // New qualification fields
    public string $qual_title = '';
    public string $qual_type = 'certification';
    public string $qual_issuing_body = '';
    public string $qual_issue_date = '';
    public string $qual_expiry_date = '';
    public string $qual_status = 'active';
    public string $qual_notes = '';

    public function mount(User $user): void
    {
        $this->userId = $user->id;
        $profile = $user->staffProfile;

        if ($profile) {
            $this->employee_id = $profile->employee_id ?? '';
            $this->department = $profile->department ?? '';
            $this->position = $profile->position ?? '';
            $this->hire_date = $profile->hire_date?->format('Y-m-d') ?? '';
            $this->phone = $profile->phone ?? '';
            $this->address = $profile->address ?? '';
            $this->employment_status = $profile->employment_status;
            $this->emergency_contact_name = $profile->emergency_contact_name ?? '';
            $this->emergency_contact_phone = $profile->emergency_contact_phone ?? '';
            $this->emergency_contact_relationship = $profile->emergency_contact_relationship ?? '';
            $this->notes = $profile->notes ?? '';
        }
    }

    #[Computed]
    public function member(): User
    {
        return User::with('roles')->findOrFail($this->userId);
    }

    #[Computed]
    public function qualifications()
    {
        return Qualification::where('user_id', $this->userId)->latest()->get();
    }

    public function save(): void
    {
        $validated = $this->validate($this->staffProfileRules());

        $data = array_merge($validated, [
            'user_id' => $this->userId,
        ]);

        // Convert empty strings to null
        foreach (['employee_id', 'department', 'position', 'hire_date', 'phone', 'address', 'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship', 'notes'] as $field) {
            if (empty($data[$field])) {
                $data[$field] = null;
            }
        }

        if (!StaffProfile::where('user_id', $this->userId)->exists()) {
            $data['created_by'] = auth()->id();
        }

        StaffProfile::updateOrCreate(
            ['user_id' => $this->userId],
            $data
        );

        session()->flash('status', 'Staff profile updated successfully.');
        $this->redirect(route('staff.show', $this->userId), navigate: true);
    }

    public function addQualification(): void
    {
        $validated = $this->validate([
            'qual_title' => $this->qualificationRules()['title'],
            'qual_type' => $this->qualificationRules()['type'],
            'qual_issuing_body' => $this->qualificationRules()['issuing_body'],
            'qual_issue_date' => $this->qualificationRules()['issue_date'],
            'qual_expiry_date' => ['nullable', 'date', 'after_or_equal:qual_issue_date'],
            'qual_status' => $this->qualificationRules()['status'],
            'qual_notes' => $this->qualificationRules()['notes'],
        ]);

        Qualification::create([
            'user_id' => $this->userId,
            'title' => $validated['qual_title'],
            'type' => $validated['qual_type'],
            'issuing_body' => $validated['qual_issuing_body'] ?: null,
            'issue_date' => $validated['qual_issue_date'] ?: null,
            'expiry_date' => $validated['qual_expiry_date'] ?: null,
            'status' => $validated['qual_status'],
            'notes' => $validated['qual_notes'] ?: null,
        ]);

        // Reset qualification form
        $this->qual_title = '';
        $this->qual_type = 'certification';
        $this->qual_issuing_body = '';
        $this->qual_issue_date = '';
        $this->qual_expiry_date = '';
        $this->qual_status = 'active';
        $this->qual_notes = '';

        unset($this->qualifications);
    }

    public function removeQualification(int $id): void
    {
        Qualification::where('id', $id)->where('user_id', $this->userId)->delete();
        unset($this->qualifications);
    }
}; ?>

<flux:main>
    <div class="max-w-3xl space-y-6">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" :href="route('staff.show', $this->userId)" wire:navigate icon="arrow-left" />
            <div>
                <flux:heading size="xl">{{ __('Edit Staff Profile') }}</flux:heading>
                <flux:subheading>{{ $this->member->name }}</flux:subheading>
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            {{-- Employment Details --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Employment Details') }}</flux:heading>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="employee_id" :label="__('Employee ID')" placeholder="e.g. EMP001" />
                    <flux:input wire:model="department" :label="__('Department')" placeholder="e.g. Nursing" />
                    <flux:input wire:model="position" :label="__('Position')" placeholder="e.g. Senior Nurse" />
                    <flux:input wire:model="hire_date" :label="__('Hire Date')" type="date" />
                    <flux:select wire:model="employment_status" :label="__('Employment Status')" required>
                        <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                        <flux:select.option value="on_leave">{{ __('On Leave') }}</flux:select.option>
                        <flux:select.option value="suspended">{{ __('Suspended') }}</flux:select.option>
                        <flux:select.option value="terminated">{{ __('Terminated') }}</flux:select.option>
                    </flux:select>
                </div>
            </flux:card>

            {{-- Contact Information --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Contact Information') }}</flux:heading>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="phone" :label="__('Phone')" placeholder="+254 700 000 000" />
                    <div class="sm:col-span-2">
                        <flux:textarea wire:model="address" :label="__('Address')" rows="2" />
                    </div>
                </div>
            </flux:card>

            {{-- Emergency Contact --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Emergency Contact') }}</flux:heading>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-3">
                    <flux:input wire:model="emergency_contact_name" :label="__('Name')" />
                    <flux:input wire:model="emergency_contact_phone" :label="__('Phone')" />
                    <flux:input wire:model="emergency_contact_relationship" :label="__('Relationship')" placeholder="e.g. Spouse, Parent" />
                </div>
            </flux:card>

            {{-- Notes --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Notes') }}</flux:heading>
                <flux:separator />
                <flux:textarea wire:model="notes" rows="3" placeholder="Any additional notes about this staff member..." />
            </flux:card>

            {{-- Save Button --}}
            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" :href="route('staff.show', $this->userId)" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="primary" type="submit">
                    {{ __('Save Profile') }}
                </flux:button>
            </div>
        </form>

        {{-- Qualifications Management --}}
        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('Qualifications & Certifications') }}</flux:heading>
            <flux:separator />

            {{-- Existing Qualifications --}}
            @if($this->qualifications->count() > 0)
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Title') }}</flux:table.column>
                        <flux:table.column>{{ __('Type') }}</flux:table.column>
                        <flux:table.column>{{ __('Expiry') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                        <flux:table.column class="w-16"></flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($this->qualifications as $qual)
                            <flux:table.row :key="$qual->id">
                                <flux:table.cell class="font-medium">{{ $qual->title }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" color="zinc">{{ $qual->type_label }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if($qual->expiry_date)
                                        <span @class([
                                            'text-red-600 dark:text-red-400 font-medium' => $qual->isExpired(),
                                            'text-amber-600 dark:text-amber-400 font-medium' => $qual->isExpiringSoon(),
                                        ])>
                                            {{ $qual->expiry_date->format('M d, Y') }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" :color="$qual->status_color">{{ $qual->status_label }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:button
                                        variant="ghost"
                                        size="sm"
                                        icon="trash"
                                        wire:click="removeQualification({{ $qual->id }})"
                                        wire:confirm="Are you sure you want to remove this qualification?"
                                    />
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @else
                <flux:text class="text-zinc-500">{{ __('No qualifications recorded yet.') }}</flux:text>
            @endif

            {{-- Add Qualification Form --}}
            <flux:separator text="Add Qualification" />
            <form wire:submit="addQualification" class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="qual_title" :label="__('Title')" placeholder="e.g. Registered Nurse License" required />
                    <flux:select wire:model="qual_type" :label="__('Type')" required>
                        <flux:select.option value="license">{{ __('License') }}</flux:select.option>
                        <flux:select.option value="certification">{{ __('Certification') }}</flux:select.option>
                        <flux:select.option value="training">{{ __('Training') }}</flux:select.option>
                        <flux:select.option value="education">{{ __('Education') }}</flux:select.option>
                    </flux:select>
                    <flux:input wire:model="qual_issuing_body" :label="__('Issuing Body')" placeholder="e.g. Nursing Council of Kenya" />
                    <flux:select wire:model="qual_status" :label="__('Status')" required>
                        <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                        <flux:select.option value="expired">{{ __('Expired') }}</flux:select.option>
                        <flux:select.option value="pending_renewal">{{ __('Pending Renewal') }}</flux:select.option>
                    </flux:select>
                    <flux:input wire:model="qual_issue_date" :label="__('Issue Date')" type="date" />
                    <flux:input wire:model="qual_expiry_date" :label="__('Expiry Date')" type="date" />
                    <div class="sm:col-span-2">
                        <flux:textarea wire:model="qual_notes" :label="__('Notes')" rows="2" />
                    </div>
                </div>
                <div class="flex justify-end">
                    <flux:button type="submit" icon="plus">
                        {{ __('Add Qualification') }}
                    </flux:button>
                </div>
            </form>
        </flux:card>
    </div>
</flux:main>
