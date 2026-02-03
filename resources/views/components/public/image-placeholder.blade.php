@props([
    'aspect' => '16/9',
    'text' => 'Image Coming Soon',
    'icon' => 'photo',
])

<div
    {{ $attributes->merge(['class' => 'relative bg-zinc-100 dark:bg-zinc-800 rounded-xl overflow-hidden']) }}
    style="aspect-ratio: {{ $aspect }};"
>
    {{-- Pattern Background --}}
    <div class="absolute inset-0 opacity-50">
        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="placeholder-pattern-{{ Str::random(8) }}" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                    <circle cx="1" cy="1" r="1" class="fill-zinc-300 dark:fill-zinc-700" />
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#placeholder-pattern-{{ Str::random(8) }})" />
        </svg>
    </div>

    {{-- Content --}}
    <div class="absolute inset-0 flex flex-col items-center justify-center">
        <flux:icon :name="$icon" class="size-12 text-zinc-400 dark:text-zinc-600 mb-2" />
        <span class="text-sm text-zinc-500 dark:text-zinc-500">{{ $text }}</span>
    </div>
</div>
