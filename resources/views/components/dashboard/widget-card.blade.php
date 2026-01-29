@props([
    'title' => null,
    'icon' => null,
])

<flux:card {{ $attributes }}>
    @if($title)
        <div class="mb-4 flex items-center justify-between">
            <flux:heading size="sm">{{ $title }}</flux:heading>
            @if($icon)
                <flux:icon :name="$icon" variant="outline" class="size-5 text-zinc-400" />
            @endif
        </div>
    @endif
    {{ $slot }}
</flux:card>
