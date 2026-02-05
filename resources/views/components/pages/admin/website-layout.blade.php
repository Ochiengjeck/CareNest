@props(['heading' => '', 'subheading' => ''])

<div class="self-stretch">
    <flux:heading>{{ $heading }}</flux:heading>
    @if($subheading)
        <flux:subheading>{{ $subheading }}</flux:subheading>
    @endif

    <div class="mt-5 w-full">
        {{ $slot }}
    </div>
</div>
