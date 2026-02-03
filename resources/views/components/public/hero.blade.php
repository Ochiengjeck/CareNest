@props([
    'title' => '',
    'subtitle' => '',
    'description' => '',
    'primaryAction' => null,
    'secondaryAction' => null,
    'backgroundImage' => null,
    'overlay' => true,
    'size' => 'large', // small, medium, large
])

@php
    $heightClasses = match($size) {
        'small' => 'py-16 lg:py-24',
        'medium' => 'py-24 lg:py-32',
        'large' => 'min-h-[85vh] py-24 lg:py-32',
        default => 'min-h-[85vh] py-24 lg:py-32',
    };
@endphp

<section class="relative {{ $heightClasses }} flex items-center overflow-hidden">
    {{-- Background --}}
    @if($backgroundImage)
        <div class="absolute inset-0 z-0">
            <img src="{{ $backgroundImage }}" alt="" class="w-full h-full object-cover">
            @if($overlay)
                <div class="absolute inset-0 bg-gradient-to-r from-zinc-900/90 via-zinc-900/70 to-zinc-900/50"></div>
            @endif
        </div>
    @else
        {{-- Default gradient background --}}
        <div class="absolute inset-0 z-0 bg-gradient-to-br from-zinc-900 via-zinc-800 to-zinc-900">
            {{-- Decorative elements --}}
            <div class="absolute inset-0 opacity-30">
                <div class="absolute top-0 -left-4 w-72 h-72 bg-accent rounded-full mix-blend-multiply filter blur-3xl animate-blob"></div>
                <div class="absolute top-0 -right-4 w-72 h-72 bg-accent/60 rounded-full mix-blend-multiply filter blur-3xl animate-blob animation-delay-2000"></div>
                <div class="absolute -bottom-8 left-20 w-72 h-72 bg-accent/40 rounded-full mix-blend-multiply filter blur-3xl animate-blob animation-delay-4000"></div>
            </div>
        </div>
    @endif

    {{-- Content --}}
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
        <div class="max-w-3xl">
            @if($subtitle)
                <p class="text-accent font-semibold uppercase tracking-wider mb-4">
                    {{ $subtitle }}
                </p>
            @endif

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white leading-tight">
                {{ $title }}
            </h1>

            @if($description)
                <p class="mt-6 text-lg sm:text-xl text-zinc-300 leading-relaxed max-w-2xl">
                    {{ $description }}
                </p>
            @endif

            @if($primaryAction || $secondaryAction)
                <div class="mt-8 flex flex-wrap gap-4">
                    @if($primaryAction)
                        <a
                            href="{{ $primaryAction['href'] ?? '#' }}"
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-lg text-base font-semibold text-white bg-accent hover:bg-accent-content transition-all transform hover:scale-105 shadow-lg shadow-accent/25"
                        >
                            {{ $primaryAction['label'] ?? 'Learn More' }}
                            <flux:icon.arrow-right variant="mini" class="size-5" />
                        </a>
                    @endif
                    @if($secondaryAction)
                        <a
                            href="{{ $secondaryAction['href'] ?? '#' }}"
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-lg text-base font-semibold text-white border-2 border-white/30 hover:bg-white/10 transition-colors"
                        >
                            {{ $secondaryAction['label'] ?? 'Contact Us' }}
                        </a>
                    @endif
                </div>
            @endif

            {{ $slot }}
        </div>
    </div>
</section>

<style>
    @keyframes blob {
        0% { transform: translate(0px, 0px) scale(1); }
        33% { transform: translate(30px, -50px) scale(1.1); }
        66% { transform: translate(-20px, 20px) scale(0.9); }
        100% { transform: translate(0px, 0px) scale(1); }
    }
    .animate-blob {
        animation: blob 7s infinite;
    }
    .animation-delay-2000 {
        animation-delay: 2s;
    }
    .animation-delay-4000 {
        animation-delay: 4s;
    }
</style>
