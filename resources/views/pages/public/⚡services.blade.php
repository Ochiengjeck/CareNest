<?php

use App\Models\Amenity;
use App\Models\DailySchedule;
use App\Models\Service;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.public')]
#[Title('Our Services')]
class extends Component {
    #[Computed]
    public function services()
    {
        return Service::active()->ordered()->get();
    }

    #[Computed]
    public function amenities()
    {
        return Amenity::active()->ordered()->get();
    }

    #[Computed]
    public function schedules()
    {
        return DailySchedule::active()->ordered()->get();
    }
};

?>

<div>
    {{-- Hero Section --}}
    <x-public.hero
        size="small"
        title="Our Services"
        subtitle="Care Options"
        description="Comprehensive care services tailored to meet the unique needs of each resident. From daily assistance to specialized memory care, we're here to help."
    />

    {{-- Main Services --}}
    <section class="py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-public.section-heading
                title="Care Services"
                subtitle="What We Provide"
            >
                Every resident receives personalized care designed around their individual needs and preferences.
            </x-public.section-heading>

            <div class="mt-16 space-y-16">
                @forelse($this->services as $index => $service)
                    <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-center">
                        <div class="{{ $index % 2 === 0 ? 'lg:order-1' : 'lg:order-2' }}">
                            @if($service->image_path)
                                <img
                                    src="{{ Storage::url($service->image_path) }}"
                                    alt="{{ $service->title }}"
                                    class="w-full aspect-[16/10] object-cover rounded-2xl"
                                />
                            @else
                                <x-public.image-placeholder aspect="16/10" :text="$service->title" :icon="$service->icon" />
                            @endif
                        </div>
                        <div class="{{ $index % 2 === 0 ? 'lg:order-2' : 'lg:order-1' }}">
                            <div class="w-12 h-12 rounded-xl bg-accent/10 flex items-center justify-center mb-4">
                                <flux:icon :name="$service->icon" class="size-6 text-accent" />
                            </div>
                            <h3 class="text-2xl font-bold text-zinc-900 dark:text-white mb-4">{{ $service->title }}</h3>
                            <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed mb-4">
                                {{ $service->description }}
                            </p>
                            @if($service->features && count($service->features) > 0)
                                <ul class="space-y-2 text-zinc-600 dark:text-zinc-400">
                                    @foreach($service->features as $feature)
                                        <li class="flex items-center gap-2">
                                            <flux:icon.check-circle variant="mini" class="size-5 text-accent shrink-0" />
                                            {{ $feature }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                @empty
                    {{-- Fallback: Show default services when none exist --}}
                    {{-- Residential Care --}}
                    <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-center">
                        <div class="lg:order-1">
                            <x-public.image-placeholder aspect="16/10" text="Residential Care" icon="home" />
                        </div>
                        <div class="lg:order-2">
                            <div class="w-12 h-12 rounded-xl bg-accent/10 flex items-center justify-center mb-4">
                                <flux:icon.home class="size-6 text-accent" />
                            </div>
                            <h3 class="text-2xl font-bold text-zinc-900 dark:text-white mb-4">Residential Care</h3>
                            <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed mb-4">
                                Our residential care provides a safe, comfortable home for seniors who need assistance with daily living activities.
                            </p>
                            <ul class="space-y-2 text-zinc-600 dark:text-zinc-400">
                                <li class="flex items-center gap-2">
                                    <flux:icon.check-circle variant="mini" class="size-5 text-accent" />
                                    24/7 nursing and caregiver support
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon.check-circle variant="mini" class="size-5 text-accent" />
                                    Medication management
                                </li>
                            </ul>
                        </div>
                    </div>

                    <p class="text-center text-sm text-zinc-500 dark:text-zinc-500 italic">
                        * Service information is placeholder content
                    </p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Amenities --}}
    <section class="py-20 lg:py-28 bg-zinc-50 dark:bg-zinc-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-public.section-heading
                title="Amenities & Facilities"
                subtitle="Our Environment"
            >
                A comfortable, well-equipped environment designed to feel like home.
            </x-public.section-heading>

            <div class="mt-12 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @forelse($this->amenities as $amenity)
                    <div class="p-6 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-center">
                        <flux:icon :name="$amenity->icon" class="size-8 text-accent mx-auto mb-3" />
                        <h4 class="font-semibold text-zinc-900 dark:text-white">{{ $amenity->title }}</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ $amenity->description }}</p>
                    </div>
                @empty
                    {{-- Fallback amenities --}}
                    <div class="p-6 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-center">
                        <flux:icon.home-modern class="size-8 text-accent mx-auto mb-3" />
                        <h4 class="font-semibold text-zinc-900 dark:text-white">Private Rooms</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">Comfortable, well-appointed private accommodations</p>
                    </div>
                    <div class="p-6 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-center">
                        <flux:icon.sun class="size-8 text-accent mx-auto mb-3" />
                        <h4 class="font-semibold text-zinc-900 dark:text-white">Garden Areas</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">Beautiful outdoor spaces for relaxation</p>
                    </div>
                    <div class="p-6 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-center">
                        <flux:icon.cake class="size-8 text-accent mx-auto mb-3" />
                        <h4 class="font-semibold text-zinc-900 dark:text-white">Dining Room</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">Restaurant-style dining with nutritious meals</p>
                    </div>
                    <div class="p-6 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-center">
                        <flux:icon.tv class="size-8 text-accent mx-auto mb-3" />
                        <h4 class="font-semibold text-zinc-900 dark:text-white">Lounge Areas</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">Comfortable common spaces for socializing</p>
                    </div>
                    <div class="p-6 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-center">
                        <flux:icon.puzzle-piece class="size-8 text-accent mx-auto mb-3" />
                        <h4 class="font-semibold text-zinc-900 dark:text-white">Activity Rooms</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">Dedicated spaces for arts, games, and programs</p>
                    </div>
                    <div class="p-6 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-center">
                        <flux:icon.book-open class="size-8 text-accent mx-auto mb-3" />
                        <h4 class="font-semibold text-zinc-900 dark:text-white">Library</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">Quiet reading room with books and magazines</p>
                    </div>
                    <div class="p-6 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-center">
                        <flux:icon.musical-note class="size-8 text-accent mx-auto mb-3" />
                        <h4 class="font-semibold text-zinc-900 dark:text-white">Chapel</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">Non-denominational space for spiritual reflection</p>
                    </div>
                    <div class="p-6 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-center">
                        <flux:icon.wifi class="size-8 text-accent mx-auto mb-3" />
                        <h4 class="font-semibold text-zinc-900 dark:text-white">WiFi Access</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">Stay connected with family and friends</p>
                    </div>
                @endforelse
            </div>

            @if($this->amenities->isEmpty())
                <p class="mt-8 text-center text-sm text-zinc-500 dark:text-zinc-500 italic">
                    * Amenities information is placeholder content
                </p>
            @endif
        </div>
    </section>

    {{-- Daily Schedule --}}
    <section class="py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-public.section-heading
                title="A Day at {{ system_setting('system_name', 'CareNest') }}"
                subtitle="Daily Life"
            >
                A glimpse into the structured yet flexible daily routine our residents enjoy.
            </x-public.section-heading>

            <div class="mt-12 max-w-3xl mx-auto">
                <div class="space-y-4">
                    @forelse($this->schedules as $item)
                        <div class="flex items-start gap-4 p-4 rounded-xl hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <div class="w-24 shrink-0 text-sm font-semibold text-accent">
                                {{ $item->time }}
                            </div>
                            <div class="text-zinc-700 dark:text-zinc-300">
                                {{ $item->activity }}
                            </div>
                        </div>
                    @empty
                        {{-- Fallback schedule --}}
                        @php
                            $defaultSchedule = [
                                ['time' => '7:00 AM', 'activity' => 'Morning wake-up and personal care assistance'],
                                ['time' => '8:00 AM', 'activity' => 'Breakfast in the dining room'],
                                ['time' => '9:30 AM', 'activity' => 'Morning exercises and stretching'],
                                ['time' => '10:30 AM', 'activity' => 'Group activities (arts, music, games)'],
                                ['time' => '12:00 PM', 'activity' => 'Lunch and social time'],
                                ['time' => '2:00 PM', 'activity' => 'Afternoon programs or rest time'],
                                ['time' => '3:30 PM', 'activity' => 'Tea time and snacks'],
                                ['time' => '4:30 PM', 'activity' => 'Garden walks or outdoor activities'],
                                ['time' => '6:00 PM', 'activity' => 'Dinner service'],
                                ['time' => '7:30 PM', 'activity' => 'Evening entertainment or relaxation'],
                                ['time' => '9:00 PM', 'activity' => 'Evening care and bedtime preparation'],
                            ];
                        @endphp

                        @foreach($defaultSchedule as $item)
                            <div class="flex items-start gap-4 p-4 rounded-xl hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                <div class="w-24 shrink-0 text-sm font-semibold text-accent">
                                    {{ $item['time'] }}
                                </div>
                                <div class="text-zinc-700 dark:text-zinc-300">
                                    {{ $item['activity'] }}
                                </div>
                            </div>
                        @endforeach
                    @endforelse
                </div>

                <p class="mt-6 text-sm text-zinc-500 dark:text-zinc-500 text-center italic">
                    * Schedule is flexible and adjusted based on individual preferences and needs
                </p>
            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <x-public.cta-section
        title="Interested in Our Services?"
        description="Contact us to discuss which care option is right for your loved one. We're here to help you make the best decision."
        :primaryAction="['label' => 'Schedule a Tour', 'href' => route('contact')]"
        :secondaryAction="['label' => 'Call Us', 'href' => 'tel:' . system_setting('contact_phone', '')]"
    />
</div>
