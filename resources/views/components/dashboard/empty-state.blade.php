@props([
    'title'       => 'No data yet',
    'description' => null,
    'icon'        => 'inbox',
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center py-10 text-center']) }}>
    {{-- Icon with layered rings --}}
    <div class="relative">
        <div class="absolute inset-0 scale-150 rounded-full bg-slate-100/80 opacity-50 dark:bg-white/[0.06]"></div>
        <div class="absolute inset-0 scale-125 rounded-full bg-slate-100/60 opacity-50 dark:bg-white/[0.04]"></div>
        <div class="relative flex size-12 items-center justify-center rounded-full bg-slate-100 ring-1 ring-slate-200 dark:bg-white/[0.07] dark:ring-white/[0.08]">
            <flux:icon :name="$icon" variant="outline" class="size-6 text-slate-400 dark:text-zinc-400" />
        </div>
    </div>
    <flux:heading size="sm" class="mt-4 font-semibold text-slate-600 dark:text-zinc-300">{{ $title }}</flux:heading>
    @if($description)
        <flux:text class="mt-1 max-w-xs text-sm text-slate-400 dark:text-zinc-500">{{ $description }}</flux:text>
    @endif
    @if($slot->isNotEmpty())
        <div class="mt-4">
            {{ $slot }}
        </div>
    @endif
</div>
