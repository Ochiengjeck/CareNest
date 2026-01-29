<?php

use App\Models\Medication;
use App\Models\MedicationLog;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Medication Details')]
class extends Component {
    #[Locked]
    public int $medicationId;

    public function mount(Medication $medication): void
    {
        $this->medicationId = $medication->id;
    }

    #[Computed]
    public function medication(): Medication
    {
        return Medication::with(['resident', 'creator'])->findOrFail($this->medicationId);
    }

    #[Computed]
    public function administrationLogs()
    {
        return MedicationLog::where('medication_id', $this->medicationId)
            ->with('administeredBy')
            ->latest('administered_at')
            ->get();
    }
}; ?>

<flux:main>
    <div class="max-w-4xl space-y-6">
        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <flux:button variant="ghost" :href="route('medications.index')" wire:navigate icon="arrow-left" />
                <div>
                    <div class="flex items-center gap-2">
                        <flux:heading size="xl">{{ $this->medication->name }}</flux:heading>
                        <flux:badge :color="$this->medication->status_color">
                            {{ str_replace('_', ' ', ucfirst($this->medication->status)) }}
                        </flux:badge>
                    </div>
                    <flux:subheading>
                        {{ __('For') }}
                        <flux:link :href="route('residents.show', $this->medication->resident)" wire:navigate>
                            {{ $this->medication->resident->full_name }}
                        </flux:link>
                    </flux:subheading>
                </div>
            </div>

            <div class="flex gap-2">
                @can('administer-medications')
                    @if($this->medication->status === 'active')
                        <flux:button variant="primary" :href="route('medications.administer', $this->medication)" wire:navigate icon="clipboard-document-check">
                            {{ __('Administer') }}
                        </flux:button>
                    @endif
                @endcan
                @can('manage-medications')
                    <flux:button :href="route('medications.edit', $this->medication)" wire:navigate icon="pencil">
                        {{ __('Edit') }}
                    </flux:button>
                @endcan
            </div>
        </div>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle" class="mb-4">
                {{ session('status') }}
            </flux:callout>
        @endif

        {{-- Prescription Details --}}
        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('Prescription Details') }}</flux:heading>
            <flux:separator />

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <flux:subheading size="sm">{{ __('Dosage') }}</flux:subheading>
                    <flux:text>{{ $this->medication->dosage }}</flux:text>
                </div>
                <div>
                    <flux:subheading size="sm">{{ __('Frequency') }}</flux:subheading>
                    <flux:text>{{ $this->medication->frequency }}</flux:text>
                </div>
                <div>
                    <flux:subheading size="sm">{{ __('Route') }}</flux:subheading>
                    <flux:badge size="sm" color="zinc">{{ $this->medication->route_label }}</flux:badge>
                </div>
                <div>
                    <flux:subheading size="sm">{{ __('Prescribed By') }}</flux:subheading>
                    <flux:text>{{ $this->medication->prescribed_by }}</flux:text>
                </div>
                <div>
                    <flux:subheading size="sm">{{ __('Prescribed Date') }}</flux:subheading>
                    <flux:text>{{ $this->medication->prescribed_date->format('M d, Y') }}</flux:text>
                </div>
                <div>
                    <flux:subheading size="sm">{{ __('Start Date') }}</flux:subheading>
                    <flux:text>{{ $this->medication->start_date->format('M d, Y') }}</flux:text>
                </div>
                @if($this->medication->end_date)
                    <div>
                        <flux:subheading size="sm">{{ __('End Date') }}</flux:subheading>
                        <flux:text>{{ $this->medication->end_date->format('M d, Y') }}</flux:text>
                    </div>
                @endif
            </div>
        </flux:card>

        {{-- Instructions --}}
        @if($this->medication->instructions)
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Instructions') }}</flux:heading>
                <flux:separator />
                <flux:text class="whitespace-pre-line">{{ $this->medication->instructions }}</flux:text>
            </flux:card>
        @endif

        {{-- Notes --}}
        @if($this->medication->notes)
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Notes') }}</flux:heading>
                <flux:separator />
                <flux:text class="whitespace-pre-line">{{ $this->medication->notes }}</flux:text>
            </flux:card>
        @endif

        {{-- Administration History --}}
        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('Administration History') }}</flux:heading>
            <flux:separator />

            @if($this->administrationLogs->count() > 0)
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Date & Time') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                        <flux:table.column>{{ __('Administered By') }}</flux:table.column>
                        <flux:table.column>{{ __('Notes') }}</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach($this->administrationLogs as $log)
                            <flux:table.row :key="$log->id">
                                <flux:table.cell>{{ $log->administered_at->format('M d, Y H:i') }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" :color="$log->status_color">
                                        {{ ucfirst($log->status) }}
                                    </flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>{{ $log->administeredBy?->name ?? '-' }}</flux:table.cell>
                                <flux:table.cell>{{ $log->notes ?? '-' }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @else
                <x-dashboard.empty-state
                    title="No administration records"
                    description="Medication administration history will appear here."
                    icon="clipboard-document-check"
                />
            @endif
        </flux:card>

        {{-- Metadata --}}
        <flux:text size="sm" class="text-zinc-400">
            {{ __('Added by') }} {{ $this->medication->creator?->name ?? __('System') }}
            {{ __('on') }} {{ $this->medication->created_at->format('M d, Y H:i') }}
        </flux:text>
    </div>
</flux:main>
