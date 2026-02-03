<x-layouts::auth>
    <div class="flex flex-col gap-6">
        {{-- Icon --}}
        <div class="mx-auto w-16 h-16 rounded-full bg-accent/10 flex items-center justify-center">
            <flux:icon.shield-check class="size-8 text-accent" />
        </div>

        <x-auth-header
            :title="__('Confirm your password')"
            :description="__('This is a secure area. Please confirm your password to continue.')"
        />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-5">
            @csrf

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
                        autocomplete="current-password"
                        :placeholder="__('Enter your password')"
                        viewable
                        class="!pl-10"
                    />
                </div>
            </div>

            <flux:button variant="primary" type="submit" class="w-full" data-test="confirm-password-button">
                {{ __('Confirm') }}
            </flux:button>
        </form>
    </div>
</x-layouts::auth>
