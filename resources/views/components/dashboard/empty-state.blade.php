@props([
    'title' => 'No data yet',
    'description' => null,
    'icon' => 'inbox',
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center py-12 text-center']) }}>
    <div class="rounded-full bg-zinc-100 p-3 dark:bg-zinc-800">
        <flux:icon :name="$icon" variant="outline" class="size-6 text-zinc-400" />
    </div>
    <flux:heading size="sm" class="mt-4">{{ $title }}</flux:heading>
    @if($description)
        <flux:text class="mt-1 text-sm text-zinc-500">{{ $description }}</flux:text>
    @endif
    @if($slot->isNotEmpty())
        <div class="mt-4">
            {{ $slot }}
        </div>
    @endif
</div>
