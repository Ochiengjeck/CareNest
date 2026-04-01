@props([
    'title' => null,
    'icon'  => null,
])

<flux:card {{ $attributes->merge(['class' => 'cn-widget-card !border-slate-200/80 !bg-white !shadow-sm dark:!border-white/[0.06] dark:!bg-white/[0.025] dark:!shadow-none rounded-xl']) }}>
    @if($title)
        <div class="mb-3 flex items-center justify-between">
            <flux:heading size="sm" class="font-semibold tracking-tight text-slate-800 dark:text-zinc-100">{{ $title }}</flux:heading>
            @if($icon)
                <div class="flex size-7 items-center justify-center rounded-lg bg-slate-100 dark:bg-white/[0.05]">
                    <flux:icon :name="$icon" variant="outline" class="size-4 text-slate-500 dark:text-zinc-400" />
                </div>
            @endif
        </div>
        <div class="mb-4 h-px bg-gradient-to-r from-slate-200 via-slate-100 to-transparent dark:from-white/[0.07] dark:via-white/[0.04]"></div>
    @endif
    {{ $slot }}
</flux:card>
