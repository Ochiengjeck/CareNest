@props(['content', 'title' => null])

@php
$contentData = $content;

// Handle various input formats
if (is_string($contentData)) {
    $decoded = json_decode($contentData, true);
    if (is_array($decoded) && isset($decoded['sections'])) {
        $contentData = $decoded;
    } else {
        $contentData = [
            'sections' => [[
                'id' => 'legacy',
                'title' => 'Lesson Content',
                'content' => Str::markdown($contentData, ['html_input' => 'escape', 'allow_unsafe_links' => false]),
                'media' => [],
                'subsections' => [],
            ]],
        ];
    }
}

if (!is_array($contentData) || !isset($contentData['sections'])) {
    $contentData = ['sections' => []];
}

$sections = $contentData['sections'];
$metadata = $contentData['metadata'] ?? [];
$sectionCount = count($sections);
$subsectionCount = $metadata['subsection_count'] ?? 0;
$videoCount = ($metadata['video_count'] ?? 0) + ($metadata['suggested_video_count'] ?? 0);
$imageCount = ($metadata['image_count'] ?? 0) + ($metadata['suggested_image_count'] ?? 0);
$documentCount = $metadata['document_count'] ?? 0;
$allowedTags = '<p><strong><em><b><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6><a><br><span><div><blockquote><code><pre>';

// Section icons (cycle through for visual variety)
$sectionIcons = ['light-bulb', 'academic-cap', 'book-open', 'chat-bubble-left-right', 'clipboard-document-check', 'puzzle-piece', 'star', 'rocket-launch'];
$sectionColors = [
    'from-purple-500 to-indigo-500',
    'from-blue-500 to-cyan-500',
    'from-emerald-500 to-teal-500',
    'from-amber-500 to-orange-500',
    'from-rose-500 to-pink-500',
    'from-violet-500 to-fuchsia-500',
    'from-sky-500 to-blue-500',
    'from-lime-500 to-green-500',
];
$sectionBgs = [
    'bg-purple-50 dark:bg-purple-950/30 border-purple-200 dark:border-purple-800',
    'bg-blue-50 dark:bg-blue-950/30 border-blue-200 dark:border-blue-800',
    'bg-emerald-50 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800',
    'bg-amber-50 dark:bg-amber-950/30 border-amber-200 dark:border-amber-800',
    'bg-rose-50 dark:bg-rose-950/30 border-rose-200 dark:border-rose-800',
    'bg-violet-50 dark:bg-violet-950/30 border-violet-200 dark:border-violet-800',
    'bg-sky-50 dark:bg-sky-950/30 border-sky-200 dark:border-sky-800',
    'bg-lime-50 dark:bg-lime-950/30 border-lime-200 dark:border-lime-800',
];
@endphp

<div
    x-data="lessonViewer({{ $sectionCount }})"
    {{ $attributes->merge(['class' => '']) }}
