<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Create an account')"
            :description="__('Enter your details to get started')"
            :eyebrow="__('New member')"
        />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-5">
            @csrf

            <!-- Name -->
            <div>
                <flux:label for="name">{{ __('Full name') }}</flux:label>
                <div class="relative mt-1">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-400 dark:text-zinc-500">
                        <flux:icon.user class="size-5" />
                    </div>
                    <flux:input
                        id="name"
                        name="name"
                        :value="old('name')"
                        type="text"
                        required
                        autofocus
                        autocomplete="name"
                        :placeholder="__('John Doe')"
                        class="!pl-10"
                    />
                </div>
            </div>

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
                        :value="old('email')"
                        type="email"
                        required
                        autocomplete="email"
                        placeholder="you@example.com"
                        class="!pl-10"
                    />
                </div>
            </div>

            <!-- Password -->
            <div>
                <flux:label for="password">{{ __('Password') }}</flux:label>
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
                        :placeholder="__('Create a password')"
                        viewable
                        class="!pl-10"
                    />
                </div>
            </div>

            <!-- Confirm Password -->
            <div>
                <flux:label for="password_confirmation">{{ __('Confirm password') }}</flux:label>
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
                        :placeholder="__('Confirm your password')"
                        viewable
                        class="!pl-10"
                    />
                </div>
            </div>

            <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                {{ __('Create account') }}
            </flux:button>
        </form>

        <div class="text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate class="font-medium">{{ __('Sign in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
