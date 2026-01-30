<?php

use App\Concerns\ShiftValidationRules;
use App\Models\Shift;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Edit Shift')]
class extends Component {
    use ShiftValidationRules;

    #[Locked]
    public int $shiftId;

    #[Locked]
    public int $user_id;

    public string $shift_date = '';
    public string $start_time = '';
    public string $end_time = '';
    public string $type = 'morning';
    public string $status = 'scheduled';
    public string $notes = '';

    public function mount(Shift $shift): void
    {
        $this->shiftId = $shift->id;
        $this->user_id = $shift->user_id;
        $this->shift_date = $shift->shift_date->format('Y-m-d');
        $this->start_time = \Carbon\Carbon::parse($shift->start_time)->format('H:i');
        $this->end_time = \Carbon\Carbon::parse($shift->end_time)->format('H:i');
        $this->type = $shift->type;
        $this->status = $shift->status;
        $this->notes = $shift->notes ?? '';
    }

    #[Computed]
    public function shift(): Shift
    {
        return Shift::with('user')->findOrFail($this->shiftId);
    }

    public function save(): void
    {
        $rules = $this->shiftRules();
        // user_id is locked, skip validation for it
        unset($rules['user_id']);

        $validated = $this->validate($rules);

        $this->shift->update($validated);

        session()->flash('status', 'Shift updated successfully.');
        $this->redirect(route('shifts.show', $this->shiftId), navigate: true);
    }
}; ?>

<flux:main>
    <div class="max-w-3xl space-y-6">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" :href="route('shifts.show', $this->shiftId)" wire:navigate icon="arrow-left" />
            <div>
                <flux:heading size="xl">{{ __('Edit Shift') }}</flux:heading>
                <flux:subheading>{{ $this->shift->user->name }} - {{ $this->shift->shift_date->format('M d, Y') }}</flux:subheading>
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            {{-- Staff Member (read-only) --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Staff Member') }}</flux:heading>
                <flux:separator />
                <div class="flex items-center gap-3">
                    <flux:avatar :name="$this->shift->user->name" size="sm" />
                    <flux:text class="font-medium">{{ $this->shift->user->name }}</flux:text>
                </div>
            </flux:card>

            {{-- Shift Details --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Shift Details') }}</flux:heading>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="shift_date" :label="__('Date')" type="date" required />

                    <flux:select wire:model="type" :label="__('Type')" required>
                        <flux:select.option value="morning">{{ __('Morning') }}</flux:select.option>
                        <flux:select.option value="afternoon">{{ __('Afternoon') }}</flux:select.option>
                        <flux:select.option value="night">{{ __('Night') }}</flux:select.option>
                        <flux:select.option value="custom">{{ __('Custom') }}</flux:select.option>
                    </flux:select>

                    <flux:input wire:model="start_time" :label="__('Start Time')" type="time" required />
                    <flux:input wire:model="end_time" :label="__('End Time')" type="time" required />

                    <flux:select wire:model="status" :label="__('Status')" required>
                        <flux:select.option value="scheduled">{{ __('Scheduled') }}</flux:select.option>
                        <flux:select.option value="in_progress">{{ __('In Progress') }}</flux:select.option>
                        <flux:select.option value="completed">{{ __('Completed') }}</flux:select.option>
                        <flux:select.option value="cancelled">{{ __('Cancelled') }}</flux:select.option>
                        <flux:select.option value="no_show">{{ __('No Show') }}</flux:select.option>
                    </flux:select>
                </div>
            </flux:card>

            {{-- Notes --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Notes') }}</flux:heading>
                <flux:separator />
                <flux:textarea wire:model="notes" rows="3" placeholder="Any notes about this shift..." />
            </flux:card>

            {{-- Actions --}}
            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" :href="route('shifts.show', $this->shiftId)" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="primary" type="submit">
                    {{ __('Update Shift') }}
                </flux:button>
            </div>
        </form>
    </div>
</flux:main>
