<?php

use App\Concerns\PasswordValidationRules;
use App\Concerns\UserValidationRules;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Edit User')]
class extends Component {
    use UserValidationRules, PasswordValidationRules;

    #[Locked]
    public int $userId;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public array $selectedRoles = [];
    public array $directPermissions = [];

    public function mount(User $user): void
    {
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
        $this->directPermissions = $user->getDirectPermissions()->pluck('name')->toArray();
    }

    #[Computed]
    public function user(): User
    {
        return User::findOrFail($this->userId);
    }

    #[Computed]
    public function roles()
    {
        return Role::all();
    }

    #[Computed]
    public function allPermissions()
    {
        return Permission::orderBy('name')->get();
    }

    #[Computed]
    public function permissionsFromRoles(): array
    {
        return Role::whereIn('name', $this->selectedRoles)
            ->with('permissions')
            ->get()
            ->flatMap(fn($role) => $role->permissions->pluck('name'))
            ->unique()
            ->toArray();
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

    public function updateProfile(): void
    {
        $validated = $this->validate($this->userRules($this->userId));
        $this->user->update($validated);
        $this->dispatch('profile-updated');
    }

    public function updatePassword(): void
    {
        if (empty($this->password)) {
            return;
        }

        $this->validate([
            'password' => $this->passwordRules(),
        ]);

        $this->user->update([
            'password' => Hash::make($this->password),
        ]);

        $this->password = '';
        $this->password_confirmation = '';
        $this->dispatch('password-updated');
    }

    public function updateRoles(): void
    {
        $this->validate($this->roleRules());

        $this->user->syncRoles($this->selectedRoles);

        // Clear direct permissions that are now covered by roles
        $rolePerms = $this->permissionsFromRoles;
        $newDirectPerms = array_diff($this->directPermissions, $rolePerms);
        $this->directPermissions = array_values($newDirectPerms);
        $this->user->syncPermissions($this->directPermissions);

        $this->dispatch('roles-updated');
    }

    public function updatePermissions(): void
    {
        $this->validate($this->permissionRules());

        // Only sync permissions that are NOT from roles
        $rolePerms = $this->permissionsFromRoles;
        $directOnly = array_diff($this->directPermissions, $rolePerms);

        $this->user->syncPermissions($directOnly);
        $this->dispatch('permissions-updated');
    }

    public function isPermissionFromRole(string $permission): bool
    {
        return in_array($permission, $this->permissionsFromRoles);
    }
}; ?>

<flux:main>
    <x-pages.admin.layout :heading="__('Edit User')" :subheading="$this->user->email">
        <div class="space-y-8 max-w-3xl">
            <flux:card>
                <flux:heading size="sm" class="mb-4">{{ __('Profile Information') }}</flux:heading>

                <form wire:submit="updateProfile" class="space-y-4">
                    <flux:input wire:model="name" :label="__('Name')" required />
                    <flux:input wire:model="email" :label="__('Email')" type="email" required />

                    <div class="flex items-center gap-4">
                        <flux:button variant="primary" type="submit">
                            {{ __('Save') }}
                        </flux:button>
                        <x-action-message on="profile-updated">{{ __('Saved.') }}</x-action-message>
                    </div>
                </form>
            </flux:card>

            <flux:card>
                <flux:heading size="sm" class="mb-2">{{ __('Update Password') }}</flux:heading>
                <flux:subheading class="mb-4">{{ __('Leave blank to keep current password.') }}</flux:subheading>

                <form wire:submit="updatePassword" class="space-y-4">
                    <flux:input wire:model="password" :label="__('New Password')" type="password" />
                    <flux:input wire:model="password_confirmation" :label="__('Confirm Password')" type="password" />

                    <div class="flex items-center gap-4">
                        <flux:button variant="primary" type="submit">
                            {{ __('Update Password') }}
                        </flux:button>
                        <x-action-message on="password-updated">{{ __('Updated.') }}</x-action-message>
                    </div>
                </form>
            </flux:card>

            <flux:card>
                <flux:heading size="sm" class="mb-2">{{ __('Roles') }}</flux:heading>
                <flux:subheading class="mb-4">
                    {{ __('Roles provide a set of default permissions.') }}
                </flux:subheading>

                <form wire:submit="updateRoles" class="space-y-4">
                    <div class="space-y-2">
                        @foreach($this->roles as $role)
                            <label class="flex items-center gap-3 p-2 rounded cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <flux:checkbox
                                    wire:model.live="selectedRoles"
                                    value="{{ $role->name }}"
                                />
                                <span>{{ str_replace('_', ' ', ucwords($role->name, '_')) }}</span>
                            </label>
                        @endforeach
                    </div>

                    @error('selectedRoles')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror

                    <div class="flex items-center gap-4">
                        <flux:button variant="primary" type="submit">
                            {{ __('Save Roles') }}
                        </flux:button>
                        <x-action-message on="roles-updated">{{ __('Saved.') }}</x-action-message>
                    </div>
                </form>
            </flux:card>

            <flux:card>
                <flux:heading size="sm" class="mb-2">{{ __('Permission Overrides') }}</flux:heading>
                <flux:subheading class="mb-4">
                    {{ __('Grant additional permissions beyond those provided by roles. Permissions marked with a checkmark come from assigned roles.') }}
                </flux:subheading>

                <form wire:submit="updatePermissions" class="space-y-6">
                    @foreach($this->permissionGroups as $groupName => $groupPermissions)
                        <div>
                            <flux:heading size="xs" class="mb-3">{{ $groupName }}</flux:heading>
                            <div class="grid gap-2 sm:grid-cols-2">
                                @foreach($groupPermissions as $permission)
                                    @php
                                        $fromRole = $this->isPermissionFromRole($permission);
                                        $label = str_replace('-', ' ', ucwords($permission, '-'));
                                    @endphp
                                    <label class="flex items-center gap-3 p-2 rounded cursor-pointer {{ $fromRole ? 'bg-green-50 dark:bg-green-900/20' : 'hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}">
                                        @if($fromRole)
                                            <flux:icon name="check-circle" variant="solid" class="size-5 text-green-600" />
                                            <span class="text-green-700 dark:text-green-400">{{ $label }}</span>
                                            <flux:badge size="sm" color="green">{{ __('from role') }}</flux:badge>
                                        @else
                                            <flux:checkbox
                                                wire:model="directPermissions"
                                                value="{{ $permission }}"
                                            />
                                            <span>{{ $label }}</span>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <div class="flex items-center gap-4">
                        <flux:button variant="primary" type="submit">
                            {{ __('Save Permissions') }}
                        </flux:button>
                        <x-action-message on="permissions-updated">{{ __('Saved.') }}</x-action-message>
                    </div>
                </form>
            </flux:card>

            <flux:button variant="ghost" :href="route('admin.users.index')" wire:navigate icon="arrow-left">
                {{ __('Back to Users') }}
            </flux:button>
        </div>
    </x-pages.admin.layout>
</flux:main>
