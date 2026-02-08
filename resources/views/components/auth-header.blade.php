@props([
    'title',
    'description',
    'eyebrow' => null,
])

<div class="flex w-full flex-col gap-2 text-left">
    @if($eyebrow)
        <p class="text-xs uppercase tracking-[0.25em] text-zinc-500 dark:text-zinc-400">
            {{ $eyebrow }}
        </p>
    @endif
    <flux:heading size="xl" class="text-balance">{{ $title }}</flux:heading>
    <flux:subheading class="text-pretty">{{ $description }}</flux:subheading>
</div>
