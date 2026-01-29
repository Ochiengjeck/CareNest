@props([
    'title',
    'value' => '0',
    'icon' => null,
    'description' => null,
    'trend' => null,
    'trendUp' => true,
])

<flux:card class="space-y-2">
    <div class="flex items-center justify-between">
        <flux:subheading>{{ $title }}</flux:subheading>
        @if($icon)
            <flux:icon :name="$icon" variant="outline" class="size-5 text-zinc-400" />
        @endif
    </div>
    <flux:heading size="xl">{{ $value }}</flux:heading>
    @if($description)
        <flux:text class="text-sm text-zinc-500">{{ $description }}</flux:text>
    @endif
    @if($trend)
        <div class="flex items-center gap-1 text-sm {{ $trendUp ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
            <flux:icon :name="$trendUp ? 'arrow-trending-up' : 'arrow-trending-down'" variant="mini" class="size-4" />
            <span>{{ $trend }}</span>
        </div>
    @endif
</flux:card>
