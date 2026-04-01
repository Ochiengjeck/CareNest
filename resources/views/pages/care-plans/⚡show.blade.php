<?php

use App\Models\CarePlan;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Care Plan Details')]
class extends Component {
    #[Locked]
    public int $carePlanId;

    public function mount(CarePlan $carePlan): void
    {
        $this->carePlanId = $carePlan->id;
    }

    #[Computed]
    public function carePlan(): CarePlan
    {
        return CarePlan::with(['resident', 'creator', 'reviewer', 'updater', 'carePlanGoals'])->findOrFail($this->carePlanId);
    }
}; ?>

<flux:main>
    <div class="max-w-3xl space-y-6">
        {{-- Header --}}
        <flux:card class="p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex items-start gap-4">
                    <flux:button variant="ghost" :href="route('care-plans.index')" wire:navigate icon="arrow-left" />
                    <div>
                        <flux:heading size="xl">{{ $this->carePlan->title }}</flux:heading>
                        <div class="mt-1.5 flex flex-wrap items-center gap-2">
                            <flux:badge size="sm" color="zinc">{{ $this->carePlan->type_label }}</flux:badge>
                            <flux:badge size="sm" :color="$this->carePlan->status_color">
                                {{ str_replace('_', ' ', ucfirst($this->carePlan->status)) }}
                            </flux:badge>
                        </div>

                        {{-- Resident Info --}}
                        @if($this->carePlan->resident)
                            <div class="mt-3 flex items-center gap-3">
                                @if($this->carePlan->resident->photo_path)
                                    <img src="{{ Storage::url($this->carePlan->resident->photo_path) }}" alt="" class="size-10 rounded-full object-cover ring-2 ring-zinc-200 dark:ring-zinc-700" />
                                @else
                                    <flux:avatar size="sm" name="{{ $this->carePlan->resident->full_name }}" />
                                @endif
                                <div>
                                    <flux:link :href="route('residents.show', $this->carePlan->resident)" wire:navigate class="font-medium text-sm">
                                        {{ $this->carePlan->resident->full_name }}
                                    </flux:link>
                                    <flux:text class="text-xs text-zinc-500">
                                        {{ $this->carePlan->resident->age }} {{ __('years old') }}
                                        @if($this->carePlan->resident->room_number)
                                            &middot; {{ __('Room') }} {{ $this->carePlan->resident->room_number }}
                                        @endif
                                    </flux:text>
                                </div>
                            </div>

                            {{-- Behavioral Health Header Metadata --}}
                            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-zinc-500">
                                @if($this->carePlan->resident->ahcccs_id)
                                    <span><span class="font-medium">AHCCCS ID:</span> {{ $this->carePlan->resident->ahcccs_id }}</span>
                                @endif
                                @if($this->carePlan->resident->date_of_birth)
                                    <span><span class="font-medium">DOB:</span> {{ $this->carePlan->resident->date_of_birth->format('m/d/Y') }}</span>
                                @endif
                                @if($this->carePlan->resident->admission_date)
                                    <span><span class="font-medium">Date of Intake:</span> {{ $this->carePlan->resident->admission_date->format('m/d/Y') }}</span>
                                @endif
                                @if($this->carePlan->review_date)
                                    <span><span class="font-medium">Tx Plan Review Date:</span> {{ $this->carePlan->review_date->format('m/d/Y') }}</span>
                                @endif
                                <span><span class="font-medium">Today's Date:</span> {{ now()->format('m/d/Y') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                @can('manage-care-plans')
                    <flux:button variant="primary" :href="route('care-plans.edit', $this->carePlan)" wire:navigate icon="pencil">
                        {{ __('Edit') }}
                    </flux:button>
                @endcan
            </div>
        </flux:card>

        {{-- Schedule --}}
        <flux:card class="space-y-3">
            <flux:heading size="sm">{{ __('Schedule') }}</flux:heading>
            <flux:separator />
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-zinc-500">{{ __('Start Date') }}</dt>
                    <dd class="font-medium">{{ $this->carePlan->start_date->format('M d, Y') }}</dd>
                </div>
                <div>
                    <dt class="text-zinc-500">{{ __('Tx Plan Review Date') }}</dt>
                    <dd class="font-medium">
                        @if($this->carePlan->review_date)
                            <span @class(['text-amber-600 dark:text-amber-400' => $this->carePlan->review_date->lte(now()->addDays(30))])>
                                {{ $this->carePlan->review_date->format('M d, Y') }}
                            </span>
                            @if($this->carePlan->review_date->lte(now()))
                                <flux:badge size="sm" color="red" class="ml-1">{{ __('Overdue') }}</flux:badge>
                            @elseif($this->carePlan->review_date->lte(now()->addDays(30)))
                                <flux:badge size="sm" color="amber" class="ml-1">{{ __('Due soon') }}</flux:badge>
                            @endif
                        @else
                            {{ __('Not set') }}
                        @endif
                    </dd>
                </div>
            </dl>
        </flux:card>

        {{-- Clinical Background --}}
        @if($this->carePlan->description)
            <flux:card class="space-y-3">
                <flux:heading size="sm">{{ __('Clinical Background') }}</flux:heading>
                <flux:separator />
                <flux:text class="whitespace-pre-wrap text-sm">{{ $this->carePlan->description }}</flux:text>
            </flux:card>
        @endif

        {{-- Treatment & Discharge Plan Goals --}}
        @if($this->carePlan->carePlanGoals->isNotEmpty())
            <flux:card class="space-y-3">
                <flux:heading size="sm">{{ __('Treatment & Discharge Plan') }}</flux:heading>
                <flux:separator />
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700 text-left">
                                <th class="pb-2 pr-4 font-medium text-zinc-500 w-1/4">{{ __('Problem / Challenge') }}</th>
                                <th class="pb-2 pr-4 font-medium text-zinc-500 w-1/4">{{ __('Case Manager Will') }}</th>
                                <th class="pb-2 pr-4 font-medium text-zinc-500 w-1/4">{{ __('Client Will') }}</th>
                                <th class="pb-2 pr-4 font-medium text-zinc-500">{{ __('Progress') }}</th>
                                <th class="pb-2 font-medium text-zinc-500">{{ __('Target Date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach($this->carePlan->carePlanGoals as $goal)
                                <tr class="align-top">
                                    <td class="py-3 pr-4 text-zinc-800 dark:text-zinc-200 whitespace-pre-wrap">{{ $goal->problem_description }}</td>
                                    <td class="py-3 pr-4 text-zinc-600 dark:text-zinc-400 whitespace-pre-wrap">{{ $goal->case_manager_actions ?? '—' }}</td>
                                    <td class="py-3 pr-4 text-zinc-600 dark:text-zinc-400 whitespace-pre-wrap">{{ $goal->client_actions ?? '—' }}</td>
                                    <td class="py-3 pr-4">
                                        <flux:badge size="sm" :color="$goal->progress_status_color">
                                            {{ $goal->progress_status_label }}
                                        </flux:badge>
                                    </td>
                                    <td class="py-3 text-zinc-500 whitespace-nowrap">
                                        {{ $goal->target_date?->format('m/d/Y') ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </flux:card>
        @endif

        {{-- Notes --}}
        @if($this->carePlan->notes)
            <flux:card class="space-y-3">
                <flux:heading size="sm">{{ __('Notes') }}</flux:heading>
                <flux:separator />
                <flux:text class="whitespace-pre-wrap text-sm">{{ $this->carePlan->notes }}</flux:text>
            </flux:card>
        @endif

        {{-- Recovery Team --}}
        @if($this->carePlan->recovery_team)
            <flux:card class="space-y-3">
                <flux:heading size="sm">{{ __('Recovery Team') }}</flux:heading>
                <flux:separator />
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700 text-left">
                                <th class="pb-2 pr-4 font-medium text-zinc-500 w-1/4">{{ __('Role') }}</th>
                                <th class="pb-2 pr-4 font-medium text-zinc-500">{{ __('Name') }}</th>
                                <th class="pb-2 pr-4 font-medium text-zinc-500">{{ __('Title / Credentials') }}</th>
                                <th class="pb-2 font-medium text-zinc-500">{{ __('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach($this->carePlan->recovery_team as $member)
                                <tr>
                                    <td class="py-2 pr-4 font-medium text-zinc-700 dark:text-zinc-300">{{ $member['role'] ?? '' }}</td>
                                    <td class="py-2 pr-4 text-zinc-600 dark:text-zinc-400">{{ $member['name'] ?: '—' }}</td>
                                    <td class="py-2 pr-4 text-zinc-600 dark:text-zinc-400">{{ $member['title'] ?: '—' }}</td>
                                    <td class="py-2 text-zinc-500">{{ $member['date'] ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </flux:card>
        @endif

        {{-- Metadata --}}
        <flux:text class="text-xs text-zinc-400">
            {{ __('Created') }} {{ $this->carePlan->created_at->format('M d, Y H:i') }}
            @if($this->carePlan->creator)
                {{ __('by') }} {{ $this->carePlan->creator->name }}
            @endif
            @if($this->carePlan->updater)
                &middot; {{ __('Updated by') }} {{ $this->carePlan->updater->name }}
            @endif
            @if($this->carePlan->reviewer)
                &middot; {{ __('Reviewed by') }} {{ $this->carePlan->reviewer->name }}
            @endif
        </flux:text>
    </div>
</flux:main>
