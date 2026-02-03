@props([
    'name' => '',
    'role' => '',
    'image' => null,
    'bio' => '',
])

<div {{ $attributes->merge(['class' => 'text-center group']) }}>
    {{-- Image --}}
    <div class="relative w-40 h-40 mx-auto mb-4 rounded-2xl overflow-hidden bg-zinc-100 dark:bg-zinc-800">
        @if($image)
            <img src="{{ $image }}" alt="{{ $name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
        @else
            <div class="w-full h-full flex items-center justify-center">
                <flux:icon.user class="size-16 text-zinc-400 dark:text-zinc-600" />
            </div>
        @endif
    </div>

    {{-- Info --}}
    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $name }}</h3>
    <p class="text-sm text-accent font-medium">{{ $role }}</p>
    @if($bio)
        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">{{ $bio }}</p>
    @endif
</div>
