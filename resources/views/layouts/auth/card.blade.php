<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-neutral-100 antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="bg-muted flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-md flex-col gap-6">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
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

                <div class="flex flex-col gap-6">
                    <div class="rounded-xl border bg-white dark:bg-stone-950 dark:border-stone-800 text-stone-800 shadow-xs">
                        <div class="px-10 py-8">{{ $slot }}</div>
                    </div>
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
