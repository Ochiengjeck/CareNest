@props([
    'title'   => 'Quick Actions',
    'actions' => [],
])

<flux:card class="!border-slate-200/80 !bg-white !shadow-sm dark:!border-white/[0.06] dark:!bg-white/[0.025] dark:!shadow-none rounded-xl">
    <flux:heading size="sm" class="mb-3 font-semibold tracking-tight text-slate-800 dark:text-zinc-100">{{ $title }}</flux:heading>
    <div class="mb-4 h-px bg-gradient-to-r from-slate-200 via-slate-100 to-transparent dark:from-white/[0.07] dark:via-white/[0.04]"></div>
    @if(count($actions) > 0)
        <div class="flex flex-wrap gap-2">
            @foreach($actions as $action)
                <flux:button
                    :href="$action['href'] ?? '#'"
                    :icon="$action['icon'] ?? null"
                    size="sm"
                    variant="ghost"
                    class="!border-slate-200 rounded-lg text-slate-700 hover:!bg-slate-50 dark:!border-white/[0.08] dark:text-zinc-300 dark:hover:!bg-white/[0.06]"
                >
                    {{ $action['label'] }}
                </flux:button>
            @endforeach
        </div>
    @else
        <x-dashboard.empty-state
            title="No actions available"
            icon="cursor-arrow-rays"
        />
    @endif
</flux:card>
