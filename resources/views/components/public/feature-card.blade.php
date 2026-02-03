@props([
    'title' => '',
    'description' => '',
    'icon' => 'star',
])

<div {{ $attributes->merge(['class' => 'group relative p-6 rounded-2xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 hover:border-accent/50 hover:shadow-lg hover:shadow-accent/5 transition-all duration-300']) }}>
    {{-- Icon --}}
    <div class="w-12 h-12 rounded-xl bg-accent/10 flex items-center justify-center mb-4 group-hover:bg-accent/20 transition-colors">
        <flux:icon :name="$icon" class="size-6 text-accent" />
    </div>

    {{-- Content --}}
    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">
        {{ $title }}
    </h3>
    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
        {{ $description }}
    </p>

    {{ $slot }}
</div>
