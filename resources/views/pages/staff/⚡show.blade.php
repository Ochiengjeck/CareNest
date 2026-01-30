<?php

use App\Models\Qualification;
use App\Models\Shift;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Staff Profile')]
class extends Component {
    #[Locked]
    public int $userId;

    public function mount(User $user): void
    {
        $this->userId = $user->id;
    }

    #[Computed]
    public function member(): User
    {
        return User::with(['roles', 'staffProfile', 'qualifications', 'shifts' => fn ($q) => $q->latest('shift_date')->limit(10)])->findOrFail($this->userId);
    }

    #[Computed]
    public function qualifications()
    {
        return Qualification::where('user_id', $this->userId)->latest()->get();
    }

    #[Computed]
    public function recentShifts()
    {
        return Shift::where('user_id', $this->userId)->latest('shift_date')->limit(10)->get();
    }
}; ?>

<flux:main>
    <div class="max-w-4xl space-y-6">
        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <flux:button variant="ghost" :href="route('staff.index')" wire:navigate icon="arrow-left" />
                <flux:avatar :name="$this->member->name" size="lg" />
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <flux:heading size="xl">{{ $this->member->name }}</flux:heading>
                        @if($this->member->staffProfile)
                            <flux:badge :color="$this->member->staffProfile->status_color">
                                {{ $this->member->staffProfile->status_label }}
                            </flux:badge>
                        @endif
                    </div>
                    <div class="mt-1 flex flex-wrap gap-1">
                        @foreach($this->member->roles as $role)
                            <flux:badge size="sm" color="zinc">
                                {{ str_replace('_', ' ', ucwords($role->name, '_')) }}
                            </flux:badge>
                        @endforeach
                    </div>
                </div>
            </div>

            @can('manage-staff')
                <flux:button :href="route('staff.edit', $this->member)" wire:navigate icon="pencil">
                    {{ __('Edit Profile') }}
                </flux:button>
            @endcan
        </div>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle" class="mb-4">
                {{ session('status') }}
            </flux:callout>
        @endif

        {{-- Staff Profile --}}
        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('Staff Profile') }}</flux:heading>
            <flux:separator />
            @if($this->member->staffProfile)
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <flux:subheading size="sm">{{ __('Employee ID') }}</flux:subheading>
                        <flux:text>{{ $this->member->staffProfile->employee_id ?? '-' }}</flux:text>
                    </div>
                    <div>
                        <flux:subheading size="sm">{{ __('Department') }}</flux:subheading>
                        <flux:text>{{ $this->member->staffProfile->department ?? '-' }}</flux:text>
                    </div>
                    <div>
                        <flux:subheading size="sm">{{ __('Position') }}</flux:subheading>
                        <flux:text>{{ $this->member->staffProfile->position ?? '-' }}</flux:text>
                    </div>
                    <div>
                        <flux:subheading size="sm">{{ __('Hire Date') }}</flux:subheading>
                        <flux:text>{{ $this->member->staffProfile->hire_date?->format('M d, Y') ?? '-' }}</flux:text>
                    </div>
                    <div>
                        <flux:subheading size="sm">{{ __('Phone') }}</flux:subheading>
                        <flux:text>{{ $this->member->staffProfile->phone ?? '-' }}</flux:text>
                    </div>
                    <div>
                        <flux:subheading size="sm">{{ __('Email') }}</flux:subheading>
                        <flux:text>{{ $this->member->email }}</flux:text>
                    </div>
                    @if($this->member->staffProfile->address)
                        <div class="sm:col-span-2">
                            <flux:subheading size="sm">{{ __('Address') }}</flux:subheading>
                            <flux:text class="whitespace-pre-line">{{ $this->member->staffProfile->address }}</flux:text>
                        </div>
                    @endif
                </div>
            @else
                <flux:text class="text-zinc-500">{{ __('No staff profile configured yet.') }}</flux:text>
            @endif
        </flux:card>

        {{-- Emergency Contact --}}
        @if($this->member->staffProfile && ($this->member->staffProfile->emergency_contact_name || $this->member->staffProfile->emergency_contact_phone))
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Emergency Contact') }}</flux:heading>
                <flux:separator />
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <flux:subheading size="sm">{{ __('Name') }}</flux:subheading>
                        <flux:text>{{ $this->member->staffProfile->emergency_contact_name ?? '-' }}</flux:text>
                    </div>
                    <div>
                        <flux:subheading size="sm">{{ __('Phone') }}</flux:subheading>
                        <flux:text>{{ $this->member->staffProfile->emergency_contact_phone ?? '-' }}</flux:text>
                    </div>
                    <div>
                        <flux:subheading size="sm">{{ __('Relationship') }}</flux:subheading>
                        <flux:text>{{ $this->member->staffProfile->emergency_contact_relationship ?? '-' }}</flux:text>
                    </div>
                </div>
            </flux:card>
        @endif

        {{-- Qualifications --}}
        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('Qualifications & Certifications') }}</flux:heading>
            <flux:separator />
            @if($this->qualifications->count() > 0)
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Title') }}</flux:table.column>
                        <flux:table.column>{{ __('Type') }}</flux:table.column>
                        <flux:table.column>{{ __('Issuing Body') }}</flux:table.column>
                        <flux:table.column>{{ __('Issue Date') }}</flux:table.column>
                        <flux:table.column>{{ __('Expiry Date') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($this->qualifications as $qual)
                            <flux:table.row :key="$qual->id">
                                <flux:table.cell class="font-medium">{{ $qual->title }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" color="zinc">{{ $qual->type_label }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>{{ $qual->issuing_body ?? '-' }}</flux:table.cell>
                                <flux:table.cell>{{ $qual->issue_date?->format('M d, Y') ?? '-' }}</flux:table.cell>
                                <flux:table.cell>
                                    @if($qual->expiry_date)
                                        <span @class([
                                            'text-red-600 dark:text-red-400 font-medium' => $qual->isExpired(),
                                            'text-amber-600 dark:text-amber-400 font-medium' => $qual->isExpiringSoon(),
                                        ])>
                                            {{ $qual->expiry_date->format('M d, Y') }}
                                        </span>
                                    @else
                                        <flux:text class="text-zinc-400">{{ __('No expiry') }}</flux:text>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" :color="$qual->status_color">{{ $qual->status_label }}</flux:badge>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @else
                <flux:text class="text-zinc-500">{{ __('No qualifications recorded.') }}</flux:text>
            @endif
        </flux:card>

        {{-- Recent Shifts --}}
        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('Recent Shifts') }}</flux:heading>
            <flux:separator />
            @if($this->recentShifts->count() > 0)
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Date') }}</flux:table.column>
                        <flux:table.column>{{ __('Start') }}</flux:table.column>
                        <flux:table.column>{{ __('End') }}</flux:table.column>
                        <flux:table.column>{{ __('Type') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($this->recentShifts as $shift)
                            <flux:table.row :key="$shift->id">
                                <flux:table.cell>{{ $shift->shift_date->format('M d, Y') }}</flux:table.cell>
                                <flux:table.cell>{{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }}</flux:table.cell>
                                <flux:table.cell>{{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" :color="$shift->type_color">{{ $shift->type_label }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" :color="$shift->status_color">{{ $shift->status_label }}</flux:badge>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @else
                <flux:text class="text-zinc-500">{{ __('No shifts recorded.') }}</flux:text>
            @endif
        </flux:card>

        {{-- Notes --}}
        @if($this->member->staffProfile?->notes)
            <flux:card class="space-y-4">
                <flux:heading size="sm">{{ __('Notes') }}</flux:heading>
                <flux:separator />
                <flux:text class="whitespace-pre-line">{{ $this->member->staffProfile->notes }}</flux:text>
            </flux:card>
        @endif

        {{-- Metadata --}}
        <flux:card class="space-y-4">
            <flux:heading size="sm">{{ __('Account Details') }}</flux:heading>
            <flux:separator />
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:subheading size="sm">{{ __('Account Created') }}</flux:subheading>
                    <flux:text>{{ $this->member->created_at->format('M d, Y') }}</flux:text>
                </div>
                @if($this->member->staffProfile?->created_by)
                    <div>
                        <flux:subheading size="sm">{{ __('Profile Created By') }}</flux:subheading>
                        <flux:text>{{ $this->member->staffProfile->creator?->name ?? '-' }}</flux:text>
                    </div>
                @endif
            </div>
        </flux:card>
    </div>
</flux:main>
