@props([
    'title' => '',
    'subtitle' => '',
    'centered' => true,
    'light' => false,
])

<div {{ $attributes->merge(['class' => $centered ? 'text-center' : '']) }}>
    @if($subtitle)
        <p class="text-sm font-semibold uppercase tracking-wider {{ $light ? 'text-white/70' : 'text-accent' }} mb-2">
            {{ $subtitle }}
        </p>
    @endif
    <h2 class="text-3xl sm:text-4xl font-bold {{ $light ? 'text-white' : 'text-zinc-900 dark:text-white' }}">
        {{ $title }}
    </h2>
    @if($slot->isNotEmpty())
        <p class="mt-4 text-lg {{ $light ? 'text-white/80' : 'text-zinc-600 dark:text-zinc-400' }} max-w-2xl {{ $centered ? 'mx-auto' : '' }}">
            {{ $slot }}
        </p>
    @endif
</div>
