<?php

use App\Models\Agency;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new
#[Layout('layouts.app.sidebar')]
#[Title('Agencies')]
class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    public ?int $deleteAgencyId = null;

    #[Computed]
    public function agencies()
    {
        return Agency::query()
            ->when($this->search, fn($q) => $q->search($this->search))
            ->when($this->statusFilter === 'active', fn($q) => $q->active())
            ->when($this->statusFilter === 'inactive', fn($q) => $q->inactive())
            ->orderByDesc('is_institution')
            ->orderBy('name')
            ->paginate(15);
    }

    public function toggleStatus(int $agencyId): void
    {
        $agency = Agency::find($agencyId);
        if ($agency && !$agency->is_institution) {
            $agency->update([
                'is_active' => !$agency->is_active,
                'updated_by' => auth()->id(),
            ]);
        }
    }

    public function confirmDelete(int $agencyId): void
    {
        $this->deleteAgencyId = $agencyId;
    }

    public function deleteAgency(): void
    {
        if ($this->deleteAgencyId) {
            $agency = Agency::find($this->deleteAgencyId);
            if ($agency && !$agency->is_institution) {
                $agency->delete();
                $this->dispatch('agency-deleted');
            }
            $this->deleteAgencyId = null;
        }
    }

    public function cancelDelete(): void
    {
        $this->deleteAgencyId = null;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }
}; ?>

<flux:main>
    <x-pages.admin.layout :heading="__('Agencies')" :subheading="__('Manage agency and contact information for discharges')">
        <div class="space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-1 gap-4">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search agencies..."
                        icon="magnifying-glass"
                        class="max-w-xs"
                    />

                    <flux:select wire:model.live="statusFilter" class="max-w-xs">
                        <flux:select.option value="">{{ __('All Status') }}</flux:select.option>
                        <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                        <flux:select.option value="inactive">{{ __('Inactive') }}</flux:select.option>
                    </flux:select>
                </div>

                <flux:button variant="primary" :href="route('admin.agencies.create')" wire:navigate icon="plus">
                    {{ __('Add Agency') }}
                </flux:button>
            </div>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                    <flux:table.column>{{ __('Phone') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column class="w-32"></flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse($this->agencies as $agency)
                        <flux:table.row :key="$agency->id" class="{{ $agency->is_institution ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">{{ $agency->name }}</span>
                                    @if($agency->is_institution)
                                        <flux:badge size="sm" color="blue">{{ __('Institution') }}</flux:badge>
                                    @endif
                                </div>
                                @if($agency->address)
                                    <div class="text-xs text-zinc-500 mt-1">{{ $agency->address }}</div>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($agency->phone)
                                    <span class="font-mono">{{ $agency->phone }}</span>
                                @else
                                    <span class="text-zinc-400">{{ __('Not set') }}</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$agency->status_color">
                                    {{ $agency->status_label }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    @unless($agency->is_institution)
                                        <flux:button
                                            variant="ghost"
                                            size="sm"
                                            wire:click="toggleStatus({{ $agency->id }})"
                                            icon="{{ $agency->is_active ? 'eye-slash' : 'eye' }}"
                                            title="{{ $agency->is_active ? __('Deactivate') : __('Activate') }}"
                                        />
                                    @endunless

                                    <flux:dropdown position="bottom" align="end">
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                        <flux:menu>
                                            <flux:menu.item :href="route('admin.agencies.edit', $agency)" wire:navigate icon="pencil">
                                                {{ __('Edit') }}
                                            </flux:menu.item>
                                            @unless($agency->is_institution)
                                                <flux:menu.separator />
                                                <flux:menu.item variant="danger" wire:click="confirmDelete({{ $agency->id }})" icon="trash">
                                                    {{ __('Delete') }}
                                                </flux:menu.item>
                                            @endunless
                                        </flux:menu>
                                    </flux:dropdown>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4" class="text-center py-8">
                                <x-dashboard.empty-state
                                    title="No agencies found"
                                    description="Try adjusting your search or add a new agency"
                                    icon="building-office-2"
                                />
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <div class="mt-6">
                {{ $this->agencies->links() }}
            </div>
        </div>

        <flux:modal name="confirm-delete" :show="$deleteAgencyId !== null" class="max-w-md">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Delete Agency') }}</flux:heading>
                    <flux:subheading>
                        {{ __('Are you sure you want to delete this agency? This action cannot be undone.') }}
                    </flux:subheading>
                </div>

                <div class="flex justify-end gap-3">
                    <flux:button variant="ghost" wire:click="cancelDelete">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button variant="danger" wire:click="deleteAgency">
                        {{ __('Delete Agency') }}
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    </x-pages.admin.layout>
</flux:main>
