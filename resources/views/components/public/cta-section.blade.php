@props([
    'title' => 'Ready to Learn More?',
    'description' => '',
    'primaryAction' => null,
    'secondaryAction' => null,
])

<section {{ $attributes->merge(['class' => 'relative py-16 lg:py-24 overflow-hidden']) }}>
    {{-- Background --}}
    <div class="absolute inset-0 bg-gradient-to-r from-accent via-accent-content to-accent"></div>
    <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=\"30\" height=\"30\" viewBox=\"0 0 30 30\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cpath d=\"M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z\" fill=\"rgba(255,255,255,0.07)\"%3E%3C/path%3E%3C/svg%3E')] opacity-50"></div>

    {{-- Content --}}
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto">
            <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">
                {{ $title }}
            </h2>
            @if($description)
                <p class="text-lg text-white/80 mb-8">
                    {{ $description }}
                </p>
            @endif

            @if($primaryAction || $secondaryAction)
                <div class="flex flex-wrap justify-center gap-4">
                    @if($primaryAction)
                        <a
                            href="{{ $primaryAction['href'] ?? '#' }}"
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-lg text-base font-semibold bg-white text-accent hover:bg-zinc-100 transition-colors shadow-lg"
                        >
                            {{ $primaryAction['label'] ?? 'Get Started' }}
                            <flux:icon.arrow-right variant="mini" class="size-5" />
                        </a>
                    @endif
                    @if($secondaryAction)
                        <a
                            href="{{ $secondaryAction['href'] ?? '#' }}"
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-lg text-base font-semibold text-white border-2 border-white/30 hover:bg-white/10 transition-colors"
                        >
                            {{ $secondaryAction['label'] ?? 'Learn More' }}
                        </a>
                    @endif
                </div>
            @endif

            {{ $slot }}
        </div>
    </div>
</section>
