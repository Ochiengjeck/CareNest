<?php

use App\Concerns\ShiftValidationRules;
use App\Models\Shift;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Create Shift')]
class extends Component {
    use ShiftValidationRules;

    public string $user_id = '';
    public string $shift_date = '';
    public string $start_time = '';
    public string $end_time = '';
    public string $type = 'morning';
    public string $status = 'scheduled';
    public string $notes = '';

    public function mount(): void
    {
        $this->shift_date = today()->format('Y-m-d');
    }

    #[Computed]
    public function staffMembers(): array
    {
        return User::query()
            ->whereHas('roles')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn ($u) => [$u->id => $u->name])
            ->toArray();
    }

    public function save(): void
    {
        $validated = $this->validate($this->shiftRules());
        $validated['created_by'] = auth()->id();

        $shift = Shift::create($validated);

        session()->flash('status', 'Shift created successfully.');
        $this->redirect(route('shifts.show', $shift), navigate: true);
    }
}; ?>

<flux:main>
    <div class="max-w-3xl space-y-6">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" :href="route('shifts.index')" wire:navigate icon="arrow-left" />
            <div>
                <flux:heading size="xl">{{ __('Create Shift') }}</flux:heading>
                <flux:subheading>{{ __('Schedule a new shift assignment') }}</flux:subheading>
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            {{-- Shift Details --}}
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Shift Details') }}</flux:heading>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <flux:select wire:model="user_id" :label="__('Staff Member')" required>
                            <flux:select.option value="">{{ __('Select staff member...') }}</flux:select.option>
                            @foreach($this->staffMembers as $id => $name)
                                <flux:select.option value="{{ $id }}">{{ $name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

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
                <flux:button variant="ghost" :href="route('shifts.index')" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="primary" type="submit">
                    {{ __('Create Shift') }}
                </flux:button>
            </div>
        </form>
    </div>
</flux:main>
