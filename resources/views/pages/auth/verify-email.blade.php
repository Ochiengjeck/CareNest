<x-layouts::auth>
    <div class="flex flex-col gap-6">
        {{-- Icon --}}
        <div class="mx-auto w-16 h-16 rounded-full bg-accent/10 flex items-center justify-center">
            <flux:icon.envelope class="size-8 text-accent" />
        </div>

        <x-auth-header
            :title="__('Check your email')"
            :description="__('We\'ve sent a verification link to your email address. Click the link to verify your account.')"
        />

        @if (session('status') == 'verification-link-sent')
            <div class="p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                <p class="text-sm text-center text-green-700 dark:text-green-300">
                    {{ __('A new verification link has been sent to your email address.') }}
                </p>
            </div>
        @endif

        <div class="flex flex-col gap-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <flux:button type="submit" variant="primary" class="w-full">
                    {{ __('Resend verification email') }}
                </flux:button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <flux:button variant="ghost" type="submit" class="w-full" data-test="logout-button">
                    {{ __('Sign out') }}
                </flux:button>
            </form>
        </div>
    </div>
</x-layouts::auth>
