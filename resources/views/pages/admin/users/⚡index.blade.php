<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new
#[Layout('layouts.app.sidebar')]
#[Title('Users')]
class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $roleFilter = '';

    public ?int $deleteUserId = null;

    #[Computed]
    public function users()
    {
        return User::query()
            ->with('roles')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%"))
            ->when($this->roleFilter, fn($q) => $q->role($this->roleFilter))
            ->latest()
            ->paginate(15);
    }

    #[Computed]
    public function roles(): array
    {
        return Role::pluck('name', 'name')->toArray();
    }

    public function confirmDelete(int $userId): void
    {
        $this->deleteUserId = $userId;
    }

    public function deleteUser(): void
    {
        if ($this->deleteUserId) {
            $user = User::find($this->deleteUserId);
            if ($user && $user->id !== auth()->id()) {
                $user->delete();
                $this->dispatch('user-deleted');
            }
            $this->deleteUserId = null;
        }
    }

    public function cancelDelete(): void
    {
        $this->deleteUserId = null;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }
}; ?>

<flux:main>
    <x-pages.admin.layout :heading="__('Users')" :subheading="__('Manage system users and their roles')">
        <div class="space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-1 gap-4">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search users..."
                        icon="magnifying-glass"
                        class="max-w-xs"
                    />

                    <flux:select wire:model.live="roleFilter" class="max-w-xs">
                        <flux:select.option value="">{{ __('All Roles') }}</flux:select.option>
                        @foreach($this->roles as $role)
                            <flux:select.option value="{{ $role }}">
                                {{ str_replace('_', ' ', ucwords($role, '_')) }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <flux:button variant="primary" :href="route('admin.users.create')" wire:navigate icon="plus">
                    {{ __('Add User') }}
                </flux:button>
            </div>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                    <flux:table.column>{{ __('Email') }}</flux:table.column>
                    <flux:table.column>{{ __('Roles') }}</flux:table.column>
                    <flux:table.column>{{ __('Created') }}</flux:table.column>
                    <flux:table.column class="w-24"></flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse($this->users as $user)
                        <flux:table.row :key="$user->id">
                            <flux:table.cell>
                                <div class="flex items-center gap-3">
                                    <flux:avatar size="sm" name="{{ $user->name }}" />
                                    <span class="font-medium">{{ $user->name }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>{{ $user->email }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex flex-wrap gap-1">
                                    @forelse($user->roles as $role)
                                        <flux:badge size="sm" color="zinc">
                                            {{ str_replace('_', ' ', ucwords($role->name, '_')) }}
                                        </flux:badge>
                                    @empty
                                        <flux:badge size="sm" color="amber">{{ __('No Role') }}</flux:badge>
                                    @endforelse
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>{{ $user->created_at->format('M d, Y') }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:dropdown position="bottom" align="end">
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                    <flux:menu>
                                        <flux:menu.item :href="route('admin.users.edit', $user)" wire:navigate icon="pencil">
                                            {{ __('Edit') }}
                                        </flux:menu.item>
                                        @if($user->id !== auth()->id())
                                            <flux:menu.separator />
                                            <flux:menu.item variant="danger" wire:click="confirmDelete({{ $user->id }})" icon="trash">
                                                {{ __('Delete') }}
                                            </flux:menu.item>
                                        @endif
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="text-center py-8">
                                <x-dashboard.empty-state
                                    title="No users found"
                                    description="Try adjusting your search or filter"
                                    icon="users"
                                />
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <div class="mt-6">
                {{ $this->users->links() }}
            </div>
        </div>

        <flux:modal name="confirm-delete" :show="$deleteUserId !== null" class="max-w-md">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Delete User') }}</flux:heading>
                    <flux:subheading>
                        {{ __('Are you sure you want to delete this user? This action cannot be undone.') }}
                    </flux:subheading>
                </div>

                <div class="flex justify-end gap-3">
                    <flux:button variant="ghost" wire:click="cancelDelete">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button variant="danger" wire:click="deleteUser">
                        {{ __('Delete User') }}
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    </x-pages.admin.layout>
</flux:main>
