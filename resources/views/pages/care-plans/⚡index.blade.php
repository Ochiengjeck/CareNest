<?php

use App\Models\CarePlan;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new
#[Layout('layouts.app.sidebar')]
#[Title('Care Plans')]
class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $typeFilter = '';

    #[Url]
    public string $statusFilter = '';

    #[Url]
    public string $viewMode = 'card';

    #[Computed]
    public function carePlans()
    {
        return CarePlan::query()
            ->with('resident')
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%")
                ->orWhereHas('resident', fn ($r) => $r->where('first_name', 'like', "%{$this->search}%")
                    ->orWhere('last_name', 'like', "%{$this->search}%")))
            ->when($this->typeFilter, fn ($q) => $q->byType($this->typeFilter))
            ->when($this->statusFilter, fn ($q) => $q->byStatus($this->statusFilter))
            ->latest()
            ->paginate(15);
    }

    #[Computed]
    public function totalCount(): int
    {
        return CarePlan::count();
    }

    #[Computed]
    public function activeCount(): int
    {
        return CarePlan::where('status', 'active')->count();
    }

    #[Computed]
    public function draftCount(): int
    {
        return CarePlan::where('status', 'draft')->count();
    }

    #[Computed]
    public function dueForReviewCount(): int
    {
        return CarePlan::active()
            ->whereNotNull('review_date')
            ->where('review_date', '<=', now()->addDays(7))
            ->count();
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }
}; ?>

