@props([
    'value' => '0',
    'label' => '',
    'suffix' => '',
    'prefix' => '',
])

<div {{ $attributes->merge(['class' => 'text-center']) }}>
    <div class="text-4xl sm:text-5xl font-bold text-accent">
        {{ $prefix }}<span>{{ $value }}</span>{{ $suffix }}
    </div>
    <p class="mt-2 text-sm font-medium text-zinc-600 dark:text-zinc-400 uppercase tracking-wider">
        {{ $label }}
    </p>
</div>
