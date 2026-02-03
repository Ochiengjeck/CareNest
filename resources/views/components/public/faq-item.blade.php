@props([
    'question' => '',
    'answer' => '',
    'open' => false,
])

<div
    x-data="{ open: {{ $open ? 'true' : 'false' }} }"
    {{ $attributes->merge(['class' => 'border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden']) }}
>
    <button
        @click="open = !open"
        type="button"
        class="w-full flex items-center justify-between gap-4 p-5 text-left bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors"
        :aria-expanded="open"
    >
        <span class="font-medium text-zinc-900 dark:text-white">{{ $question }}</span>
        <flux:icon.chevron-down
            class="size-5 text-zinc-500 shrink-0 transition-transform duration-200"
            ::class="{ 'rotate-180': open }"
        />
    </button>

    <div
        x-show="open"
        x-collapse
        x-cloak
    >
        <div class="px-5 pb-5 text-zinc-600 dark:text-zinc-400 leading-relaxed bg-white dark:bg-zinc-800">
            {{ $answer }}
            {{ $slot }}
        </div>
    </div>
</div>
