@props(['heading' => '', 'subheading' => ''])

<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <flux:navlist.group :heading="__('User Management')">
                <flux:navlist.item :href="route('admin.users.index')" wire:navigate
                    :current="request()->routeIs('admin.users.*')">
                    {{ __('Users') }}
                </flux:navlist.item>
            </flux:navlist.group>
            @can('manage-roles')
            <flux:navlist.group :heading="__('Role Management')">
                <flux:navlist.item :href="route('admin.roles.index')" wire:navigate
                    :current="request()->routeIs('admin.roles.*')">
                    {{ __('Roles & Permissions') }}
                </flux:navlist.item>
            </flux:navlist.group>
            @endcan
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
