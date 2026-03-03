<?php

use Livewire\Component;

new class extends Component {
    //
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <div class="max-w-2xl">
        <flux:card>
            <div class="mb-6 flex items-center gap-3">
                <div class="flex size-9 items-center justify-center rounded-lg bg-orange-50 dark:bg-orange-900/20">
                    <flux:icon.sun class="size-5 text-orange-500 dark:text-orange-400" />
                </div>
                <div>
                    <flux:heading size="lg">{{ __('Appearance') }}</flux:heading>
                    <flux:text size="sm" class="text-zinc-500">{{ __('Choose your preferred color theme') }}</flux:text>
                </div>
            </div>

            <div x-data class="space-y-3">
                <flux:radio.group variant="segmented" x-model="$flux.appearance" class="w-full">
                    <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
                    <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
                    <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
                </flux:radio.group>

                <flux:text size="sm" class="text-zinc-400">
                    {{ __('System will follow your operating system's light or dark mode setting.') }}
                </flux:text>
            </div>
        </flux:card>
    </div>
</section>
