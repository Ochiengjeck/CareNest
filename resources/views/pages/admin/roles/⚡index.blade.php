<?php

use Spatie\Permission\Models\Role;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Roles & Permissions')]
class extends Component {
    #[Computed]
    public function roles()
    {
        return Role::withCount(['users', 'permissions'])->get();
    }
}; ?>

<flux:main>
    <x-pages.admin.layout :heading="__('Roles & Permissions')" :subheading="__('Manage role permissions')">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Role') }}</flux:table.column>
                <flux:table.column>{{ __('Users') }}</flux:table.column>
                <flux:table.column>{{ __('Permissions') }}</flux:table.column>
                <flux:table.column class="w-32"></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach($this->roles as $role)
                    <flux:table.row :key="$role->id">
                        <flux:table.cell>
                            <span class="font-medium">
                                {{ str_replace('_', ' ', ucwords($role->name, '_')) }}
                            </span>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm">{{ $role->users_count }} {{ __('users') }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="zinc">{{ $role->permissions_count }} {{ __('permissions') }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:button variant="ghost" size="sm" :href="route('admin.roles.edit', $role)" wire:navigate icon="pencil">
                                {{ __('Edit') }}
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </x-pages.admin.layout>
</flux:main>
