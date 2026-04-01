@props([
    'title',
    'value'       => '0',
    'icon'        => null,
    'description' => null,
    'trend'       => null,
    'trendUp'     => true,
    'color'       => 'accent',   {{-- accent | emerald | amber | rose | sky | violet | cyan | pink | teal | orange --}}
])

@php
$iconClass = match($color) {
    'emerald' => 'si-emerald',
    'amber'   => 'si-amber',
    'rose'    => 'si-rose',
    'sky'     => 'si-sky',
    'violet'  => 'si-violet',
    'cyan'    => 'si-cyan',
    'pink'    => 'si-pink',
    'teal'    => 'si-teal',
    'orange'  => 'si-orange',
    default   => 'si-accent',
};
@endphp

<div {{ $attributes->merge(['class' => 'cn-stat-card group relative overflow-hidden rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm hover:bg-slate-50 dark:border-white/[0.06] dark:bg-white/[0.03] dark:shadow-none dark:hover:bg-white/[0.05]']) }}>

    {{-- Subtle top-edge glow line --}}
    <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-slate-300/40 to-transparent dark:via-white/10"></div>

    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
            <p class="truncate text-xs font-semibold uppercase tracking-widest text-slate-500 dark:text-zinc-400">
                {{ $title }}
            </p>
            <p class="mt-2 text-3xl font-bold leading-none tracking-tight text-slate-900 dark:text-white">
                {{ $value }}
            </p>

            @if($description)
                <p class="mt-1.5 text-xs text-slate-400 dark:text-zinc-500">{{ $description }}</p>
            @endif

            @if($trend)
                <div class="mt-2 flex items-center gap-1 text-xs font-medium {{ $trendUp ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                    <flux:icon
                        :name="$trendUp ? 'arrow-trending-up' : 'arrow-trending-down'"
                        variant="micro"
                        class="size-3 flex-shrink-0"
                    />
                    <span>{{ $trend }}</span>
                </div>
            @endif
        </div>

        @if($icon)
            <div class="si-icon-bg {{ $iconClass }} flex size-10 flex-shrink-0 items-center justify-center rounded-lg transition-transform duration-200 group-hover:scale-110">
                <flux:icon :name="$icon" variant="outline" class="size-5" />
            </div>
        @endif
    </div>
</div>
