<?php

use App\Models\Resident;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Readmit Resident')]
class extends Component {
    #[Locked]
    public int $residentId;

    public string $admission_date = '';
    public string $reason = '';

    public function mount(Resident $resident): void
    {
        abort_unless(auth()->user()->can('manage-residents'), 403);
        abort_unless($resident->status === 'discharged', 403);
        $this->residentId   = $resident->id;
        $this->admission_date = now()->format('Y-m-d');
    }

    #[Computed]
    public function resident(): Resident
    {
        return Resident::findOrFail($this->residentId);
    }

    public function readmit(): void
    {
        $this->validate([
            'admission_date' => ['required', 'date'],
            'reason'         => ['nullable', 'string', 'max:1000'],
        ]);

        $resident = $this->resident;

        // Soft-delete the discharge record so history is preserved
        // and the resident can be discharged again cleanly in the future
        $resident->discharge?->delete();

        $resident->update([
            'status'         => 'active',
            'admission_date' => $this->admission_date,
            'discharge_date' => null,
            'updated_by'     => auth()->id(),
        ]);

        session()->flash('status', 'Resident readmitted successfully.');
        $this->redirect(route('residents.show', $resident), navigate: true);
    }
}; ?>

<flux:main>
    <div class="max-w-2xl space-y-6">

        {{-- Header --}}
        <div class="flex items-center gap-3">
            <flux:button variant="ghost" :href="route('residents.show', $this->residentId)" wire:navigate icon="arrow-left" />
            <div>
                <flux:heading size="xl">{{ __('Readmit Resident') }}</flux:heading>
                <flux:subheading>{{ $this->resident->full_name }}</flux:subheading>
            </div>
        </div>

        {{-- Resident info bar --}}
        <div class="rounded-xl border border-amber-100 bg-amber-50/60 px-5 py-3 dark:border-amber-900/40 dark:bg-amber-950/20">
            <div class="flex flex-wrap gap-x-8 gap-y-1 text-sm">
                <div class="flex items-center gap-1.5">
                    <flux:icon name="user" class="size-4 text-amber-500" />
                    <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $this->resident->full_name }}</span>
                </div>
                @if($this->resident->date_of_birth)
                    <div class="flex items-center gap-1.5">
                        <flux:icon name="cake" class="size-4 text-amber-400" />
                        <span class="text-zinc-600 dark:text-zinc-300">{{ $this->resident->date_of_birth->format('M d, Y') }} ({{ $this->resident->age }}y)</span>
                    </div>
                @endif
                @if($this->resident->discharge_date)
                    <div class="flex items-center gap-1.5">
                        <flux:icon name="arrow-right-start-on-rectangle" class="size-4 text-amber-400" />
                        <span class="text-zinc-600 dark:text-zinc-300">Discharged {{ $this->resident->discharge_date->format('M d, Y') }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Notice --}}
        <flux:card class="flex items-start gap-3 border-blue-100 bg-blue-50/60 dark:border-blue-900/40 dark:bg-blue-950/20">
            <flux:icon name="information-circle" class="mt-0.5 size-5 shrink-0 text-blue-500" />
            <div class="text-sm text-zinc-700 dark:text-zinc-300">
                Readmitting this resident will restore their status to <strong>Active</strong> and set a new admission date.
                The previous discharge record will be archived and a new discharge can be created when needed.
            </div>
        </flux:card>

        {{-- Form --}}
        <form wire:submit="readmit">
            <flux:card class="space-y-5">

                <flux:input
                    wire:model="admission_date"
                    type="date"
                    :label="__('New Admission Date')"
                    required
                />

                <flux:textarea
                    wire:model="reason"
                    :label="__('Reason for Readmission')"
                    :description="__('Optional — briefly note why the resident is being readmitted.')"
                    rows="3"
                />

                <div class="flex justify-end gap-3 pt-2">
                    <flux:button variant="ghost" :href="route('residents.show', $this->residentId)" wire:navigate>
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button variant="primary" type="submit" icon="arrow-left-end-on-rectangle">
                        {{ __('Confirm Readmission') }}
                    </flux:button>
                </div>

            </flux:card>
        </form>

    </div>
</flux:main>
