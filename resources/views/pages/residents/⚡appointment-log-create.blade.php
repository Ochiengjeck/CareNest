<?php

use App\Concerns\AppointmentLogValidationRules;
use App\Models\AppointmentLog;
use App\Models\Resident;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('New Appointment Log')]
class extends Component {
    use AppointmentLogValidationRules;

    #[Locked]
    public int $residentId;

    public string $contact_number = '';
    public string $appointment_date = '';
    public string $time_slot = '';
    public string $address = '';
    public string $reason = '';

    public function mount(Resident $resident): void
    {
        abort_unless(auth()->user()->can('manage-residents'), 403);
        $this->residentId       = $resident->id;
        $this->appointment_date = now()->format('Y-m-d');
    }

    #[Computed]
    public function resident(): Resident { return Resident::findOrFail($this->residentId); }

    #[Computed]
    public function reasonLength(): int { return strlen($this->reason); }

    public function save(): void
    {
        $v = $this->validate($this->appointmentLogRules());
        AppointmentLog::create([
            'resident_id'      => $this->residentId,
            'contact_number'   => $v['contact_number'] ?? null,
            'appointment_date' => $v['appointment_date'],
            'time_slot'        => $v['time_slot'] ?? null,
            'address'          => $v['address'] ?? null,
            'reason'           => $v['reason'] ?? null,
            'recorded_by'      => auth()->id(),
        ]);
        session()->flash('status', 'Appointment logged successfully.');
        $this->redirect(route('residents.appointment-logs.index', $this->residentId), navigate: true);
    }
}; ?>

<flux:main>
    <div class="max-w-3xl space-y-1">

        <div class="mb-6 flex items-center gap-3">
            <flux:button variant="ghost" :href="route('residents.appointment-logs.index', $this->residentId)" wire:navigate icon="arrow-left" />
            <div>
                <flux:heading size="xl">{{ __('New Appointment Log') }}</flux:heading>
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

            {{-- Booking Details --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="calendar-days" class="size-5 text-accent" />
                    <flux:heading size="sm">{{ __('Booking Details') }}</flux:heading>
                </div>
                <flux:separator />

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="contact_number" :label="__('Contact Number')" placeholder="{{ __('Provider phone number...') }}" />
                    <flux:input type="date" wire:model="appointment_date" :label="__('Appointment Date')" required />
                </div>
                @error('appointment_date') <flux:text class="text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text> @enderror

                <flux:input wire:model="time_slot" :label="__('Time Slot')" placeholder="{{ __('e.g. 10:00 AM – 11:00 AM') }}" />

                <flux:textarea wire:model="address" :label="__('Address')" rows="2" placeholder="{{ __('Appointment location address...') }}" />

                <div>
                    <flux:textarea wire:model.live="reason" :label="__('Reason for Appointment')" rows="4" placeholder="{{ __('Describe the reason for this appointment...') }}" maxlength="500" />
                    <div class="mt-1 flex justify-end">
                        <span class="text-xs {{ $this->reasonLength > 480 ? 'text-red-500' : 'text-zinc-400' }}">{{ $this->reasonLength }} / 500</span>
                    </div>
                    @error('reason') <flux:text class="text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text> @enderror
                </div>
            </flux:card>

            <div class="flex justify-end gap-3 pb-4">
                <flux:button variant="ghost" :href="route('residents.appointment-logs.index', $this->residentId)" wire:navigate>{{ __('Cancel') }}</flux:button>
                <flux:button variant="primary" type="submit" icon="check">{{ __('Save Appointment') }}</flux:button>
            </div>

        </form>
    </div>
</flux:main>
