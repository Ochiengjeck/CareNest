@props([
    'title' => 'Quick Actions',
    'actions' => [],
])

<flux:card>
    <flux:heading size="sm" class="mb-4">{{ $title }}</flux:heading>
    @if(count($actions) > 0)
        <div class="flex flex-wrap gap-2">
            @foreach($actions as $action)
                <flux:button
                    :href="$action['href'] ?? '#'"
                    :icon="$action['icon'] ?? null"
                    size="sm"
                    variant="ghost"
                >
                    {{ $action['label'] }}
                </flux:button>
            @endforeach
        </div>
    @else
        <x-dashboard.empty-state
            title="No actions available"
            icon="cursor-arrow-rays"
        />
    @endif
</flux:card>
