@props(['content' => '', 'placeholder' => 'Start writing...'])

<div
    x-data="tiptapEditor(@js($content), @js($placeholder))"
    x-init="init()"
    x-on:destroy.window="destroy()"
    wire:ignore
    {{ $attributes->merge(['class' => 'border border-zinc-300 dark:border-zinc-600 rounded-lg overflow-hidden']) }}
>
    {{-- Toolbar --}}
    <div class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-300 dark:border-zinc-600 px-2 py-1.5 flex flex-wrap gap-0.5">
        <button type="button" @click="toggleBold()" :class="isActive('bold') && 'bg-zinc-200 dark:bg-zinc-700'" class="p-1.5 rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 text-sm font-bold" title="Bold">
            B
        </button>
        <button type="button" @click="toggleItalic()" :class="isActive('italic') && 'bg-zinc-200 dark:bg-zinc-700'" class="p-1.5 rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 text-sm italic" title="Italic">
            I
        </button>
        <button type="button" @click="toggleUnderline()" :class="isActive('underline') && 'bg-zinc-200 dark:bg-zinc-700'" class="p-1.5 rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 text-sm underline" title="Underline">
            U
        </button>

        <div class="w-px bg-zinc-300 dark:bg-zinc-600 mx-1"></div>

        <button type="button" @click="toggleHeading(3)" :class="isActive('heading', {level: 3}) && 'bg-zinc-200 dark:bg-zinc-700'" class="px-1.5 py-1 rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 text-xs font-semibold" title="Heading 3">
            H3
        </button>
        <button type="button" @click="toggleHeading(4)" :class="isActive('heading', {level: 4}) && 'bg-zinc-200 dark:bg-zinc-700'" class="px-1.5 py-1 rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 text-xs font-semibold" title="Heading 4">
            H4
        </button>

        <div class="w-px bg-zinc-300 dark:bg-zinc-600 mx-1"></div>

        <button type="button" @click="toggleBulletList()" :class="isActive('bulletList') && 'bg-zinc-200 dark:bg-zinc-700'" class="px-1.5 py-1 rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 text-xs" title="Bullet List">
            &bull; List
        </button>
        <button type="button" @click="toggleOrderedList()" :class="isActive('orderedList') && 'bg-zinc-200 dark:bg-zinc-700'" class="px-1.5 py-1 rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 text-xs" title="Numbered List">
            1. List
        </button>

        <div class="w-px bg-zinc-300 dark:bg-zinc-600 mx-1"></div>

        <button type="button" @click="isActive('link') ? unsetLink() : setLink()" :class="isActive('link') && 'bg-zinc-200 dark:bg-zinc-700'" class="px-1.5 py-1 rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 text-xs" title="Link">
            Link
        </button>
    </div>

    {{-- Editor --}}
    <div x-ref="element" class="bg-white dark:bg-zinc-900"></div>
</div>
