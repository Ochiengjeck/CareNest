<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark scroll-smooth">
    <head>
        @include('partials.head')
        <meta name="description" content="{{ system_setting('system_tagline', 'Professional care in a warm, homely environment') }}">
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 antialiased">
        {{-- Header --}}
        <x-public.header />

        {{-- Main Content --}}
        <main>
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <x-public.footer />

        {{-- Public Chatbot Widget --}}
        <x-public-chatbot />

        @fluxScripts
    </body>
</html>
