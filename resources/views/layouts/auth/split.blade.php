<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            <div class="bg-muted relative hidden h-full flex-col p-10 text-white lg:flex dark:border-e dark:border-neutral-800">
                <div class="absolute inset-0 bg-neutral-900"></div>
                <a href="{{ route('home') }}" class="relative z-20 flex items-center gap-2 text-lg font-medium" wire:navigate>
                    <span class="flex items-center justify-center rounded-md">
                        @if(!empty($systemLogo))
                            <img src="{{ Storage::url($systemLogo) }}" alt="{{ $systemName ?? config('app.name') }}" class="h-8 w-auto object-contain brightness-0 invert" />
                        @else
                            <x-app-logo-icon class="h-7 fill-current text-white" />
                        @endif
                    </span>
                    {{ $systemName ?? config('app.name', 'Laravel') }}
                </a>

                @php
                    [$message, $author] = str(Illuminate\Foundation\Inspiring::quotes()->random())->explode('-');
                @endphp

                <div class="relative z-20 mt-auto">
                    <blockquote class="space-y-2">
                        <flux:heading size="lg">&ldquo;{{ trim($message) }}&rdquo;</flux:heading>
                        <footer><flux:heading>{{ trim($author) }}</flux:heading></footer>
                    </blockquote>
                </div>
            </div>
            <div class="w-full lg:p-8">
                <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                    <a href="{{ route('home') }}" class="z-20 flex flex-col items-center gap-2 font-medium lg:hidden" wire:navigate>
                        <span class="flex items-center justify-center rounded-md">
                            @if(!empty($systemLogo))
                                <img src="{{ Storage::url($systemLogo) }}" alt="{{ $systemName ?? config('app.name') }}" class="h-10 w-auto object-contain" />
                            @else
                                <x-app-logo-icon class="size-9 fill-current text-black dark:text-white" />
                            @endif
                        </span>
                        <span class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $systemName ?? config('app.name', 'Laravel') }}</span>
                        @if(!empty($systemTagline))
                            <span class="text-sm text-zinc-500 dark:text-zinc-400 -mt-1">{{ $systemTagline }}</span>
                        @endif
                    </a>
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
