<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="min-h-full">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen antialiased text-zinc-900 dark:text-zinc-100">
        <div class="auth-page relative grid min-h-dvh lg:grid-cols-2">
            {{-- Left Panel --}}
            <div class="relative hidden lg:flex flex-col overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-950"></div>
                <div class="absolute inset-0 bg-[radial-gradient(600px_circle_at_15%_15%,rgba(255,255,255,0.14),transparent_60%)]"></div>
                <div class="absolute -right-24 top-10 h-72 w-72 rounded-full bg-emerald-400/20 blur-3xl"></div>
                <div class="absolute -left-24 bottom-0 h-80 w-80 rounded-full bg-sky-400/20 blur-3xl"></div>

                {{-- Content --}}
                <div class="relative z-10 flex flex-col h-full p-10 text-white">
                    {{-- Top: Logo --}}
                    <a href="{{ route('home') }}" class="flex items-center gap-3" wire:navigate>
                        <div class="w-12 h-12 bg-white/95 rounded-2xl flex items-center justify-center shadow-lg shadow-black/10">
                            @if(!empty($systemLogo))
                                <img src="{{ Storage::url($systemLogo) }}" alt="{{ $systemName ?? config('app.name') }}" class="h-7 w-auto object-contain" />
                            @else
                                <x-app-logo-icon class="w-7 h-7 text-slate-900" />
                            @endif
                        </div>
                        <span class="text-xl font-semibold tracking-tight">{{ $systemName ?? config('app.name', 'CareNest') }}</span>
                    </a>

                    {{-- Center: Main Content --}}
                    <div class="flex-1 flex flex-col justify-center max-w-md">
                        <p class="text-xs uppercase tracking-[0.3em] text-white/60">CareNest</p>
                        <h1 class="mt-4 text-4xl font-semibold leading-tight">
                            Care that feels calm,<br>clear, and connected.
                        </h1>

                        <p class="mt-4 text-white/70 text-lg">
                            {{ $systemTagline ?? 'Professional, personalized care in a warm and supportive environment.' }}
                        </p>

                        {{-- Features --}}
                        <div class="mt-10 space-y-4">
                            <div class="flex items-start gap-4">
                                <div class="w-11 h-11 rounded-2xl bg-white/10 flex items-center justify-center shrink-0 ring-1 ring-white/10">
                                    <flux:icon.shield-check class="size-5" />
                                </div>
                                <div>
                                    <p class="font-medium">24/7 Professional Care</p>
                                    <p class="text-sm text-white/60">Trained staff available around the clock</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-11 h-11 rounded-2xl bg-white/10 flex items-center justify-center shrink-0 ring-1 ring-white/10">
                                    <flux:icon.heart class="size-5" />
                                </div>
                                <div>
                                    <p class="font-medium">Person-Centered Approach</p>
                                    <p class="text-sm text-white/60">Individualized care plans for every resident</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-11 h-11 rounded-2xl bg-white/10 flex items-center justify-center shrink-0 ring-1 ring-white/10">
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
            <div class="flex flex-col justify-center px-6 py-12 lg:px-8">
                <div class="mx-auto w-full max-w-[440px] rounded-3xl border auth-card backdrop-blur">
                    {{-- Mobile Logo --}}
                    <div class="lg:hidden flex flex-col items-center px-8 pt-8">
                        <a href="{{ route('home') }}" class="flex flex-col items-center gap-3" wire:navigate>
                            <span class="flex items-center justify-center w-14 h-14 rounded-2xl bg-slate-900 text-white shadow-lg shadow-slate-900/20">
                                @if(!empty($systemLogo))
                                    <img src="{{ Storage::url($systemLogo) }}" alt="{{ $systemName ?? config('app.name') }}" class="h-8 w-auto object-contain brightness-0 invert" />
                                @else
                                    <x-app-logo-icon class="w-8 h-8 text-white" />
                                @endif
                            </span>
                            <span class="text-xl font-semibold tracking-tight text-zinc-900 dark:text-white">{{ $systemName ?? config('app.name', 'Laravel') }}</span>
                        </a>
                    </div>

                    {{-- Form Content --}}
                    <div class="px-8 pb-8 pt-6">
                        {{ $slot }}
                    </div>

                    {{-- Trust Badge --}}
                    <div class="px-8 pb-8 pt-6 border-t border-zinc-200/70 dark:border-zinc-800/70">
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
