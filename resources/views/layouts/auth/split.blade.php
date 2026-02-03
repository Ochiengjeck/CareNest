<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-zinc-900">
        <div class="relative grid min-h-dvh lg:grid-cols-2">
            {{-- Left Panel --}}
            <div class="relative hidden lg:flex flex-col overflow-hidden bg-gradient-to-br from-accent to-accent-content">
                {{-- Subtle Pattern Overlay --}}
                <div class="absolute inset-0 opacity-5">
                    <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <pattern id="dots" width="20" height="20" patternUnits="userSpaceOnUse">
                                <circle cx="2" cy="2" r="1" fill="white"/>
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#dots)" />
                    </svg>
                </div>

                {{-- Content --}}
                <div class="relative z-10 flex flex-col h-full p-10 text-white">
                    {{-- Top: Logo --}}
                    <a href="{{ route('home') }}" class="flex items-center gap-3" wire:navigate>
                        <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center">
                            @if(!empty($systemLogo))
                                <img src="{{ Storage::url($systemLogo) }}" alt="{{ $systemName ?? config('app.name') }}" class="h-7 w-auto object-contain" />
                            @else
                                <x-app-logo-icon class="w-7 h-7 text-accent" />
                            @endif
                        </div>
                        <span class="text-xl font-semibold">{{ $systemName ?? config('app.name', 'CareNest') }}</span>
                    </a>

                    {{-- Center: Main Content --}}
                    <div class="flex-1 flex flex-col justify-center max-w-md">
                        <h1 class="text-4xl font-bold leading-tight">
                            Care that feels<br>like family.
                        </h1>

                        <p class="mt-4 text-white/70 text-lg">
                            {{ $systemTagline ?? 'Professional, personalized care in a warm and supportive environment.' }}
                        </p>

                        {{-- Features --}}
                        <div class="mt-10 space-y-4">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
                                    <flux:icon.shield-check class="size-5" />
                                </div>
                                <div>
                                    <p class="font-medium">24/7 Professional Care</p>
                                    <p class="text-sm text-white/60">Trained staff available around the clock</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
                                    <flux:icon.heart class="size-5" />
                                </div>
                                <div>
                                    <p class="font-medium">Person-Centered Approach</p>
                                    <p class="text-sm text-white/60">Individualized care plans for every resident</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
                                    <flux:icon.home class="size-5" />
                                </div>
                                <div>
                                    <p class="font-medium">Home-Like Environment</p>
                                    <p class="text-sm text-white/60">Comfortable spaces designed for wellbeing</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Bottom: Stats --}}
                    <div class="flex items-center gap-8 pt-8 border-t border-white/10">
                        <div>
                            <p class="text-2xl font-bold">20+</p>
                            <p class="text-xs text-white/50 uppercase tracking-wide">Years Experience</p>
                        </div>
                        <div class="w-px h-10 bg-white/20"></div>
                        <div>
                            <p class="text-2xl font-bold">150+</p>
                            <p class="text-xs text-white/50 uppercase tracking-wide">Happy Residents</p>
                        </div>
                        <div class="w-px h-10 bg-white/20"></div>
                        <div>
                            <p class="text-2xl font-bold">98%</p>
                            <p class="text-xs text-white/50 uppercase tracking-wide">Satisfaction</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Panel - Form --}}
            <div class="flex flex-col justify-center px-6 py-12 lg:px-8 bg-white dark:bg-zinc-900">
                <div class="mx-auto w-full max-w-[400px]">
                    {{-- Mobile Logo --}}
                    <div class="lg:hidden flex flex-col items-center mb-8">
                        <a href="{{ route('home') }}" class="flex flex-col items-center gap-3" wire:navigate>
                            <span class="flex items-center justify-center w-14 h-14 bg-accent rounded-xl">
                                @if(!empty($systemLogo))
                                    <img src="{{ Storage::url($systemLogo) }}" alt="{{ $systemName ?? config('app.name') }}" class="h-8 w-auto object-contain brightness-0 invert" />
                                @else
                                    <x-app-logo-icon class="w-8 h-8 text-white" />
                                @endif
                            </span>
                            <span class="text-xl font-semibold text-zinc-900 dark:text-white">{{ $systemName ?? config('app.name', 'Laravel') }}</span>
                        </a>
                    </div>

                    {{-- Form Content --}}
                    {{ $slot }}

                    {{-- Trust Badge --}}
                    <div class="mt-8 pt-6 border-t border-zinc-200 dark:border-zinc-800">
                        <div class="flex items-center justify-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                            <flux:icon.lock-closed class="size-4" />
                            <span>Secure, encrypted connection</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
