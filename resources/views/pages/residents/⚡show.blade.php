<?php

use App\Models\Incident;
use App\Models\Medication;
use App\Models\Resident;
use App\Models\Vital;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Resident Details')]
class extends Component {
    #[Locked]
    public int $residentId;

    public function mount(Resident $resident): void
    {
        $this->residentId = $resident->id;
    }

    #[Computed]
    public function resident(): Resident
    {
        return Resident::with(['carePlans' => fn ($q) => $q->latest(), 'creator', 'updater'])->findOrFail($this->residentId);
    }

    #[Computed]
    public function activeMedications()
    {
        return Medication::where('resident_id', $this->residentId)->active()->latest()->get();
    }

    #[Computed]
    public function recentVitals()
    {
        return Vital::where('resident_id', $this->residentId)->with('recordedBy')->latest('recorded_at')->take(5)->get();
    }

    #[Computed]
    public function incidents()
    {
        return Incident::where('resident_id', $this->residentId)->with('reporter')->latest('occurred_at')->get();
    }
}; ?>

<flux:main>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <flux:button variant="ghost" :href="route('residents.index')" wire:navigate icon="arrow-left" />
                @if($this->resident->photo_path)
                    <img src="{{ Storage::url($this->resident->photo_path) }}" alt="" class="size-14 rounded-full object-cover" />
                @else
                    <flux:avatar size="lg" name="{{ $this->resident->full_name }}" />
                @endif
                <div>
                    <flux:heading size="xl">{{ $this->resident->full_name }}</flux:heading>
                    <div class="mt-1 flex flex-wrap items-center gap-2">
                        <flux:badge size="sm" :color="match($this->resident->status) {
                            'active' => 'green',
                            'discharged' => 'amber',
                            'deceased' => 'red',
                            'on_leave' => 'blue',
                            default => 'zinc',
                        }">
                            {{ str_replace('_', ' ', ucfirst($this->resident->status)) }}
                        </flux:badge>
                        <flux:text class="text-sm text-zinc-500">
                            {{ $this->resident->age }} {{ __('years old') }}
                            &middot; {{ ucfirst($this->resident->gender) }}
                            @if($this->resident->room_number)
                                &middot; {{ __('Room') }} {{ $this->resident->room_number }}
                                @if($this->resident->bed_number)
                                    / {{ __('Bed') }} {{ $this->resident->bed_number }}
                                @endif
                            @endif
                        </flux:text>
                    </div>
                </div>
            </div>

            @can('manage-residents')
                <flux:button variant="primary" :href="route('residents.edit', $this->resident)" wire:navigate icon="pencil">
                    {{ __('Edit') }}
                </flux:button>
            @endcan
        </div>

        {{-- Personal & Admission Info --}}
        <div class="grid gap-6 md:grid-cols-2">
            <flux:card class="space-y-3">
                <flux:heading size="sm">{{ __('Personal Information') }}</flux:heading>
                <flux:separator />
                <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                    <div>
                        <dt class="text-zinc-500">{{ __('Date of Birth') }}</dt>
                        <dd class="font-medium">{{ $this->resident->date_of_birth->format('M d, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">{{ __('Gender') }}</dt>
                        <dd class="font-medium">{{ ucfirst($this->resident->gender) }}</dd>
                    </div>
                    @if($this->resident->phone)
                        <div>
                            <dt class="text-zinc-500">{{ __('Phone') }}</dt>
                            <dd class="font-medium">{{ $this->resident->phone }}</dd>
                        </div>
                    @endif
                    @if($this->resident->email)
                        <div>
                            <dt class="text-zinc-500">{{ __('Email') }}</dt>
                            <dd class="font-medium">{{ $this->resident->email }}</dd>
                        </div>
                    @endif
                </dl>
            </flux:card>

            <flux:card class="space-y-3">
                <flux:heading size="sm">{{ __('Admission Details') }}</flux:heading>
                <flux:separator />
                <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                    <div>
                        <dt class="text-zinc-500">{{ __('Admitted') }}</dt>
                        <dd class="font-medium">{{ $this->resident->admission_date->format('M d, Y') }}</dd>
                    </div>
                    @if($this->resident->discharge_date)
                        <div>
                            <dt class="text-zinc-500">{{ __('Discharged') }}</dt>
                            <dd class="font-medium">{{ $this->resident->discharge_date->format('M d, Y') }}</dd>
                        </div>
                    @endif
                    @if($this->resident->room_number)
                        <div>
                            <dt class="text-zinc-500">{{ __('Room') }}</dt>
                            <dd class="font-medium">{{ $this->resident->room_number }}</dd>
                        </div>
                    @endif
                    @if($this->resident->bed_number)
                        <div>
                            <dt class="text-zinc-500">{{ __('Bed') }}</dt>
                            <dd class="font-medium">{{ $this->resident->bed_number }}</dd>
                        </div>
                    @endif
                </dl>
            </flux:card>
        </div>

        {{-- Medical Information --}}
        <flux:card class="space-y-3">
            <flux:heading size="sm">{{ __('Medical Information') }}</flux:heading>
            <flux:separator />
            <div class="grid gap-4 md:grid-cols-3">
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-zinc-500">{{ __('Blood Type') }}</dt>
                        <dd class="font-medium">{{ $this->resident->blood_type ?? __('Unknown') }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">{{ __('Mobility Status') }}</dt>
                        <dd>
                            <flux:badge size="sm" :color="match($this->resident->mobility_status) {
                                'independent' => 'green',
                                'assisted' => 'amber',
                                'wheelchair' => 'blue',
                                'bedridden' => 'red',
                                default => 'zinc',
                            }">{{ ucfirst($this->resident->mobility_status) }}</flux:badge>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">{{ __('Fall Risk') }}</dt>
                        <dd>
                            <flux:badge size="sm" :color="match($this->resident->fall_risk_level) {
                                'low' => 'green',
                                'medium' => 'amber',
                                'high' => 'red',
                                default => 'zinc',
                            }">{{ ucfirst($this->resident->fall_risk_level) }}</flux:badge>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">{{ __('DNR Status') }}</dt>
                        <dd>
                            <flux:badge size="sm" :color="$this->resident->dnr_status ? 'red' : 'green'">
                                {{ $this->resident->dnr_status ? __('Yes') : __('No') }}
                            </flux:badge>
                        </dd>
                    </div>
                </dl>
                <div class="space-y-3 text-sm">
                    @if($this->resident->allergies)
                        <div>
                            <dt class="text-zinc-500">{{ __('Allergies') }}</dt>
                            <dd class="mt-1 whitespace-pre-wrap">{{ $this->resident->allergies }}</dd>
                        </div>
                    @endif
                    @if($this->resident->medical_conditions)
                        <div>
                            <dt class="text-zinc-500">{{ __('Medical Conditions') }}</dt>
                            <dd class="mt-1 whitespace-pre-wrap">{{ $this->resident->medical_conditions }}</dd>
                        </div>
                    @endif
                </div>
                <div class="text-sm">
                    @if($this->resident->dietary_requirements)
                        <div>
                            <dt class="text-zinc-500">{{ __('Dietary Requirements') }}</dt>
                            <dd class="mt-1 whitespace-pre-wrap">{{ $this->resident->dietary_requirements }}</dd>
                        </div>
                    @endif
                </div>
            </div>
        </flux:card>

        {{-- Emergency Contact & Next of Kin --}}
        <div class="grid gap-6 md:grid-cols-2">
            <flux:card class="space-y-3">
                <flux:heading size="sm">{{ __('Emergency Contact') }}</flux:heading>
                <flux:separator />
                @if($this->resident->emergency_contact_name)
                    <dl class="space-y-2 text-sm">
                        <div>
                            <dt class="text-zinc-500">{{ __('Name') }}</dt>
                            <dd class="font-medium">{{ $this->resident->emergency_contact_name }}</dd>
                        </div>
                        @if($this->resident->emergency_contact_phone)
                            <div>
                                <dt class="text-zinc-500">{{ __('Phone') }}</dt>
                                <dd class="font-medium">{{ $this->resident->emergency_contact_phone }}</dd>
                            </div>
                        @endif
                        @if($this->resident->emergency_contact_relationship)
                            <div>
                                <dt class="text-zinc-500">{{ __('Relationship') }}</dt>
                                <dd class="font-medium">{{ $this->resident->emergency_contact_relationship }}</dd>
                            </div>
                        @endif
                    </dl>
                @else
                    <flux:text class="text-sm text-zinc-400">{{ __('No emergency contact provided') }}</flux:text>
                @endif
            </flux:card>

            <flux:card class="space-y-3">
                <flux:heading size="sm">{{ __('Next of Kin') }}</flux:heading>
                <flux:separator />
                @if($this->resident->nok_name)
                    <dl class="space-y-2 text-sm">
                        <div>
                            <dt class="text-zinc-500">{{ __('Name') }}</dt>
                            <dd class="font-medium">{{ $this->resident->nok_name }}</dd>
                        </div>
                        @if($this->resident->nok_phone)
                            <div>
                                <dt class="text-zinc-500">{{ __('Phone') }}</dt>
                                <dd class="font-medium">{{ $this->resident->nok_phone }}</dd>
                            </div>
                        @endif
                        @if($this->resident->nok_email)
                            <div>
                                <dt class="text-zinc-500">{{ __('Email') }}</dt>
                                <dd class="font-medium">{{ $this->resident->nok_email }}</dd>
                            </div>
                        @endif
                        @if($this->resident->nok_relationship)
                            <div>
                                <dt class="text-zinc-500">{{ __('Relationship') }}</dt>
                                <dd class="font-medium">{{ $this->resident->nok_relationship }}</dd>
                            </div>
                        @endif
                        @if($this->resident->nok_address)
                            <div>
                                <dt class="text-zinc-500">{{ __('Address') }}</dt>
                                <dd class="whitespace-pre-wrap">{{ $this->resident->nok_address }}</dd>
                            </div>
                        @endif
                    </dl>
                @else
                    <flux:text class="text-sm text-zinc-400">{{ __('No next of kin provided') }}</flux:text>
                @endif
            </flux:card>
        </div>

        {{-- Care Plans --}}
        <flux:card class="space-y-4">
            <div class="flex items-center justify-between">
                <flux:heading size="sm">{{ __('Care Plans') }}</flux:heading>
                @can('manage-care-plans')
                    <flux:button variant="primary" size="sm" :href="route('care-plans.create', $this->resident)" wire:navigate icon="plus">
                        {{ __('Add Care Plan') }}
                    </flux:button>
                @endcan
            </div>
            <flux:separator />

            @if($this->resident->carePlans->count() > 0)
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Title') }}</flux:table.column>
                        <flux:table.column>{{ __('Type') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                        <flux:table.column>{{ __('Start Date') }}</flux:table.column>
                        <flux:table.column>{{ __('Review Date') }}</flux:table.column>
                        <flux:table.column class="w-16"></flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($this->resident->carePlans as $plan)
                            <flux:table.row :key="$plan->id">
                                <flux:table.cell>
                                    <flux:link :href="route('care-plans.show', $plan)" wire:navigate class="font-medium">
                                        {{ $plan->title }}
                                    </flux:link>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" color="zinc">{{ $plan->type_label }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" :color="$plan->status_color">
                                        {{ str_replace('_', ' ', ucfirst($plan->status)) }}
                                    </flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>{{ $plan->start_date->format('M d, Y') }}</flux:table.cell>
                                <flux:table.cell>{{ $plan->review_date?->format('M d, Y') ?? '-' }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:button variant="ghost" size="sm" :href="route('care-plans.show', $plan)" wire:navigate icon="eye" />
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @else
                <x-dashboard.empty-state
                    title="No care plans"
                    description="No care plans have been created for this resident yet."
                    icon="clipboard-document-list"
                />
            @endif
        </flux:card>

        {{-- Active Medications --}}
        @can('manage-medications')
        <flux:card class="space-y-4">
            <div class="flex items-center justify-between">
                <flux:heading size="sm">{{ __('Active Medications') }}</flux:heading>
                <flux:button variant="primary" size="sm" :href="route('medications.create')" wire:navigate icon="plus">
                    {{ __('Add Medication') }}
                </flux:button>
            </div>
            <flux:separator />

            @if($this->activeMedications->count() > 0)
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Medication') }}</flux:table.column>
                        <flux:table.column>{{ __('Dosage') }}</flux:table.column>
                        <flux:table.column>{{ __('Frequency') }}</flux:table.column>
                        <flux:table.column>{{ __('Route') }}</flux:table.column>
                        <flux:table.column class="w-16"></flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($this->activeMedications as $medication)
                            <flux:table.row :key="$medication->id">
                                <flux:table.cell>
                                    <flux:link :href="route('medications.show', $medication)" wire:navigate class="font-medium">
                                        {{ $medication->name }}
                                    </flux:link>
                                </flux:table.cell>
                                <flux:table.cell>{{ $medication->dosage }}</flux:table.cell>
                                <flux:table.cell>{{ $medication->frequency }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" color="zinc">{{ $medication->route_label }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:button variant="ghost" size="sm" :href="route('medications.show', $medication)" wire:navigate icon="eye" />
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @else
                <x-dashboard.empty-state
                    title="No active medications"
                    description="No medications are currently prescribed for this resident."
                    icon="beaker"
                />
            @endif
        </flux:card>
        @endcan

        {{-- Recent Vitals --}}
        @can('manage-medications')
        <flux:card class="space-y-4">
            <div class="flex items-center justify-between">
                <flux:heading size="sm">{{ __('Recent Vitals') }}</flux:heading>
                <flux:button variant="primary" size="sm" :href="route('vitals.create')" wire:navigate icon="plus">
                    {{ __('Record Vitals') }}
                </flux:button>
            </div>
            <flux:separator />

            @if($this->recentVitals->count() > 0)
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Recorded At') }}</flux:table.column>
                        <flux:table.column>{{ __('BP') }}</flux:table.column>
                        <flux:table.column>{{ __('HR') }}</flux:table.column>
                        <flux:table.column>{{ __('Temp') }}</flux:table.column>
                        <flux:table.column>{{ __('SpO2') }}</flux:table.column>
                        <flux:table.column class="w-16"></flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($this->recentVitals as $vital)
                            <flux:table.row :key="$vital->id">
                                <flux:table.cell>{{ $vital->recorded_at->format('M d, H:i') }}</flux:table.cell>
                                <flux:table.cell>
                                    @if($vital->blood_pressure)
                                        <span @class(['text-red-500 font-medium' => $vital->blood_pressure_systolic > 140 || $vital->blood_pressure_systolic < 90])>
                                            {{ $vital->blood_pressure }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>{{ $vital->heart_rate ?? '-' }}</flux:table.cell>
                                <flux:table.cell>{{ $vital->temperature ? $vital->temperature . '°C' : '-' }}</flux:table.cell>
                                <flux:table.cell>{{ $vital->oxygen_saturation ? $vital->oxygen_saturation . '%' : '-' }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:button variant="ghost" size="sm" :href="route('vitals.show', $vital)" wire:navigate icon="eye" />
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @else
                <x-dashboard.empty-state
                    title="No vitals recorded"
                    description="No vital signs have been recorded for this resident yet."
                    icon="heart"
                />
            @endif
        </flux:card>
        @endcan

        {{-- Incidents --}}
        @can('manage-incidents')
        <flux:card class="space-y-4">
            <div class="flex items-center justify-between">
                <flux:heading size="sm">{{ __('Incidents') }}</flux:heading>
                @can('report-incidents')
                    <flux:button variant="primary" size="sm" :href="route('incidents.create')" wire:navigate icon="plus">
                        {{ __('Report Incident') }}
                    </flux:button>
                @endcan
            </div>
            <flux:separator />

            @if($this->incidents->count() > 0)
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Title') }}</flux:table.column>
                        <flux:table.column>{{ __('Type') }}</flux:table.column>
                        <flux:table.column>{{ __('Severity') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                        <flux:table.column>{{ __('Date') }}</flux:table.column>
                        <flux:table.column class="w-16"></flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($this->incidents as $incident)
                            <flux:table.row :key="$incident->id">
                                <flux:table.cell>
                                    <flux:link :href="route('incidents.show', $incident)" wire:navigate class="font-medium">
                                        {{ $incident->title }}
                                    </flux:link>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" color="zinc">{{ $incident->type_label }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" :color="$incident->severity_color">{{ ucfirst($incident->severity) }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" :color="$incident->status_color">
                                        {{ str_replace('_', ' ', ucfirst($incident->status)) }}
                                    </flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>{{ $incident->occurred_at->format('M d, Y') }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:button variant="ghost" size="sm" :href="route('incidents.show', $incident)" wire:navigate icon="eye" />
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @else
                <x-dashboard.empty-state
                    title="No incidents"
                    description="No incidents have been reported for this resident."
                    icon="exclamation-triangle"
                />
            @endif
        </flux:card>
        @endcan

        {{-- Notes --}}
        @if($this->resident->notes)
            <flux:card class="space-y-3">
                <flux:heading size="sm">{{ __('Notes') }}</flux:heading>
                <flux:separator />
                <flux:text class="whitespace-pre-wrap text-sm">{{ $this->resident->notes }}</flux:text>
            </flux:card>
        @endif

        {{-- Metadata --}}
        <flux:text class="text-xs text-zinc-400">
            {{ __('Created') }} {{ $this->resident->created_at->format('M d, Y H:i') }}
            @if($this->resident->creator)
                {{ __('by') }} {{ $this->resident->creator->name }}
            @endif
            @if($this->resident->updater)
                &middot; {{ __('Last updated by') }} {{ $this->resident->updater->name }}
                {{ $this->resident->updated_at->format('M d, Y H:i') }}
            @endif
        </flux:text>
    </div>
</flux:main>
