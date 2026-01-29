@props([
    'sidebar' => false,
])

@php
    $brandName = $systemName ?? config('app.name', 'CareNest');
    $logoPath = $systemLogo ?? null;
@endphp

@if($sidebar)
    <flux:sidebar.brand :name="$brandName" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            @if($logoPath)
                <img src="{{ Storage::url($logoPath) }}" class="size-5 object-contain" alt="" />
            @else
                <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
            @endif
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="$brandName" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            @if($logoPath)
                <img src="{{ Storage::url($logoPath) }}" class="size-5 object-contain" alt="" />
            @else
                <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
            @endif
        </x-slot>
    </flux:brand>
@endif
