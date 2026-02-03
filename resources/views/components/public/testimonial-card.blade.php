@props([
    'quote' => '',
    'author' => '',
    'relation' => '',
    'image' => null,
])

<div {{ $attributes->merge(['class' => 'relative p-6 lg:p-8 rounded-2xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700']) }}>
    {{-- Quote Icon --}}
    <div class="absolute top-6 right-6 text-accent/20">
        <svg class="size-12" fill="currentColor" viewBox="0 0 24 24">
            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
        </svg>
    </div>

    {{-- Quote --}}
    <blockquote class="text-zinc-700 dark:text-zinc-300 text-lg leading-relaxed mb-6 relative z-10">
        "{{ $quote }}"
    </blockquote>

    {{-- Author --}}
    <div class="flex items-center gap-4">
        @if($image)
            <img src="{{ $image }}" alt="{{ $author }}" class="w-12 h-12 rounded-full object-cover">
        @else
            <div class="w-12 h-12 rounded-full bg-accent/10 flex items-center justify-center">
                <flux:icon.user class="size-6 text-accent" />
            </div>
        @endif
        <div>
            <p class="font-semibold text-zinc-900 dark:text-white">{{ $author }}</p>
            @if($relation)
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $relation }}</p>
            @endif
        </div>
    </div>
</div>
