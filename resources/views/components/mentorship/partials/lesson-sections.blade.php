@php
/** @var array $sections */
/** @var array $sectionIcons */
/** @var array $sectionColors */
/** @var array $sectionBgs */
/** @var string $allowedTags */
/** @var array $metadata */
@endphp

<div class="space-y-4">
    @foreach($sections as $idx => $section)
        @php
            $colorIdx = $idx % count($sectionColors);
            $icon = $sectionIcons[$idx % count($sectionIcons)];
        @endphp
        <div data-section-idx="{{ $idx }}" class="group">
            {{-- Section card --}}
            <div class="rounded-2xl border {{ $sectionBgs[$colorIdx] }} overflow-hidden transition-shadow duration-200"
                 :class="isSectionOpen({{ $idx }}) && 'shadow-lg ring-1 ring-black/5 dark:ring-white/5'"
            >
                {{-- Section header (clickable) --}}
                <button
                    @click="toggleSection({{ $idx }})"
                    class="w-full text-left px-5 py-4 flex items-center gap-4 transition-colors hover:bg-black/[0.02] dark:hover:bg-white/[0.02]"
                >
                    {{-- Number badge with gradient --}}
                    <div class="flex items-center justify-center size-10 rounded-xl bg-gradient-to-br {{ $sectionColors[$colorIdx] }} text-white font-bold text-sm shadow-sm shrink-0">
                        {{ $idx + 1 }}
                    </div>

                    {{-- Title --}}
                    <div class="flex-1 min-w-0">
                        <h2 class="text-lg font-bold text-zinc-900 dark:text-white truncate">
                            {{ $section['title'] ?: __('Section') . ' ' . ($idx + 1) }}
                        </h2>
                        @if(!empty($section['subsections']))
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                                {{ count($section['subsections']) }} {{ trans_choice('subtopic|subtopics', count($section['subsections'])) }}
                                @if(!empty($section['media']))
                                    &middot; {{ count($section['media']) }} {{ trans_choice('attachment|attachments', count($section['media'])) }}
                                @endif
                            </p>
                        @endif
                    </div>

                    {{-- Chevron --}}
                    <div class="shrink-0 text-zinc-400 transition-transform duration-300"
                         :class="isSectionOpen({{ $idx }}) && 'rotate-180'"
                    >
                        <flux:icon.chevron-down class="size-5" />
                    </div>
                </button>

                {{-- Section body (collapsible) --}}
                <div
                    x-show="isSectionOpen({{ $idx }})"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                >
                    <div class="px-5 pb-5 space-y-5">
                        {{-- Divider --}}
                        <div class="h-px bg-gradient-to-r from-transparent via-zinc-300 dark:via-zinc-600 to-transparent"></div>

                        {{-- Section content --}}
                        @if(!empty($section['content']))
                            <div class="prose prose-zinc dark:prose-invert max-w-none prose-headings:text-zinc-800 dark:prose-headings:text-zinc-100 prose-a:text-purple-600 dark:prose-a:text-purple-400 prose-img:rounded-xl">
                                {!! strip_tags($section['content'], $allowedTags) !!}
                            </div>
                        @endif

                        {{-- Section media gallery --}}
                        @if(!empty($section['media']))
                            <div class="space-y-4">
                                {{-- Images --}}
                                @php $images = array_filter($section['media'], fn($m) => ($m['type'] ?? '') === 'image' && !empty($m['path'])); @endphp
                                @if(count($images) > 0)
                                    <div class="grid gap-3 {{ count($images) === 1 ? '' : 'sm:grid-cols-2' }}">
                                        @foreach($images as $media)
                                            <div class="group/img relative rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700 cursor-pointer shadow-sm hover:shadow-md transition-shadow"
                                                 @click="openLightbox('{{ Storage::url($media['path']) }}', '{{ addslashes($media['caption'] ?? '') }}')"
                                            >
                                                <img src="{{ Storage::url($media['path']) }}" alt="{{ $media['caption'] ?? '' }}"
                                                     class="w-full object-cover {{ count($images) === 1 ? 'max-h-96' : 'h-48' }}" loading="lazy">
                                                <div class="absolute inset-0 bg-black/0 group-hover/img:bg-black/10 transition-colors flex items-center justify-center">
                                                    <div class="opacity-0 group-hover/img:opacity-100 transition-opacity bg-white/90 dark:bg-zinc-800/90 rounded-full p-2 shadow-lg">
                                                        <flux:icon.arrows-pointing-out class="size-5 text-zinc-700 dark:text-zinc-200" />
                                                    </div>
                                                </div>
                                                @if(!empty($media['caption']))
                                                    <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/60 to-transparent px-3 py-2">
                                                        <p class="text-xs text-white">{{ $media['caption'] }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Videos --}}
                                @php $videos = array_filter($section['media'], fn($m) => ($m['type'] ?? '') === 'video' && !empty($m['url'])); @endphp
                                @if(count($videos) > 0)
                                    <div class="grid gap-3 {{ count($videos) === 1 ? '' : 'sm:grid-cols-2' }}">
                                        @foreach($videos as $media)
                                            @php
                                                $embedUrl = null;
                                                $url = $media['url'];
                                                if (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) {
                                                    preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $matches);
                                                    $embedUrl = isset($matches[1]) ? "https://www.youtube.com/embed/{$matches[1]}" : null;
                                                } elseif (str_contains($url, 'vimeo.com')) {
                                                    preg_match('/vimeo\.com\/(\d+)/', $url, $matches);
                                                    $embedUrl = isset($matches[1]) ? "https://player.vimeo.com/video/{$matches[1]}" : null;
                                                }
                                            @endphp
                                            <div class="rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700 shadow-sm">
                                                @if($embedUrl)
                                                    <div class="aspect-video bg-black">
                                                        <iframe src="{{ $embedUrl }}" class="w-full h-full" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>
                                                    </div>
                                                @else
                                                    <a href="{{ $url }}" target="_blank" rel="noopener" class="flex items-center gap-3 p-4 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                                                        <div class="flex items-center justify-center size-10 rounded-full bg-red-100 dark:bg-red-900/30">
                                                            <flux:icon.play class="size-5 text-red-600" />
                                                        </div>
                                                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300 truncate">{{ $media['title'] ?? $url }}</span>
                                                        <flux:icon.arrow-top-right-on-square class="size-4 text-zinc-400 ml-auto shrink-0" />
                                                    </a>
                                                @endif
                                                @if(!empty($media['title']) && $embedUrl)
                                                    <div class="px-3 py-2 bg-zinc-50 dark:bg-zinc-800/50 border-t border-zinc-200 dark:border-zinc-700">
                                                        <p class="text-xs font-medium text-zinc-600 dark:text-zinc-400">{{ $media['title'] }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Documents --}}
                                @php $docs = array_filter($section['media'], fn($m) => ($m['type'] ?? '') === 'document' && !empty($m['path'])); @endphp
                                @if(count($docs) > 0)
                                    <div class="space-y-2">
                                        @foreach($docs as $media)
                                            <a href="{{ Storage::url($media['path']) }}" target="_blank" rel="noopener"
                                               class="flex items-center gap-3 p-3 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-750 hover:shadow-sm transition group/doc">
                                                <div class="flex items-center justify-center size-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 shrink-0">
                                                    <flux:icon.document-text class="size-5 text-blue-600" />
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="font-medium text-sm truncate">{{ $media['name'] ?? 'Document' }}</p>
                                                    @if(!empty($media['caption']))
                                                        <p class="text-xs text-zinc-500 truncate">{{ $media['caption'] }}</p>
                                                    @endif
                                                </div>
                                                <flux:icon.arrow-down-tray class="size-4 text-zinc-400 group-hover/doc:text-blue-600 transition shrink-0" />
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- AI Media Suggestions --}}
                        @if(!empty($section['media_suggestions']))
                            <div class="rounded-xl border border-amber-200 dark:border-amber-800/60 bg-amber-50/50 dark:bg-amber-950/20 p-4">
                                <div class="flex items-center gap-2 mb-3">
                                    <flux:icon.sparkles class="size-4 text-amber-600" />
                                    <span class="text-xs font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wide">{{ __('Suggested Resources') }}</span>
                                </div>
                                <div class="space-y-2">
                                    @foreach($section['media_suggestions'] as $suggestion)
                                        <div class="flex items-start gap-3 p-2 rounded-lg hover:bg-amber-100/50 dark:hover:bg-amber-900/20 transition">
                                            @if(($suggestion['type'] ?? '') === 'video')
                                                <div class="flex items-center justify-center size-8 rounded-lg bg-red-100 dark:bg-red-900/30 shrink-0 mt-0.5">
                                                    <flux:icon.play class="size-4 text-red-500" />
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $suggestion['description'] ?? $suggestion['search_term'] ?? '' }}</p>
                                                    @if(!empty($suggestion['search_term']))
                                                        <a href="https://www.youtube.com/results?search_query={{ urlencode($suggestion['search_term']) }}" target="_blank" rel="noopener"
                                                           class="inline-flex items-center gap-1 mt-1 text-xs font-medium text-red-600 dark:text-red-400 hover:underline">
                                                            <flux:icon.play-circle class="size-3" />
                                                            {{ __('Search on YouTube') }}
                                                            <flux:icon.arrow-top-right-on-square class="size-3" />
                                                        </a>
                                                    @endif
                                                </div>
                                            @elseif(($suggestion['type'] ?? '') === 'image')
                                                <div class="flex items-center justify-center size-8 rounded-lg bg-green-100 dark:bg-green-900/30 shrink-0 mt-0.5">
                                                    <flux:icon.photo class="size-4 text-green-500" />
                                                </div>
                                                <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $suggestion['description'] ?? '' }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Subsections --}}
                        @if(!empty($section['subsections']))
                            <div class="space-y-3 pt-2">
                                @foreach($section['subsections'] as $subIdx => $subsection)
                                    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/50 overflow-hidden">
                                        {{-- Subsection header --}}
                                        <div class="px-4 py-3 border-b border-zinc-100 dark:border-zinc-700/50 flex items-center gap-3">
                                            <div class="flex items-center justify-center size-6 rounded-md bg-gradient-to-br {{ $sectionColors[$colorIdx] }} text-white text-[10px] font-bold opacity-60">
                                                {{ $idx + 1 }}.{{ $subIdx + 1 }}
                                            </div>
                                            <h3 class="font-semibold text-zinc-800 dark:text-zinc-100 text-[15px]">
                                                {{ $subsection['title'] ?? '' }}
                                            </h3>
                                        </div>

                                        {{-- Subsection content --}}
                                        <div class="px-4 py-3 space-y-4">
                                            @if(!empty($subsection['content']))
                                                <div class="prose prose-sm prose-zinc dark:prose-invert max-w-none prose-a:text-purple-600 dark:prose-a:text-purple-400">
                                                    {!! strip_tags($subsection['content'], $allowedTags) !!}
                                                </div>
                                            @endif

                                            {{-- Subsection media --}}
                                            @if(!empty($subsection['media']))
                                                <div class="grid gap-3 {{ count($subsection['media']) === 1 ? '' : 'sm:grid-cols-2' }}">
                                                    @foreach($subsection['media'] as $media)
                                                        @if($media['type'] === 'image' && !empty($media['path']))
                                                            <div class="rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 cursor-pointer hover:shadow-md transition-shadow"
                                                                 @click="openLightbox('{{ Storage::url($media['path']) }}', '{{ addslashes($media['caption'] ?? '') }}')"
                                                            >
                                                                <img src="{{ Storage::url($media['path']) }}" alt="{{ $media['caption'] ?? '' }}" class="w-full h-40 object-cover" loading="lazy">
                                                                @if(!empty($media['caption']))
                                                                    <p class="text-xs text-zinc-500 p-2">{{ $media['caption'] }}</p>
                                                                @endif
                                                            </div>
                                                        @elseif($media['type'] === 'video' && !empty($media['url']))
                                                            @php
                                                                $subEmbedUrl = null;
                                                                $subUrl = $media['url'];
                                                                if (str_contains($subUrl, 'youtube.com') || str_contains($subUrl, 'youtu.be')) {
                                                                    preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $subUrl, $subMatches);
                                                                    $subEmbedUrl = isset($subMatches[1]) ? "https://www.youtube.com/embed/{$subMatches[1]}" : null;
                                                                } elseif (str_contains($subUrl, 'vimeo.com')) {
                                                                    preg_match('/vimeo\.com\/(\d+)/', $subUrl, $subMatches);
                                                                    $subEmbedUrl = isset($subMatches[1]) ? "https://player.vimeo.com/video/{$subMatches[1]}" : null;
                                                                }
                                                            @endphp
                                                            <div class="rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700">
                                                                @if($subEmbedUrl)
                                                                    <div class="aspect-video bg-black">
                                                                        <iframe src="{{ $subEmbedUrl }}" class="w-full h-full" frameborder="0" allowfullscreen loading="lazy"></iframe>
                                                                    </div>
                                                                @else
                                                                    <a href="{{ $subUrl }}" target="_blank" rel="noopener" class="flex items-center gap-2 p-3 hover:bg-zinc-50 dark:hover:bg-zinc-800">
                                                                        <flux:icon.play-circle class="size-4 text-red-600" />
                                                                        <span class="text-xs">{{ $media['title'] ?? $subUrl }}</span>
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        @elseif($media['type'] === 'document' && !empty($media['path']))
                                                            <a href="{{ Storage::url($media['path']) }}" target="_blank" rel="noopener"
                                                               class="flex items-center gap-2 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 text-sm transition">
                                                                <flux:icon.document class="size-4 text-blue-600" />
                                                                <span class="truncate">{{ $media['name'] ?? 'Document' }}</span>
                                                                <flux:icon.arrow-down-tray class="size-3.5 text-zinc-400 ml-auto" />
                                                            </a>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif

                                            {{-- Subsection media suggestions --}}
                                            @if(!empty($subsection['media_suggestions']))
                                                <div class="space-y-1.5">
                                                    @foreach($subsection['media_suggestions'] as $suggestion)
                                                        <div class="flex items-center gap-2 text-xs text-amber-700 dark:text-amber-400">
                                                            @if(($suggestion['type'] ?? '') === 'video')
                                                                <flux:icon.play-circle class="size-3.5 text-red-500" />
                                                            @else
                                                                <flux:icon.photo class="size-3.5 text-green-500" />
                                                            @endif
                                                            <span>{{ $suggestion['description'] ?? '' }}</span>
                                                            @if(!empty($suggestion['search_term']))
                                                                <a href="https://www.youtube.com/results?search_query={{ urlencode($suggestion['search_term']) }}" target="_blank" rel="noopener" class="text-red-600 hover:underline ml-auto shrink-0">
                                                                    {{ __('YouTube') }}
                                                                </a>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Expand / Collapse all (if multiple sections) --}}
    @if(count($sections) > 1)
        <div class="flex justify-center gap-3 pt-2">
            <button @click="expandAll()" class="text-xs text-purple-600 dark:text-purple-400 hover:underline font-medium">
                {{ __('Expand All') }}
            </button>
            <span class="text-zinc-300 dark:text-zinc-600">&middot;</span>
            <button @click="collapseAll()" class="text-xs text-zinc-500 hover:underline font-medium">
                {{ __('Collapse All') }}
            </button>
        </div>
    @endif
</div>