>
    {{-- Fullscreen overlay --}}
    <div
        x-show="isFullscreen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-cloak
        class="fixed inset-0 z-50 bg-white dark:bg-zinc-900 overflow-y-auto"
        @keydown.escape.window="isFullscreen = false"
    >
        {{-- Fullscreen top bar --}}
        <div class="sticky top-0 z-10 bg-white/90 dark:bg-zinc-900/90 backdrop-blur-md border-b border-zinc-200 dark:border-zinc-700">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="flex items-center justify-center size-8 rounded-lg bg-gradient-to-br from-purple-500 to-indigo-500 text-white shrink-0">
                        <flux:icon.book-open class="size-4" />
                    </div>
                    @if($title)
                        <h1 class="text-lg font-semibold truncate">{{ $title }}</h1>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    {{-- Section quick nav --}}
                    <div class="hidden sm:flex items-center gap-1">
                        @foreach($sections as $idx => $s)
                            <button
                                @click="scrollToSection({{ $idx }}); openSection = {{ $idx }}"
                                :class="openSection === {{ $idx }} ? 'bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300' : 'text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300'"
                                class="size-7 rounded-full text-xs font-medium transition-all flex items-center justify-center"
                                title="{{ $s['title'] ?? 'Section ' . ($idx + 1) }}"
                            >
                                {{ $idx + 1 }}
                            </button>
                        @endforeach
                    </div>
                    <div class="w-px h-6 bg-zinc-200 dark:bg-zinc-700 hidden sm:block"></div>
                    <button @click="isFullscreen = false" class="p-2 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-500 transition">
                        <flux:icon.x-mark class="size-5" />
                    </button>
                </div>
            </div>
        </div>

        {{-- Fullscreen content --}}
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
            <div x-ref="fullscreenContent">
                @include('components.mentorship.partials.lesson-sections', ['sections' => $sections, 'sectionIcons' => $sectionIcons, 'sectionColors' => $sectionColors, 'sectionBgs' => $sectionBgs, 'allowedTags' => $allowedTags, 'metadata' => $metadata])
            </div>
        </div>
    </div>

    {{-- Normal view --}}
    <div x-show="!isFullscreen">
        {{-- Stats bar --}}
        @if($sectionCount > 0)
            <div class="flex flex-wrap items-center gap-3 mb-6">
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 text-xs font-medium">
                    <flux:icon.squares-2x2 class="size-3.5" />
                    {{ $sectionCount }} {{ trans_choice('section|sections', $sectionCount) }}
                </div>
                @if($subsectionCount > 0)
                    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs font-medium">
                        <flux:icon.list-bullet class="size-3.5" />
                        {{ $subsectionCount }} {{ trans_choice('subtopic|subtopics', $subsectionCount) }}
                    </div>
                @endif
                @if($videoCount > 0)
                    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 text-xs font-medium">
                        <flux:icon.play-circle class="size-3.5" />
                        {{ $videoCount }} {{ trans_choice('video|videos', $videoCount) }}
                    </div>
                @endif
                @if($imageCount > 0)
                    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 text-xs font-medium">
                        <flux:icon.photo class="size-3.5" />
                        {{ $imageCount }} {{ trans_choice('image|images', $imageCount) }}
                    </div>
                @endif
                @if($documentCount > 0)
                    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-sky-100 dark:bg-sky-900/30 text-sky-700 dark:text-sky-300 text-xs font-medium">
                        <flux:icon.document class="size-3.5" />
                        {{ $documentCount }} {{ trans_choice('document|documents', $documentCount) }}
                    </div>
                @endif

                <div class="ml-auto">
                    <button
                        @click="isFullscreen = true"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 transition"
                        title="{{ __('Fullscreen') }}"
                    >
                        <flux:icon.arrows-pointing-out class="size-3.5" />
                        <span class="hidden sm:inline">{{ __('Fullscreen') }}</span>
                    </button>
                </div>
            </div>
        @endif

        {{-- Section navigation pills (horizontal scroll) --}}
        @if($sectionCount > 1)
            <div class="flex gap-2 mb-6 overflow-x-auto pb-2 scrollbar-hide">
                @foreach($sections as $idx => $section)
                    <button
                        @click="toggleSection({{ $idx }}); scrollToSection({{ $idx }})"
                        :class="openSection === {{ $idx }}
                            ? 'bg-gradient-to-r {{ $sectionColors[$idx % count($sectionColors)] }} text-white shadow-md'
                            : 'bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600'"
                        class="flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-all shrink-0"
                    >
                        <span class="flex items-center justify-center size-5 rounded-full text-xs"
                            :class="openSection === {{ $idx }} ? 'bg-white/20' : 'bg-zinc-100 dark:bg-zinc-700'"
                        >{{ $idx + 1 }}</span>
                        {{ Str::limit($section['title'] ?? __('Section') . ' ' . ($idx + 1), 25) }}
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Sections --}}
        <div x-ref="normalContent">
            @include('components.mentorship.partials.lesson-sections', ['sections' => $sections, 'sectionIcons' => $sectionIcons, 'sectionColors' => $sectionColors, 'sectionBgs' => $sectionBgs, 'allowedTags' => $allowedTags, 'metadata' => $metadata])
        </div>
    </div>

    {{-- Image lightbox --}}
    <div
        x-show="lightboxImage"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-cloak
        @click="lightboxImage = null"
        @keydown.escape.window="lightboxImage = null"
        class="fixed inset-0 z-[60] bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 cursor-pointer"
    >
        <div class="relative max-w-5xl max-h-[90vh]" @click.stop>
            <img :src="lightboxImage" class="max-w-full max-h-[85vh] rounded-xl shadow-2xl object-contain" />
            <button @click="lightboxImage = null" class="absolute -top-3 -right-3 size-8 rounded-full bg-white dark:bg-zinc-800 shadow-lg flex items-center justify-center text-zinc-500 hover:text-zinc-700 transition">
                <flux:icon.x-mark class="size-4" />
            </button>
            <p x-show="lightboxCaption" x-text="lightboxCaption" class="text-center text-white/80 text-sm mt-3"></p>
        </div>
    </div>
</div>

