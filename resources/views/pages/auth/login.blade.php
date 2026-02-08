<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Welcome back')"
            :description="__('Enter your credentials to access your account')"
            :eyebrow="__('Member access')"
        />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
            @csrf

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
                        autofocus
                        autocomplete="email"
                        placeholder="you@example.com"
                        class="!pl-10"
                    />
                </div>
            </div>

            <!-- Password -->
            <div>
                <div class="flex items-center justify-between">
                    <flux:label for="password">{{ __('Password') }}</flux:label>
                    @if (Route::has('password.request'))
                        <flux:link class="text-sm" :href="route('password.request')" wire:navigate>
                            {{ __('Forgot password?') }}
                        </flux:link>
                    @endif
                </div>
                <div class="relative mt-1">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-400 dark:text-zinc-500 z-10">
                        <flux:icon.lock-closed class="size-5" />
                    </div>
                    <flux:input
                        id="password"
                        name="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        :placeholder="__('Enter your password')"
                        viewable
                        class="!pl-10"
                    />
                </div>
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="__('Remember me for 30 days')" :checked="old('remember')" />

            <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                {{ __('Sign in') }}
            </flux:button>
        </form>

        @if (Route::has('register'))
            <div class="text-center text-sm text-zinc-600 dark:text-zinc-400">
                <span>{{ __('New to CareNest?') }}</span>
                <flux:link :href="route('register')" wire:navigate class="font-medium">{{ __('Create an account') }}</flux:link>
            </div>
        @endif
    </div>
</x-layouts::auth>
