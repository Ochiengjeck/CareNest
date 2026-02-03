<header
    x-data="{ mobileMenuOpen: false }"
    class="sticky top-0 z-50 w-full border-b border-zinc-200 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/80 backdrop-blur-lg"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 lg:h-20">
            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0">
                @if(!empty($systemLogo))
                    <img src="{{ Storage::url($systemLogo) }}" alt="{{ $systemName ?? 'CareNest' }}" class="h-10 w-auto">
                @else
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-accent">
                        <x-app-logo-icon class="size-6 text-white" />
                    </div>
                @endif
                <span class="text-xl font-semibold text-zinc-900 dark:text-white">
                    {{ $systemName ?? 'CareNest' }}
                </span>
            </a>

            {{-- Desktop Navigation --}}
            <nav class="hidden lg:flex items-center gap-1">
                @php
                    $navItems = [
                        ['label' => 'Home', 'route' => 'home'],
                        ['label' => 'About', 'route' => 'about'],
                        ['label' => 'Services', 'route' => 'services'],
                        ['label' => 'Gallery', 'route' => 'gallery'],
                        ['label' => 'FAQ', 'route' => 'faq'],
                        ['label' => 'Contact', 'route' => 'contact'],
                    ];
                @endphp

                @foreach($navItems as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors
                            {{ request()->routeIs($item['route'])
                                ? 'text-accent bg-accent/10'
                                : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800'
                            }}"
                    >
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            {{-- Desktop Actions --}}
            <div class="hidden lg:flex items-center gap-3">
                @auth
                    <a
                        href="{{ route('dashboard') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-accent hover:bg-accent-content transition-colors"
                    >
                        <flux:icon.squares-2x2 variant="mini" class="size-4" />
                        Dashboard
                    </a>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                    >
                        Staff Login
                    </a>
                    <a
                        href="{{ route('contact') }}"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-medium text-white bg-accent hover:bg-accent-content transition-colors"
                    >
                        Schedule a Visit
                    </a>
                @endauth
            </div>

            {{-- Mobile Menu Button --}}
            <button
                @click="mobileMenuOpen = !mobileMenuOpen"
                type="button"
                class="lg:hidden inline-flex items-center justify-center p-2 rounded-lg text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                :aria-expanded="mobileMenuOpen"
                aria-controls="mobile-menu"
            >
                <span class="sr-only">Open main menu</span>
                <svg x-show="!mobileMenuOpen" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg x-show="mobileMenuOpen" x-cloak class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div
        x-show="mobileMenuOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="lg:hidden border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900"
        id="mobile-menu"
    >
        <nav class="px-4 py-4 space-y-1">
            @foreach($navItems as $item)
                <a
                    href="{{ route($item['route']) }}"
                    class="block px-4 py-3 rounded-lg text-base font-medium transition-colors
                        {{ request()->routeIs($item['route'])
                            ? 'text-accent bg-accent/10'
                            : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800'
                        }}"
                >
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="px-4 py-4 border-t border-zinc-200 dark:border-zinc-800 space-y-3">
            @auth
                <a
                    href="{{ route('dashboard') }}"
                    class="flex items-center justify-center gap-2 w-full px-4 py-3 rounded-lg text-base font-medium text-white bg-accent hover:bg-accent-content transition-colors"
                >
                    <flux:icon.squares-2x2 variant="mini" class="size-5" />
                    Dashboard
                </a>
            @else
                <a
                    href="{{ route('contact') }}"
                    class="flex items-center justify-center gap-2 w-full px-4 py-3 rounded-lg text-base font-medium text-white bg-accent hover:bg-accent-content transition-colors"
                >
                    Schedule a Visit
                </a>
                <a
                    href="{{ route('login') }}"
                    class="flex items-center justify-center gap-2 w-full px-4 py-3 rounded-lg text-base font-medium text-zinc-700 dark:text-zinc-300 border border-zinc-300 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                >
                    Staff Login
                </a>
            @endauth
        </div>
    </div>
</header>
