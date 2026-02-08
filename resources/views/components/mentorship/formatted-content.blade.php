@props(['content'])

@php
$contentData = $content;

// Handle various input formats
if (is_string($contentData)) {
    $decoded = json_decode($contentData, true);
    if (is_array($decoded) && isset($decoded['sections'])) {
        $contentData = $decoded;
    } else {
        // Legacy plain text / markdown — wrap in structure
        $contentData = [
            'sections' => [[
                'id' => 'legacy',
                'title' => '',
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

$allowedTags = '<p><strong><em><b><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6><a><br><span><div><blockquote><code><pre>';
@endphp

<div {{ $attributes->merge(['class' => 'space-y-6']) }}>
    @foreach($contentData['sections'] as $section)
        <div>
            @if(!empty($section['title']))
                <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-3">{{ $section['title'] }}</h2>
            @endif

            @if(!empty($section['content']))
                <div class="prose prose-zinc dark:prose-invert max-w-none mb-4">
                    {!! strip_tags($section['content'], $allowedTags) !!}
                </div>
            @endif

            {{-- Section Media --}}
            @if(!empty($section['media']))
                <div class="grid gap-4 mb-4 sm:grid-cols-2">
                    @foreach($section['media'] as $media)
                        @if($media['type'] === 'image' && !empty($media['path']))
                            <div class="rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700">
                                <img src="{{ Storage::url($media['path']) }}" alt="{{ $media['caption'] ?? '' }}" class="w-full" loading="lazy">
                                @if(!empty($media['caption']))
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400 p-2">{{ $media['caption'] }}</p>
                                @endif
                            </div>
                        @elseif($media['type'] === 'video' && !empty($media['url']))
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
                            <div class="rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700">
                                @if($embedUrl)
                                    <div class="aspect-video">
                                        <iframe src="{{ $embedUrl }}" class="w-full h-full" frameborder="0" allowfullscreen loading="lazy"></iframe>
                                    </div>
                                @else
                                    <a href="{{ $url }}" target="_blank" rel="noopener" class="flex items-center gap-2 p-3 hover:bg-zinc-50 dark:hover:bg-zinc-800">
                                        <flux:icon.play-circle class="size-5 text-red-600" />
                                        <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $media['title'] ?? $url }}</span>
                                    </a>
                                @endif
                            </div>
                        @elseif($media['type'] === 'document' && !empty($media['path']))
                            <a href="{{ Storage::url($media['path']) }}" target="_blank" rel="noopener" class="flex items-center gap-3 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                                <flux:icon.document class="size-5 text-blue-600" />
                                <div>
                                    <p class="font-medium text-sm">{{ $media['name'] ?? 'Document' }}</p>
                                    @if(!empty($media['caption']))
                                        <p class="text-xs text-zinc-500">{{ $media['caption'] }}</p>
                                    @endif
                                </div>
                                <flux:icon.arrow-down-tray class="size-4 text-zinc-400 ml-auto" />
                            </a>
                        @endif
                    @endforeach
                </div>
            @endif

            {{-- AI Media Suggestions (display mode) --}}
            @if(!empty($section['media_suggestions']))
                <div class="mb-4 space-y-2">
                    @foreach($section['media_suggestions'] as $suggestion)
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-sm">
                            @if(($suggestion['type'] ?? '') === 'video')
                                <flux:icon.play-circle class="size-4 text-red-500" />
                                <span class="flex-1 text-xs">{{ $suggestion['description'] ?? $suggestion['search_term'] ?? '' }}</span>
                                @if(!empty($suggestion['search_term']))
                                    <a href="https://www.youtube.com/results?search_query={{ urlencode($suggestion['search_term']) }}" target="_blank" rel="noopener" class="text-xs text-purple-600 hover:underline">
                                        {{ __('Search YouTube') }}
                                    </a>
                                @endif
                            @elseif(($suggestion['type'] ?? '') === 'image')
                                <flux:icon.photo class="size-4 text-green-500" />
                                <span class="flex-1 text-xs">{{ $suggestion['description'] ?? '' }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Subsections --}}
            @if(!empty($section['subsections']))
                <div class="ml-4 space-y-4 border-l-2 border-purple-200 dark:border-purple-700 pl-4">
                    @foreach($section['subsections'] as $subsection)
                        <div>
                            @if(!empty($subsection['title']))
                                <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200 mb-2">{{ $subsection['title'] }}</h3>
                            @endif

                            @if(!empty($subsection['content']))
                                <div class="prose prose-sm prose-zinc dark:prose-invert max-w-none mb-3">
                                    {!! strip_tags($subsection['content'], $allowedTags) !!}
                                </div>
                            @endif

                            {{-- Subsection media (same patterns) --}}
                            @if(!empty($subsection['media']))
                                <div class="grid gap-3 sm:grid-cols-2 mb-3">
                                    @foreach($subsection['media'] as $media)
                                        @if($media['type'] === 'image' && !empty($media['path']))
                                            <div class="rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700">
                                                <img src="{{ Storage::url($media['path']) }}" alt="{{ $media['caption'] ?? '' }}" class="w-full" loading="lazy">
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
                                            <div class="rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 aspect-video">
                                                @if($subEmbedUrl)
                                                    <iframe src="{{ $subEmbedUrl }}" class="w-full h-full" frameborder="0" allowfullscreen loading="lazy"></iframe>
                                                @else
                                                    <a href="{{ $subUrl }}" target="_blank" rel="noopener" class="flex items-center gap-2 p-3">
                                                        <flux:icon.play-circle class="size-4 text-red-600" />
                                                        <span class="text-xs">{{ $media['title'] ?? $subUrl }}</span>
                                                    </a>
                                                @endif
                                            </div>
                                        @elseif($media['type'] === 'document' && !empty($media['path']))
                                            <a href="{{ Storage::url($media['path']) }}" target="_blank" rel="noopener" class="flex items-center gap-2 p-2 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 text-sm">
                                                <flux:icon.document class="size-4 text-blue-600" />
                                                <span>{{ $media['name'] ?? 'Document' }}</span>
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach
</div>
