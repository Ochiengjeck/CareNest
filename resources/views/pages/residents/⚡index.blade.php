<?php

use App\Models\Resident;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new
#[Layout('layouts.app.sidebar')]
#[Title('Residents')]
class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    public ?int $deleteResidentId = null;

    #[Computed]
    public function residents()
    {
        return Resident::query()
            ->withCount('carePlans')
            ->when($this->search, fn ($q) => $q->search($this->search))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(15);
    }

    public function confirmDelete(int $residentId): void
    {
        $this->deleteResidentId = $residentId;
    }

    public function deleteResident(): void
    {
        if ($this->deleteResidentId) {
            $resident = Resident::find($this->deleteResidentId);
            if ($resident) {
                $resident->delete();
            }
            $this->deleteResidentId = null;
        }
    }

    public function cancelDelete(): void
    {
        $this->deleteResidentId = null;
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
    <div class="space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Residents') }}</flux:heading>
            <flux:subheading>{{ __('Manage care home residents and their information') }}</flux:subheading>
        </div>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-1 gap-4">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by name or room..."
                    icon="magnifying-glass"
                    class="max-w-xs"
                />

                <flux:select wire:model.live="statusFilter" class="max-w-xs">
                    <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                    <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                    <flux:select.option value="discharged">{{ __('Discharged') }}</flux:select.option>
                    <flux:select.option value="deceased">{{ __('Deceased') }}</flux:select.option>
                    <flux:select.option value="on_leave">{{ __('On Leave') }}</flux:select.option>
                </flux:select>
            </div>

            @can('manage-residents')
                <flux:button variant="primary" :href="route('residents.create')" wire:navigate icon="plus">
                    {{ __('Add Resident') }}
                </flux:button>
            @endcan
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Resident') }}</flux:table.column>
                <flux:table.column>{{ __('Age') }}</flux:table.column>
                <flux:table.column>{{ __('Room') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column>{{ __('Admitted') }}</flux:table.column>
                <flux:table.column>{{ __('Care Plans') }}</flux:table.column>
                <flux:table.column class="w-24"></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($this->residents as $resident)
                    <flux:table.row :key="$resident->id">
                        <flux:table.cell>
                            <div class="flex items-center gap-3">
                                @if($resident->photo_path)
                                    <img src="{{ Storage::url($resident->photo_path) }}" alt="" class="size-8 rounded-full object-cover" />
                                @else
                                    <flux:avatar size="sm" name="{{ $resident->full_name }}" />
                                @endif
                                <div>
                                    <flux:link :href="route('residents.show', $resident)" wire:navigate class="font-medium">
                                        {{ $resident->full_name }}
                                    </flux:link>
                                    <div class="text-xs text-zinc-500">{{ $resident->gender === 'male' ? 'M' : ($resident->gender === 'female' ? 'F' : 'O') }}</div>
                                </div>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>{{ $resident->age }}</flux:table.cell>
                        <flux:table.cell>{{ $resident->room_number ?? '-' }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" :color="match($resident->status) {
                                'active' => 'green',
                                'discharged' => 'amber',
                                'deceased' => 'red',
                                'on_leave' => 'blue',
                                default => 'zinc',
                            }">
                                {{ str_replace('_', ' ', ucfirst($resident->status)) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $resident->admission_date->format('M d, Y') }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="zinc">{{ $resident->care_plans_count }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                <flux:menu>
                                    <flux:menu.item :href="route('residents.show', $resident)" wire:navigate icon="eye">
                                        {{ __('View') }}
                                    </flux:menu.item>
                                    @can('manage-residents')
                                        <flux:menu.item :href="route('residents.edit', $resident)" wire:navigate icon="pencil">
                                            {{ __('Edit') }}
                                        </flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item variant="danger" wire:click="confirmDelete({{ $resident->id }})" icon="trash">
                                            {{ __('Delete') }}
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
                                title="No residents found"
                                description="Try adjusting your search or filter, or add a new resident."
                                icon="user-group"
                            />
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="mt-6">
            {{ $this->residents->links() }}
        </div>
    </div>

    <flux:modal name="confirm-delete" :show="$deleteResidentId !== null" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Delete Resident') }}</flux:heading>
                <flux:subheading>
                    {{ __('Are you sure you want to delete this resident? Their care plans will also be removed. This action can be reversed by an administrator.') }}
                </flux:subheading>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="cancelDelete">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="danger" wire:click="deleteResident">
                    {{ __('Delete Resident') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</flux:main>
