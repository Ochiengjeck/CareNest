@props(['heading' => '', 'subheading' => ''])

<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <flux:navlist.group :heading="__('Website Content')">
                <flux:navlist.item :href="route('admin.website.settings')" wire:navigate
                    :current="request()->routeIs('admin.website.settings')">
                    {{ __('Content Settings') }}
                </flux:navlist.item>
                <flux:navlist.item :href="route('admin.website.testimonials')" wire:navigate
                    :current="request()->routeIs('admin.website.testimonials')">
                    {{ __('Testimonials') }}
                </flux:navlist.item>
                <flux:navlist.item :href="route('admin.website.team')" wire:navigate
                    :current="request()->routeIs('admin.website.team')">
                    {{ __('Team Members') }}
                </flux:navlist.item>
                <flux:navlist.item :href="route('admin.website.faq')" wire:navigate
                    :current="request()->routeIs('admin.website.faq')">
                    {{ __('FAQ Items') }}
                </flux:navlist.item>
                <flux:navlist.item :href="route('admin.website.gallery')" wire:navigate
                    :current="request()->routeIs('admin.website.gallery')">
                    {{ __('Gallery') }}
                </flux:navlist.item>
                <flux:navlist.item :href="route('admin.website.services')" wire:navigate
                    :current="request()->routeIs('admin.website.services')">
                    {{ __('Services') }}
                </flux:navlist.item>
                <flux:navlist.item :href="route('admin.website.contact-submissions')" wire:navigate
                    :current="request()->routeIs('admin.website.contact-submissions')">
                    {{ __('Contact Submissions') }}
                    @php
                        $newCount = \App\Models\ContactSubmission::new()->count();
                    @endphp
                    @if($newCount > 0)
                        <flux:badge color="amber" size="sm" class="ml-auto">{{ $newCount }}</flux:badge>
                    @endif
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
