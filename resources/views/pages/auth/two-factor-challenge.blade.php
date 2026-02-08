<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <div
            class="relative w-full h-auto"
            x-cloak
            x-data="{
                showRecoveryInput: @js($errors->has('recovery_code')),
                code: '',
                recovery_code: '',
                toggleInput() {
                    this.showRecoveryInput = !this.showRecoveryInput;

                    this.code = '';
                    this.recovery_code = '';

                    $dispatch('clear-2fa-auth-code');

                    $nextTick(() => {
                        this.showRecoveryInput
                            ? this.$refs.recovery_code?.focus()
                            : $dispatch('focus-2fa-auth-code');
                    });
                },
            }"
        >
            {{-- Icon --}}
            <div class="mx-auto w-16 h-16 rounded-full bg-accent/10 flex items-center justify-center mb-6">
                <flux:icon.device-phone-mobile class="size-8 text-accent" x-show="!showRecoveryInput" />
                <flux:icon.key class="size-8 text-accent" x-show="showRecoveryInput" />
            </div>

            <div x-show="!showRecoveryInput">
                <x-auth-header
                    :title="__('Two-factor authentication')"
                    :description="__('Enter the 6-digit code from your authenticator app')"
                    :eyebrow="__('Security check')"
                />
            </div>

            <div x-show="showRecoveryInput">
                <x-auth-header
                    :title="__('Use recovery code')"
                    :description="__('Enter one of your emergency recovery codes to access your account')"
                    :eyebrow="__('Security check')"
                />
            </div>

            <form method="POST" action="{{ route('two-factor.login.store') }}" class="mt-6">
                @csrf

                <div class="space-y-5">
                    <div x-show="!showRecoveryInput">
                        <div class="flex items-center justify-center">
                            <flux:otp
                                x-model="code"
                                length="6"
                                name="code"
                                label="OTP Code"
                                label:sr-only
                                class="mx-auto"
                             />
                        </div>
                        @error('code')
                            <p class="mt-2 text-sm text-center text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div x-show="showRecoveryInput">
                        <div>
                            <flux:label for="recovery_code">{{ __('Recovery code') }}</flux:label>
                            <div class="relative mt-1">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-400 dark:text-zinc-500">
                                    <flux:icon.key class="size-5" />
                                </div>
                                <flux:input
                                    id="recovery_code"
                                    type="text"
                                    name="recovery_code"
                                    x-ref="recovery_code"
                                    x-bind:required="showRecoveryInput"
                                    autocomplete="one-time-code"
                                    x-model="recovery_code"
                                    placeholder="XXXXX-XXXXX"
                                    class="!pl-10"
                                />
                            </div>
                        </div>
                        @error('recovery_code')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <flux:button
                        variant="primary"
                        type="submit"
                        class="w-full"
                    >
                        {{ __('Verify') }}
                    </flux:button>
                </div>

                <div class="mt-5 text-center">
                    <button
                        type="button"
                        @click="toggleInput()"
                        class="text-sm text-accent hover:underline"
                    >
                        <span x-show="!showRecoveryInput">{{ __('Use a recovery code instead') }}</span>
                        <span x-show="showRecoveryInput">{{ __('Use authenticator app instead') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::auth>
