<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Set new password')" :description="__('Your new password must be different from previous passwords')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-5">
            @csrf
            <!-- Token -->
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <!-- Email Address -->
            <div>
                <flux:label for="email">{{ __('Email address') }}</flux:label>
                <div class="relative mt-1">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-400 dark:text-zinc-500">
                        <flux:icon.envelope class="size-5" />
                    </div>
                    <flux:input
                        id="email"
                        name="email"
                        value="{{ request('email') }}"
                        type="email"
                        required
                        autocomplete="email"
                        class="!pl-10"
                    />
                </div>
            </div>

            <!-- Password -->
            <div>
                <flux:label for="password">{{ __('New password') }}</flux:label>
                <div class="relative mt-1">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-400 dark:text-zinc-500 z-10">
                        <flux:icon.lock-closed class="size-5" />
                    </div>
                    <flux:input
                        id="password"
                        name="password"
                        type="password"
                        required
                        autocomplete="new-password"
                        :placeholder="__('Enter new password')"
                        viewable
                        class="!pl-10"
                    />
                </div>
            </div>

            <!-- Confirm Password -->
            <div>
                <flux:label for="password_confirmation">{{ __('Confirm new password') }}</flux:label>
                <div class="relative mt-1">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-400 dark:text-zinc-500 z-10">
                        <flux:icon.lock-closed class="size-5" />
                    </div>
                    <flux:input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        required
                        autocomplete="new-password"
                        :placeholder="__('Confirm new password')"
                        viewable
                        class="!pl-10"
                    />
                </div>
            </div>

            <flux:button type="submit" variant="primary" class="w-full" data-test="reset-password-button">
                {{ __('Reset password') }}
            </flux:button>
        </form>
    </div>
</x-layouts::auth>
