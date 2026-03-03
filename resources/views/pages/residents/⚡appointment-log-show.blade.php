<?php

use App\Models\AppointmentLog;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Appointment Log')]
class extends Component {
    #[Locked]
    public int $recordId;

    public function mount(AppointmentLog $appointmentLog): void
    {
        abort_unless(auth()->user()->can('view-residents'), 403);
        $this->recordId = $appointmentLog->id;
    }

    #[Computed]
    public function record(): AppointmentLog
    {
        return AppointmentLog::with(['resident', 'recorder'])->findOrFail($this->recordId);
    }
}; ?>

<flux:main>
    @php $record = $this->record; @endphp
    <div class="max-w-3xl space-y-5">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                <flux:button variant="ghost" :href="route('residents.appointment-logs.index', $record->resident_id)" wire:navigate icon="arrow-left" />
                <div>
                    <flux:heading size="xl">{{ __('Appointment Log') }}</flux:heading>
                    <flux:subheading>{{ $record->resident->full_name }}</flux:subheading>
                </div>
            </div>
            <a href="{{ route('appointment-logs.export.pdf', $record->id) }}" target="_blank">
                <flux:button variant="outline" icon="arrow-down-tray">{{ __('Download PDF') }}</flux:button>
            </a>
        </div>

        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-zinc-200 bg-zinc-50 px-5 py-3 dark:border-zinc-700 dark:bg-zinc-800/40">
            <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $record->created_at->format('M d, Y') }}</span>
            <span class="ml-auto flex items-center gap-1.5 text-sm text-zinc-500">
                <flux:icon name="user" class="size-4" />{{ $record->recorder?->name ?? '—' }}
                <span class="text-zinc-400">&bull;</span>{{ $record->created_at->diffForHumans() }}
            </span>
        </div>

        <div class="rounded-lg border border-blue-100 bg-blue-50/60 px-5 py-2.5 dark:border-blue-900/40 dark:bg-blue-950/20">
            <div class="flex flex-wrap gap-x-8 gap-y-1 text-sm">
                <div><span class="text-zinc-400">AHCCCS ID:</span> <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $record->resident->ahcccs_id ?? '—' }}</span></div>
                <div><span class="text-zinc-400">DOB:</span> <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $record->resident->date_of_birth->format('M d, Y') }}</span></div>
            </div>
        </div>

        <flux:card class="space-y-4">
            <div class="flex items-center gap-2"><flux:icon name="calendar-days" class="size-5 text-zinc-400" /><flux:heading size="sm">{{ __('Booking Details') }}</flux:heading></div>
            <flux:separator />
            <div class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
                <div>
                    <div class="text-xs text-zinc-400">{{ __('Appointment Date') }}</div>
                    <div class="font-medium text-zinc-700 dark:text-zinc-300">{{ $record->appointment_date->format('M d, Y') }}</div>
                    @if ($record->appointment_date->isFuture())
                        <flux:badge size="sm" color="blue">Upcoming</flux:badge>
                    @elseif ($record->appointment_date->isToday())
                        <flux:badge size="sm" color="green">Today</flux:badge>
                    @endif
                </div>
                <div><div class="text-xs text-zinc-400">{{ __('Time') }}</div><div class="font-medium text-zinc-700 dark:text-zinc-300">{{ $record->time_slot ?? '—' }}</div></div>
                <div><div class="text-xs text-zinc-400">{{ __('Contact #') }}</div><div class="font-medium text-zinc-700 dark:text-zinc-300">{{ $record->contact_number ?? '—' }}</div></div>
            </div>
            @if ($record->address)
                <div>
                    <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ __('Address') }}</div>
                    <flux:text class="text-sm text-zinc-700 dark:text-zinc-300">{{ $record->address }}</flux:text>
                </div>
            @endif
            @if ($record->reason)
                <div>
                    <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ __('Reason') }}</div>
                    <flux:text class="text-sm text-zinc-700 dark:text-zinc-300" style="white-space: pre-wrap;">{{ $record->reason }}</flux:text>
                </div>
            @endif
        </flux:card>

    </div>
</flux:main>
