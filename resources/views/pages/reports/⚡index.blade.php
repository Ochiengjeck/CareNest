<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Reports')]
class extends Component {
}; ?>

<flux:main>
    <div class="space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Reports') }}</flux:heading>
            <flux:subheading>{{ __('View data reports and generate AI-powered analysis') }}</flux:subheading>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            {{-- Resident Overview --}}
            <flux:card class="group transition-colors hover:border-blue-300 dark:hover:border-blue-700">
                <div class="flex items-start gap-4">
                    <div class="rounded-lg bg-blue-50 p-3 dark:bg-blue-900/20">
                        <flux:icon name="user-group" class="size-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div class="flex-1">
                        <flux:heading size="sm">{{ __('Resident Overview') }}</flux:heading>
                        <flux:subheading class="mt-1">{{ __('Census, demographics, risk levels, and admission trends') }}</flux:subheading>
                        <flux:button variant="ghost" size="sm" :href="route('reports.residents')" wire:navigate class="mt-3" icon-trailing="arrow-right">
                            {{ __('View Report') }}
                        </flux:button>
                    </div>
                </div>
            </flux:card>

            {{-- Clinical Summary --}}
            <flux:card class="group transition-colors hover:border-red-300 dark:hover:border-red-700">
                <div class="flex items-start gap-4">
                    <div class="rounded-lg bg-red-50 p-3 dark:bg-red-900/20">
                        <flux:icon name="heart" class="size-6 text-red-600 dark:text-red-400" />
                    </div>
                    <div class="flex-1">
                        <flux:heading size="sm">{{ __('Clinical Summary') }}</flux:heading>
                        <flux:subheading class="mt-1">{{ __('Medications, vitals, incidents, and compliance rates') }}</flux:subheading>
                        <flux:button variant="ghost" size="sm" :href="route('reports.clinical')" wire:navigate class="mt-3" icon-trailing="arrow-right">
                            {{ __('View Report') }}
                        </flux:button>
                    </div>
                </div>
            </flux:card>

            {{-- Staff Overview --}}
            <flux:card class="group transition-colors hover:border-amber-300 dark:hover:border-amber-700">
                <div class="flex items-start gap-4">
                    <div class="rounded-lg bg-amber-50 p-3 dark:bg-amber-900/20">
                        <flux:icon name="identification" class="size-6 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div class="flex-1">
                        <flux:heading size="sm">{{ __('Staff Overview') }}</flux:heading>
                        <flux:subheading class="mt-1">{{ __('Staffing levels, shift coverage, and qualifications') }}</flux:subheading>
                        <flux:button variant="ghost" size="sm" :href="route('reports.staff')" wire:navigate class="mt-3" icon-trailing="arrow-right">
                            {{ __('View Report') }}
                        </flux:button>
                    </div>
                </div>
            </flux:card>

            {{-- Audit Logs --}}
            <flux:card class="group transition-colors hover:border-green-300 dark:hover:border-green-700">
                <div class="flex items-start gap-4">
                    <div class="rounded-lg bg-green-50 p-3 dark:bg-green-900/20">
                        <flux:icon name="document-text" class="size-6 text-green-600 dark:text-green-400" />
                    </div>
                    <div class="flex-1">
                        <flux:heading size="sm">{{ __('Audit Logs') }}</flux:heading>
                        <flux:subheading class="mt-1">{{ __('System activity report with filtering and export options') }}</flux:subheading>
                        <flux:button variant="ghost" size="sm" :href="route('reports.audit-logs')" wire:navigate class="mt-3" icon-trailing="arrow-right">
                            {{ __('View Report') }}
                        </flux:button>
                    </div>
                </div>
            </flux:card>

            {{-- AI Report Generator --}}
            <flux:card class="group transition-colors hover:border-purple-300 dark:hover:border-purple-700">
                <div class="flex items-start gap-4">
                    <div class="rounded-lg bg-purple-50 p-3 dark:bg-purple-900/20">
                        <flux:icon name="cpu-chip" class="size-6 text-purple-600 dark:text-purple-400" />
                    </div>
                    <div class="flex-1">
                        <flux:heading size="sm">{{ __('AI Report Generator') }}</flux:heading>
                        <flux:subheading class="mt-1">{{ __('Generate custom AI-powered reports and analysis') }}</flux:subheading>
                        <flux:button variant="ghost" size="sm" :href="route('reports.ai-generate')" wire:navigate class="mt-3" icon-trailing="arrow-right">
                            {{ __('Generate Report') }}
                        </flux:button>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>
</flux:main>