<flux:main>
    <div class="space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Care Plans') }}</flux:heading>
            <flux:subheading>{{ __('View and manage care plans across all residents') }}</flux:subheading>
        </div>

        {{-- Summary Stats --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-dashboard.stat-card
                title="Total Care Plans"
                :value="$this->totalCount"
                icon="clipboard-document-list"
            />
            <x-dashboard.stat-card
                title="Active"
                :value="$this->activeCount"
                icon="check-circle"
                description="Currently active"
            />
            <x-dashboard.stat-card
                title="Drafts"
                :value="$this->draftCount"
                icon="pencil-square"
            />
            <x-dashboard.stat-card
                title="Due for Review"
                :value="$this->dueForReviewCount"
                icon="clock"
                description="Within 7 days"
            />
        </div>

        {{-- Toolbar --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-1 flex-wrap items-center gap-4">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by title or resident..."
                    icon="magnifying-glass"
                    class="max-w-xs"
                />

                <flux:select wire:model.live="typeFilter" class="max-w-[160px]">
                    <flux:select.option value="">{{ __('All Types') }}</flux:select.option>
                    <flux:select.option value="general">{{ __('General') }}</flux:select.option>
                    <flux:select.option value="nutrition">{{ __('Nutrition') }}</flux:select.option>
                    <flux:select.option value="mobility">{{ __('Mobility') }}</flux:select.option>
                    <flux:select.option value="mental_health">{{ __('Mental Health') }}</flux:select.option>
                    <flux:select.option value="personal_care">{{ __('Personal Care') }}</flux:select.option>
                    <flux:select.option value="medication">{{ __('Medication') }}</flux:select.option>
                    <flux:select.option value="social">{{ __('Social') }}</flux:select.option>
                </flux:select>

                <flux:select wire:model.live="statusFilter" class="max-w-[160px]">
                    <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                    <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                    <flux:select.option value="draft">{{ __('Draft') }}</flux:select.option>
                    <flux:select.option value="under_review">{{ __('Under Review') }}</flux:select.option>
                    <flux:select.option value="archived">{{ __('Archived') }}</flux:select.option>
                </flux:select>

                <flux:button.group>
                    <flux:button
                        :variant="$viewMode === 'card' ? 'filled' : 'ghost'"
                        size="sm"
                        icon="squares-2x2"
                        wire:click="setViewMode('card')"
                        title="Card view"
                    />
                    <flux:button
                        :variant="$viewMode === 'table' ? 'filled' : 'ghost'"
                        size="sm"
                        icon="table-cells"
                        wire:click="setViewMode('table')"
                        title="Table view"
                    />
                </flux:button.group>
            </div>
        </div>

        {{-- Card Grid View --}}
        @if($viewMode === 'card')
            <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
                @forelse($this->carePlans as $plan)
                    <flux:card class="relative flex flex-col space-y-3 p-5" :key="$plan->id">
                        {{-- Actions dropdown --}}
                        @can('manage-care-plans')
                            <div class="absolute top-2 right-2">
                                <flux:dropdown position="bottom" align="end">
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                    <flux:menu>
                                        <flux:menu.item :href="route('care-plans.edit', $plan)" wire:navigate icon="pencil">
                                            {{ __('Edit') }}
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
                        @endcan

                        {{-- Title --}}
                        <div class="pr-8">
                            <flux:link :href="route('care-plans.show', $plan)" wire:navigate class="font-medium text-sm">
                                {{ $plan->title }}
                            </flux:link>
                        </div>

                        {{-- Resident --}}
                        @if($plan->resident)
                            <div class="flex items-center gap-2">
                                @if($plan->resident->photo_path)
                                    <img src="{{ Storage::url($plan->resident->photo_path) }}" alt="" class="size-6 rounded-full object-cover" />
                                @else
                                    <flux:avatar size="xs" name="{{ $plan->resident->full_name }}" />
                                @endif
                                <flux:text class="text-xs text-zinc-500">{{ $plan->resident->full_name }}</flux:text>
                            </div>
                        @endif

                        {{-- Badges --}}
                        <div class="flex flex-wrap gap-1.5">
                            <flux:badge size="sm" color="zinc">{{ $plan->type_label }}</flux:badge>
                            <flux:badge size="sm" :color="$plan->status_color">
                                {{ str_replace('_', ' ', ucfirst($plan->status)) }}
                            </flux:badge>
                        </div>

                        {{-- Dates --}}
                        <div class="flex gap-4 text-xs text-zinc-500">
                            <div>
                                <span class="text-zinc-400">{{ __('Start') }}:</span>
                                {{ $plan->start_date->format('M d, Y') }}
                            </div>
                            @if($plan->review_date)
                                <div>
                                    <span class="text-zinc-400">{{ __('Review') }}:</span>
                                    <span @class(['text-amber-600 dark:text-amber-400 font-medium' => $plan->review_date->lte(now()->addDays(7))])>
                                        {{ $plan->review_date->format('M d, Y') }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        {{-- View button --}}
                        <flux:button variant="ghost" size="sm" :href="route('care-plans.show', $plan)" wire:navigate icon="eye" class="w-full mt-auto">
                            {{ __('View Plan') }}
                        </flux:button>
                    </flux:card>
                @empty
                    <div class="col-span-full">
                        <x-dashboard.empty-state
                            title="No care plans found"
                            description="Try adjusting your search or filters."
                            icon="clipboard-document-list"
                        />
                    </div>
                @endforelse
            </div>
        @else
            {{-- Table View --}}
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Title') }}</flux:table.column>
                    <flux:table.column>{{ __('Resident') }}</flux:table.column>
                    <flux:table.column>{{ __('Type') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Start Date') }}</flux:table.column>
                    <flux:table.column>{{ __('Review Date') }}</flux:table.column>
                    <flux:table.column class="w-24"></flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse($this->carePlans as $plan)
                        <flux:table.row :key="$plan->id">
                            <flux:table.cell>
                                <flux:link :href="route('care-plans.show', $plan)" wire:navigate class="font-medium">
                                    {{ $plan->title }}
                                </flux:link>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($plan->resident)
                                    <flux:link :href="route('residents.show', $plan->resident)" wire:navigate>
                                        {{ $plan->resident->full_name }}
                                    </flux:link>
                                @else
                                    <flux:text class="text-zinc-400">-</flux:text>
                                @endif
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
                                <flux:dropdown position="bottom" align="end">
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                    <flux:menu>
                                        <flux:menu.item :href="route('care-plans.show', $plan)" wire:navigate icon="eye">
                                            {{ __('View') }}
                                        </flux:menu.item>
                                        @can('manage-care-plans')
                                            <flux:menu.item :href="route('care-plans.edit', $plan)" wire:navigate icon="pencil">
                                                {{ __('Edit') }}
                                            </flux:menu.item>
                                        @endcan
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="7" class="text-center py-8">
                                <x-dashboard.empty-state
                                    title="No care plans found"
                                    description="Try adjusting your search or filters."
                                    icon="clipboard-document-list"
                                />
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        @endif

        <div class="mt-6">
            {{ $this->carePlans->links() }}
        </div>
    </div>
</flux:main>
