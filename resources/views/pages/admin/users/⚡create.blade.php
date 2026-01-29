<?php

use App\Concerns\PasswordValidationRules;
use App\Concerns\UserValidationRules;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Create User')]
class extends Component {
    use UserValidationRules, PasswordValidationRules;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public array $selectedRoles = [];

    #[Computed]
    public function roles()
    {
        return Role::all();
    }

    #[Computed]
    public function rolePermissionsMap(): array
    {
        return Role::with('permissions')->get()->mapWithKeys(function ($role) {
            return [$role->name => $role->permissions->pluck('name')->toArray()];
        })->toArray();
    }

    public function createUser(): void
    {
        $validated = $this->validate([
            ...$this->userRules(),
            'password' => $this->passwordRules(),
            ...$this->roleRules(),
        ]);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'email_verified_at' => now(),
        ]);

        $user->syncRoles($this->selectedRoles);

        session()->flash('status', 'User created successfully.');
        $this->redirect(route('admin.users.index'), navigate: true);
    }
}; ?>

<flux:main>
    <x-pages.admin.layout :heading="__('Create User')" :subheading="__('Add a new user to the system')">
        <form wire:submit="createUser" class="space-y-6 max-w-2xl">
            <flux:card>
                <flux:heading size="sm" class="mb-4">{{ __('User Information') }}</flux:heading>

                <div class="space-y-4">
                    <flux:input wire:model="name" :label="__('Name')" required autofocus />
                    <flux:input wire:model="email" :label="__('Email')" type="email" required />
                    <flux:input wire:model="password" :label="__('Password')" type="password" required />
                    <flux:input wire:model="password_confirmation" :label="__('Confirm Password')" type="password" required />
                </div>
            </flux:card>

            <flux:card>
                <flux:heading size="sm" class="mb-2">{{ __('Assign Roles') }}</flux:heading>
                <flux:subheading class="mb-4">
                    {{ __('Select roles for this user. Each role comes with default permissions.') }}
                </flux:subheading>

                <div class="space-y-3">
                    @foreach($this->roles as $role)
                        <label class="flex items-start gap-3 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                            <flux:checkbox
                                wire:model="selectedRoles"
                                value="{{ $role->name }}"
                            />
                            <div class="flex-1">
                                <div class="font-medium">{{ str_replace('_', ' ', ucwords($role->name, '_')) }}</div>
                                <div class="text-sm text-zinc-500 mt-1">
                                    @php
                                        $permissions = $this->rolePermissionsMap[$role->name] ?? [];
                                        $displayPermissions = array_slice($permissions, 0, 5);
                                        $remaining = count($permissions) - 5;
                                    @endphp
                                    {{ implode(', ', array_map(fn($p) => str_replace('-', ' ', $p), $displayPermissions)) }}
                                    @if($remaining > 0)
                                        <span class="text-zinc-400">+{{ $remaining }} more</span>
                                    @endif
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>

                @error('selectedRoles')
                    <flux:error class="mt-2">{{ $message }}</flux:error>
                @enderror
            </flux:card>

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">
                    {{ __('Create User') }}
                </flux:button>
                <flux:button variant="ghost" :href="route('admin.users.index')" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
            </div>
        </form>
    </x-pages.admin.layout>
</flux:main>
