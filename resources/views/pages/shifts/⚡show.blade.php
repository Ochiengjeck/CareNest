<?php

use App\Models\Shift;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Shift Details')]
class extends Component {
    #[Locked]
    public int $shiftId;

    public function mount(Shift $shift): void
    {
        $this->shiftId = $shift->id;
    }

    #[Computed]
    public function shift(): Shift
    {
        return Shift::with(['user.roles', 'creator'])->findOrFail($this->shiftId);
    }
}; ?>

<flux:main>
    <div class="max-w-4xl space-y-6">
        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <flux:button variant="ghost" :href="route('shifts.index')" wire:navigate icon="arrow-left" />
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <flux:heading size="xl">{{ $this->shift->shift_date->format('M d, Y') }}</flux:heading>
                        <flux:badge :color="$this->shift->type_color">{{ $this->shift->type_label }}</flux:badge>
                        <flux:badge :color="$this->shift->status_color">{{ $this->shift->status_label }}</flux:badge>
                    </div>
                    <flux:subheading>
                        {{ \Carbon\Carbon::parse($this->shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($this->shift->end_time)->format('H:i') }}
                    </flux:subheading>
                </div>
            </div>

            <flux:button :href="route('shifts.edit', $this->shift)" wire:navigate icon="pencil">
                {{ __('Edit') }}
            </flux:button>
        </div>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle" class="mb-4">
                {{ session('status') }}
            </flux:callout>
        @endif

        {{-- Staff Member --}}
        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('Staff Member') }}</flux:heading>
            <flux:separator />
            <div class="flex items-center gap-3">
                <flux:avatar :name="$this->shift->user->name" size="sm" />
                <div>
                    <flux:link :href="route('staff.show', $this->shift->user)" wire:navigate class="font-medium">
                        {{ $this->shift->user->name }}
                    </flux:link>
                    <div class="mt-1 flex flex-wrap gap-1">
                        @foreach($this->shift->user->roles as $role)
                            <flux:badge size="sm" color="zinc">
                                {{ str_replace('_', ' ', ucwords($role->name, '_')) }}
                            </flux:badge>
                        @endforeach
                    </div>
                </div>
            </div>
        </flux:card>

        {{-- Shift Details --}}
        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('Shift Details') }}</flux:heading>
            <flux:separator />
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:subheading size="sm">{{ __('Date') }}</flux:subheading>
                    <flux:text>{{ $this->shift->shift_date->format('l, M d, Y') }}</flux:text>
                </div>
                <div>
                    <flux:subheading size="sm">{{ __('Type') }}</flux:subheading>
                    <flux:badge :color="$this->shift->type_color">{{ $this->shift->type_label }}</flux:badge>
                </div>
                <div>
                    <flux:subheading size="sm">{{ __('Start Time') }}</flux:subheading>
                    <flux:text>{{ \Carbon\Carbon::parse($this->shift->start_time)->format('H:i') }}</flux:text>
                </div>
                <div>
                    <flux:subheading size="sm">{{ __('End Time') }}</flux:subheading>
                    <flux:text>{{ \Carbon\Carbon::parse($this->shift->end_time)->format('H:i') }}</flux:text>
                </div>
                <div>
                    <flux:subheading size="sm">{{ __('Status') }}</flux:subheading>
                    <flux:badge :color="$this->shift->status_color">{{ $this->shift->status_label }}</flux:badge>
                </div>
            </div>
        </flux:card>

        {{-- Notes --}}
        @if($this->shift->notes)
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Notes') }}</flux:heading>
                <flux:separator />
                <flux:text class="whitespace-pre-line">{{ $this->shift->notes }}</flux:text>
            </flux:card>
        @endif

        {{-- Metadata --}}
        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('Record Details') }}</flux:heading>
            <flux:separator />
            <div class="grid gap-4 sm:grid-cols-2">
                @if($this->shift->creator)
                    <div>
                        <flux:subheading size="sm">{{ __('Created By') }}</flux:subheading>
                        <flux:text>{{ $this->shift->creator->name }}</flux:text>
                    </div>
                @endif
                <div>
                    <flux:subheading size="sm">{{ __('Created On') }}</flux:subheading>
                    <flux:text>{{ $this->shift->created_at->format('M d, Y H:i') }}</flux:text>
                </div>
            </div>
        </flux:card>
    </div>
</flux:main>
