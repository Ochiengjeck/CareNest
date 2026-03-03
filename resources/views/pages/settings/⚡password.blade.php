<?php

use App\Concerns\PasswordValidationRules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

new class extends Component {
    use PasswordValidationRules;

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => $this->currentPasswordRules(),
                'password' => $this->passwordRules(),
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <div class="max-w-2xl">
        <flux:card>
            <form wire:submit="updatePassword">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="flex size-9 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/20">
                            <flux:icon.lock-closed class="size-5 text-amber-600 dark:text-amber-400" />
                        </div>
                        <div>
                            <flux:heading size="lg">{{ __('Change Password') }}</flux:heading>
                            <flux:text size="sm" class="text-zinc-500">{{ __('Use a long, random password to stay secure') }}</flux:text>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-3">
                        <x-action-message class="text-sm text-green-600 dark:text-green-400" on="password-updated">
                            {{ __('Saved.') }}
                        </x-action-message>
                        <flux:button variant="primary" size="sm" type="submit" data-test="update-password-button">
                            {{ __('Save Changes') }}
                        </flux:button>
                    </div>
                </div>

                <div class="mb-5 rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 dark:border-blue-900/40 dark:bg-blue-900/20">
                    <div class="flex items-start gap-2">
                        <flux:icon.information-circle class="mt-0.5 size-4 shrink-0 text-blue-600 dark:text-blue-400" />
                        <flux:text size="sm" class="text-blue-700 dark:text-blue-300">
                            {{ __('Choose a password with at least 12 characters, mixing upper and lower case letters, numbers, and symbols.') }}
                        </flux:text>
                    </div>
                </div>

                <div class="space-y-4">
                    <flux:input
                        wire:model="current_password"
                        :label="__('Current Password')"
                        type="password"
                        required
                        autocomplete="current-password"
                    />
                    <flux:input
                        wire:model="password"
                        :label="__('New Password')"
                        type="password"
                        required
                        autocomplete="new-password"
                    />
                    <flux:input
                        wire:model="password_confirmation"
                        :label="__('Confirm New Password')"
                        type="password"
                        required
                        autocomplete="new-password"
                    />
                </div>
            </form>
        </flux:card>
    </div>
</section>
