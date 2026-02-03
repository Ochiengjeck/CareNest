<?php

use App\Models\GalleryImage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.public')]
#[Title('Gallery')]
class extends Component {
    public string $activeFilter = 'all';

    #[Computed]
    public function galleryImages()
    {
        return GalleryImage::active()->ordered()->get();
    }

    #[Computed]
    public function categories(): array
    {
        return [
            'all' => 'All Photos',
            'rooms' => 'Rooms',
            'common' => 'Common Areas',
            'activities' => 'Activities',
            'gardens' => 'Gardens',
        ];
    }

    public function setFilter(string $filter): void
    {
        $this->activeFilter = $filter;
    }
};

?>

<div>
    {{-- Hero Section --}}
    <x-public.hero
        size="small"
        title="Our Gallery"
        subtitle="Take a Look"
        description="Explore our comfortable facilities, beautiful grounds, and the vibrant community life at our care home."
    />

    {{-- Gallery Section --}}
    <section class="py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Filter Tabs --}}
            <div class="flex flex-wrap justify-center gap-2 mb-12">
                @foreach($this->categories as $key => $label)
                    <button
                        wire:click="setFilter('{{ $key }}')"
                        type="button"
                        class="px-5 py-2.5 rounded-full text-sm font-medium transition-colors
                            {{ $activeFilter === $key
                                ? 'bg-accent text-white'
                                : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700'
                            }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            {{-- Gallery Grid --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @forelse($this->galleryImages as $image)
                    <div
                        class="{{ $activeFilter !== 'all' && $activeFilter !== $image->category ? 'hidden' : '' }}"
                        wire:key="gallery-{{ $image->id }}"
                    >
                        <div class="group relative cursor-pointer">
                            <div class="aspect-[4/3] rounded-xl overflow-hidden bg-zinc-200 dark:bg-zinc-700 group-hover:ring-2 ring-accent ring-offset-2 dark:ring-offset-zinc-900 transition-all">
                                <img
                                    src="{{ Storage::url($image->image_path) }}"
                                    alt="{{ $image->alt_text ?? $image->title }}"
                                    class="w-full h-full object-cover"
                                >
                            </div>
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors rounded-xl flex items-center justify-center">
                                <flux:icon.magnifying-glass-plus class="size-8 text-white opacity-0 group-hover:opacity-100 transition-opacity" />
                            </div>
                            <p class="mt-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 truncate">{{ $image->title }}</p>
                        </div>
                    </div>
                @empty
                    {{-- Fallback placeholder gallery --}}
                    @php
                        $placeholderItems = [
                            ['category' => 'rooms', 'title' => 'Private Bedroom', 'icon' => 'home'],
                            ['category' => 'rooms', 'title' => 'Bedroom Suite', 'icon' => 'home'],
                            ['category' => 'rooms', 'title' => 'Bathroom', 'icon' => 'home'],
                            ['category' => 'common', 'title' => 'Living Room', 'icon' => 'tv'],
                            ['category' => 'common', 'title' => 'Dining Hall', 'icon' => 'cake'],
                            ['category' => 'common', 'title' => 'Library Corner', 'icon' => 'book-open'],
                            ['category' => 'activities', 'title' => 'Art Class', 'icon' => 'paint-brush'],
                            ['category' => 'activities', 'title' => 'Music Session', 'icon' => 'musical-note'],
                            ['category' => 'gardens', 'title' => 'Main Garden', 'icon' => 'sun'],
                            ['category' => 'gardens', 'title' => 'Courtyard', 'icon' => 'sun'],
                        ];
                    @endphp

                    @foreach($placeholderItems as $index => $item)
                        <div
                            class="{{ $activeFilter !== 'all' && $activeFilter !== $item['category'] ? 'hidden' : '' }}"
                            wire:key="gallery-placeholder-{{ $index }}"
                        >
                            <div class="group relative cursor-pointer">
                                <x-public.image-placeholder
                                    aspect="4/3"
                                    :text="$item['title']"
                                    :icon="$item['icon']"
                                    class="group-hover:ring-2 ring-accent ring-offset-2 dark:ring-offset-zinc-900 transition-all"
                                />
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors rounded-xl flex items-center justify-center">
                                    <flux:icon.magnifying-glass-plus class="size-8 text-white opacity-0 group-hover:opacity-100 transition-opacity" />
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endforelse
            </div>

            @if($this->galleryImages->isEmpty())
                {{-- Placeholder Notice --}}
                <div class="mt-12 p-6 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 text-center">
                    <flux:icon.photo class="size-10 text-zinc-400 mx-auto mb-3" />
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">Gallery Coming Soon</h3>
                    <p class="text-zinc-600 dark:text-zinc-400 max-w-md mx-auto">
                        We're currently preparing our photo gallery. In the meantime, we'd love to show you around in person.
                    </p>
                    <a
                        href="{{ route('contact') }}"
                        class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 rounded-lg text-sm font-medium text-white bg-accent hover:bg-accent-content transition-colors"
                    >
                        Schedule a Tour
                        <flux:icon.arrow-right variant="mini" class="size-4" />
                    </a>
                </div>
            @endif
        </div>
    </section>

    {{-- Virtual Tour Section --}}
    <section class="py-20 lg:py-28 bg-zinc-50 dark:bg-zinc-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center">
                <div class="w-16 h-16 rounded-2xl bg-accent/10 flex items-center justify-center mx-auto mb-6">
                    <flux:icon.video-camera class="size-8 text-accent" />
                </div>
                <h2 class="text-3xl font-bold text-zinc-900 dark:text-white mb-4">Virtual Tour Coming Soon</h2>
                <p class="text-zinc-600 dark:text-zinc-400 mb-8">
                    We're working on a virtual tour experience so you can explore our facilities from the comfort of your home. Want to be notified when it's ready?
                </p>
                <a
                    href="{{ route('contact') }}"
                    class="inline-flex items-center gap-2 px-6 py-3 rounded-lg text-base font-medium border-2 border-accent text-accent hover:bg-accent hover:text-white transition-colors"
                >
                    Contact Us for Updates
                </a>
            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <x-public.cta-section
        title="See It In Person"
        description="Photos can only show so much. Come visit us and experience the warmth and care firsthand."
        :primaryAction="['label' => 'Schedule a Visit', 'href' => route('contact')]"
        :secondaryAction="['label' => 'Get Directions', 'href' => route('contact')]"
    />
</div>
