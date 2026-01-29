<?php

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Edit Role')]
class extends Component {
    #[Locked]
    public int $roleId;

    public array $selectedPermissions = [];

    public function mount(Role $role): void
    {
        $this->roleId = $role->id;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
    }

    #[Computed]
    public function role(): Role
    {
        return Role::findOrFail($this->roleId);
    }

    #[Computed]
    public function roleName(): string
    {
        return str_replace('_', ' ', ucwords($this->role->name, '_'));
    }

    #[Computed]
    public function permissionGroups(): array
    {
        return [
            'User & System' => ['manage-users', 'manage-roles', 'view-audit-logs', 'manage-settings'],
            'Residents' => ['manage-residents', 'view-residents'],
            'Staff' => ['manage-staff', 'view-staff'],
            'Clinical' => ['manage-medications', 'administer-medications', 'manage-care-plans', 'view-care-plans'],
            'Activities & Incidents' => ['log-activities', 'manage-incidents', 'report-incidents'],
            'Reports' => ['view-reports'],
        ];
    }

    public function updatePermissions(): void
    {
        $this->role->syncPermissions($this->selectedPermissions);

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->dispatch('permissions-updated');
    }

    public function selectAll(): void
    {
        $this->selectedPermissions = Permission::pluck('name')->toArray();
    }

    public function deselectAll(): void
    {
        $this->selectedPermissions = [];
    }
}; ?>

<flux:main>
    <x-pages.admin.layout
        :heading="$this->roleName"
        :subheading="__('Manage permissions for this role')">

        <form wire:submit="updatePermissions" class="space-y-6 max-w-3xl">
            <div class="flex gap-4">
                <flux:button variant="ghost" size="sm" wire:click="selectAll" type="button">
                    {{ __('Select All') }}
                </flux:button>
                <flux:button variant="ghost" size="sm" wire:click="deselectAll" type="button">
                    {{ __('Deselect All') }}
                </flux:button>
            </div>

            @foreach($this->permissionGroups as $groupName => $groupPermissions)
                <flux:card>
                    <flux:heading size="sm" class="mb-4">{{ $groupName }}</flux:heading>

                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach($groupPermissions as $permission)
                            <label class="flex items-center gap-3 p-2 rounded cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <flux:checkbox
                                    wire:model="selectedPermissions"
                                    value="{{ $permission }}"
                                />
                                <span>{{ str_replace('-', ' ', ucwords($permission, '-')) }}</span>
                            </label>
                        @endforeach
                    </div>
                </flux:card>
            @endforeach

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">
                    {{ __('Save Permissions') }}
                </flux:button>
                <x-action-message on="permissions-updated">{{ __('Saved.') }}</x-action-message>
                <flux:button variant="ghost" :href="route('admin.roles.index')" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
            </div>
        </form>

        <div class="mt-8">
            <flux:button variant="ghost" :href="route('admin.roles.index')" wire:navigate icon="arrow-left">
                {{ __('Back to Roles') }}
            </flux:button>
        </div>
    </x-pages.admin.layout>
</flux:main>
