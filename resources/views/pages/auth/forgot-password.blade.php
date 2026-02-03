<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Forgot password?')" :description="__('No worries, we\'ll send you reset instructions')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-5">
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
                        type="email"
                        required
                        autofocus
                        placeholder="you@example.com"
                        class="!pl-10"
                    />
                </div>
            </div>

            <flux:button variant="primary" type="submit" class="w-full" data-test="email-password-reset-link-button">
                {{ __('Send reset link') }}
            </flux:button>
        </form>

        <div class="text-center text-sm text-zinc-600 dark:text-zinc-400">
            <flux:link :href="route('login')" wire:navigate class="inline-flex items-center gap-1">
                <flux:icon.arrow-left class="size-4" />
                {{ __('Back to sign in') }}
            </flux:link>
        </div>
    </div>
</x-layouts::auth>
