@props(['heading' => '', 'subheading' => ''])

<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <flux:navlist.group :heading="__('System Settings')">
                <flux:navlist.item :href="route('admin.settings.general')" wire:navigate
                    :current="request()->routeIs('admin.settings.general')">
                    {{ __('General') }}
                </flux:navlist.item>
                <flux:navlist.item :href="route('admin.settings.ai')" wire:navigate
                    :current="request()->routeIs('admin.settings.ai')">
                    {{ __('AI Integration') }}
                </flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading>{{ $heading }}</flux:heading>
        @if($subheading)
            <flux:subheading>{{ $subheading }}</flux:subheading>
        @endif

        <div class="mt-5 w-full">
            {{ $slot }}
        </div>
    </div>
</div>
