<?php

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new
#[Layout('layouts.app.sidebar')]
#[Title('Staff Directory')]
class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $departmentFilter = '';

    #[Url]
    public string $statusFilter = '';

    #[Computed]
    public function staff()
    {
        return User::query()
            ->whereHas('roles')
            ->with(['roles', 'staffProfile'])
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%"))
            ->when($this->departmentFilter, fn ($q) => $q->whereHas('staffProfile', fn ($sp) => $sp->where('department', $this->departmentFilter)))
            ->when($this->statusFilter, fn ($q) => $q->whereHas('staffProfile', fn ($sp) => $sp->where('employment_status', $this->statusFilter)))
            ->orderBy('name')
            ->paginate(15);
    }

    #[Computed]
    public function departments(): array
    {
        return \App\Models\StaffProfile::query()
            ->whereNotNull('department')
            ->distinct()
            ->pluck('department')
            ->sort()
            ->values()
            ->toArray();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedDepartmentFilter(): void
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
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Staff Directory') }}</flux:heading>
                <flux:subheading>{{ __('View and manage staff members') }}</flux:subheading>
            </div>
        </div>

        <div class="flex flex-1 flex-wrap gap-4">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Search by name or email..."
                icon="magnifying-glass"
                class="max-w-xs"
            />

            @if(count($this->departments) > 0)
                <flux:select wire:model.live="departmentFilter" class="max-w-[180px]">
                    <flux:select.option value="">{{ __('All Departments') }}</flux:select.option>
                    @foreach($this->departments as $dept)
                        <flux:select.option value="{{ $dept }}">{{ $dept }}</flux:select.option>
                    @endforeach
                </flux:select>
            @endif

            <flux:select wire:model.live="statusFilter" class="max-w-[180px]">
                <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                <flux:select.option value="on_leave">{{ __('On Leave') }}</flux:select.option>
                <flux:select.option value="suspended">{{ __('Suspended') }}</flux:select.option>
                <flux:select.option value="terminated">{{ __('Terminated') }}</flux:select.option>
            </flux:select>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column>{{ __('Role') }}</flux:table.column>
                <flux:table.column>{{ __('Department') }}</flux:table.column>
                <flux:table.column>{{ __('Position') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column class="w-24"></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($this->staff as $member)
                    <flux:table.row :key="$member->id">
                        <flux:table.cell>
                            <div class="flex items-center gap-3">
                                <flux:avatar :name="$member->name" size="sm" />
                                <div>
                                    <flux:link :href="route('staff.show', $member)" wire:navigate class="font-medium">
                                        {{ $member->name }}
                                    </flux:link>
                                    <flux:text size="sm" class="text-zinc-500">{{ $member->email }}</flux:text>
                                </div>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex flex-wrap gap-1">
                                @foreach($member->roles as $role)
                                    <flux:badge size="sm" color="zinc">
                                        {{ str_replace('_', ' ', ucwords($role->name, '_')) }}
                                    </flux:badge>
                                @endforeach
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>{{ $member->staffProfile?->department ?? '-' }}</flux:table.cell>
                        <flux:table.cell>{{ $member->staffProfile?->position ?? '-' }}</flux:table.cell>
                        <flux:table.cell>
                            @if($member->staffProfile)
                                <flux:badge size="sm" :color="$member->staffProfile->status_color">
                                    {{ $member->staffProfile->status_label }}
                                </flux:badge>
                            @else
                                <flux:badge size="sm" color="zinc">{{ __('No Profile') }}</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                <flux:menu>
                                    <flux:menu.item :href="route('staff.show', $member)" wire:navigate icon="eye">
                                        {{ __('View') }}
                                    </flux:menu.item>
                                    @can('manage-staff')
                                        <flux:menu.item :href="route('staff.edit', $member)" wire:navigate icon="pencil">
                                            {{ __('Edit') }}
                                        </flux:menu.item>
                                    @endcan
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center py-8">
                            <x-dashboard.empty-state
                                title="No staff members found"
                                description="Try adjusting your search or filters."
                                icon="users"
                            />
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="mt-6">
            {{ $this->staff->links() }}
        </div>
    </div>
</flux:main>
