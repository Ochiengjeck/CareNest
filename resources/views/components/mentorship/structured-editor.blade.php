@props(['wireModel' => 'lessonContent'])

<div
    x-data="structuredEditor(@entangle($wireModel).live)"
    class="space-y-4"
>
    {{-- Metadata Summary --}}
    <div x-show="content.sections && content.sections.length > 0" class="flex flex-wrap gap-3 text-xs text-zinc-500 dark:text-zinc-400">
        <span><strong x-text="content.metadata?.section_count || 0"></strong> {{ __('sections') }}</span>
        <span><strong x-text="content.metadata?.subsection_count || 0"></strong> {{ __('subsections') }}</span>
        <span x-show="(content.metadata?.video_count || 0) > 0"><strong x-text="content.metadata?.video_count"></strong> {{ __('videos') }}</span>
        <span x-show="(content.metadata?.image_count || 0) > 0"><strong x-text="content.metadata?.image_count"></strong> {{ __('images') }}</span>
        <span x-show="(content.metadata?.document_count || 0) > 0"><strong x-text="content.metadata?.document_count"></strong> {{ __('documents') }}</span>
    </div>

    {{-- Sections --}}
    <template x-for="(section, sIdx) in content.sections" :key="section.id">
        <div class="border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
            {{-- Section Header --}}
            <div class="bg-zinc-50 dark:bg-zinc-800 px-4 py-3 flex items-center gap-3">
                <span class="text-xs font-semibold text-zinc-400 uppercase" x-text="'Section ' + (sIdx + 1)"></span>
                <input
                    type="text"
                    x-model="section.title"
                    @input="sync()"
                    placeholder="{{ __('Section Title') }}"
                    class="flex-1 bg-transparent border-0 border-b border-zinc-300 dark:border-zinc-600 focus:border-purple-500 focus:ring-0 text-sm font-semibold px-0 py-1"
                >
                <div class="flex gap-1">
                    <button type="button" @click="moveSection(sIdx, -1)" :disabled="sIdx === 0" class="p-1 rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 disabled:opacity-30" title="{{ __('Move Up') }}">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" /></svg>
                    </button>
                    <button type="button" @click="moveSection(sIdx, 1)" :disabled="sIdx === content.sections.length - 1" class="p-1 rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 disabled:opacity-30" title="{{ __('Move Down') }}">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <button type="button" @click="if(confirm('{{ __('Remove this section?') }}')) removeSection(sIdx)" class="p-1 rounded hover:bg-red-100 dark:hover:bg-red-900/30 text-red-600" title="{{ __('Remove') }}">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                    </button>
                </div>
            </div>

            {{-- Section Content --}}
            <div class="p-4 space-y-4">
                {{-- TipTap Editor --}}
                <div>
                    <label class="text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1 block">{{ __('Content') }}</label>
                    <x-mentorship.tiptap-editor
                        :content="''"
                        :placeholder="__('Write section content...')"
                        x-init="
                            $nextTick(() => {
                                const el = $el.querySelector('[x-ref=element]');
                                if (el && el.__tiptap_editor) {
                                    el.__tiptap_editor.commands.setContent(section.content || '');
                                }
                            })
                        "
                        x-on:tiptap-update.stop="section.content = $event.detail.content; sync()"
                    />
                </div>

                {{-- Media Items --}}
                <div x-show="section.media && section.media.length > 0">
                    <label class="text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-2 block">{{ __('Media') }}</label>
                    <div class="space-y-2">
                        <template x-for="(media, mIdx) in section.media" :key="mIdx">
                            <div class="flex items-center gap-3 p-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 text-sm">
                                {{-- Icon --}}
                                <span x-show="media.type === 'image'" class="text-green-600">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5a2.25 2.25 0 002.25-2.25V6a2.25 2.25 0 00-2.25-2.25H3.75A2.25 2.25 0 001.5 6v12.75c0 1.243 1.007 2.25 2.25 2.25z" /></svg>
                                </span>
                                <span x-show="media.type === 'video'" class="text-red-600">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15.91 11.672a.375.375 0 010 .656l-5.603 3.113a.375.375 0 01-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112z" /></svg>
                                </span>
                                <span x-show="media.type === 'document'" class="text-blue-600">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                                </span>

                                {{-- Name/URL --}}
                                <span class="flex-1 truncate" x-text="media.name || media.title || media.url"></span>

                                {{-- Caption input --}}
                                <input
                                    type="text"
                                    x-model="media.caption"
                                    @input="sync()"
                                    placeholder="{{ __('Caption (optional)') }}"
                                    class="w-40 text-xs bg-transparent border border-zinc-200 dark:border-zinc-600 rounded px-2 py-1"
                                    x-show="media.type !== 'video'"
                                >

                                {{-- Remove --}}
                                <button type="button" @click="removeMedia(sIdx, null, mIdx)" class="p-1 rounded hover:bg-red-100 dark:hover:bg-red-900/30 text-red-500">
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- AI Media Suggestions --}}
                <div x-show="section.media_suggestions && section.media_suggestions.length > 0">
                    <label class="text-xs font-medium text-amber-600 dark:text-amber-400 mb-2 block">{{ __('AI Suggested Media') }}</label>
                    <div class="space-y-2">
                        <template x-for="(suggestion, sgIdx) in section.media_suggestions" :key="sgIdx">
                            <div class="flex items-center gap-3 p-2 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-sm">
                                <span x-show="suggestion.type === 'video'" class="text-red-500">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15.91 11.672a.375.375 0 010 .656l-5.603 3.113a.375.375 0 01-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112z" /></svg>
                                </span>
                                <span x-show="suggestion.type === 'image'" class="text-green-500">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5a2.25 2.25 0 002.25-2.25V6a2.25 2.25 0 00-2.25-2.25H3.75A2.25 2.25 0 001.5 6v12.75c0 1.243 1.007 2.25 2.25 2.25z" /></svg>
                                </span>
                                <span class="flex-1 text-xs" x-text="suggestion.description || suggestion.search_term"></span>
                                <a x-show="suggestion.search_term" :href="'https://www.youtube.com/results?search_query=' + encodeURIComponent(suggestion.search_term)" target="_blank" class="text-xs text-purple-600 hover:underline">
                                    {{ __('Search YouTube') }}
                                </a>
                                <button type="button" @click="section.media_suggestions.splice(sgIdx, 1); sync()" class="p-1 text-zinc-400 hover:text-zinc-600">
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Add Media Buttons --}}
                <div class="flex flex-wrap gap-2">
                    <label class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-zinc-200 dark:border-zinc-600 hover:bg-zinc-50 dark:hover:bg-zinc-800 cursor-pointer transition">
                        <svg class="size-3.5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5a2.25 2.25 0 002.25-2.25V6a2.25 2.25 0 00-2.25-2.25H3.75A2.25 2.25 0 001.5 6v12.75c0 1.243 1.007 2.25 2.25 2.25z" /></svg>
                        {{ __('Image') }}
                        <input type="file" accept="image/jpeg,image/jpg,image/png,image/gif" class="hidden" @change="handleFileUpload($event, sIdx, null, 'image')">
                    </label>

                    <label class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-zinc-200 dark:border-zinc-600 hover:bg-zinc-50 dark:hover:bg-zinc-800 cursor-pointer transition">
                        <svg class="size-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                        {{ __('Document') }}
                        <input type="file" accept=".pdf,.doc,.docx" class="hidden" @change="handleFileUpload($event, sIdx, null, 'document')">
                    </label>

                    <button type="button" @click="addVideoUrl(sIdx, null)" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-zinc-200 dark:border-zinc-600 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                        <svg class="size-3.5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15.91 11.672a.375.375 0 010 .656l-5.603 3.113a.375.375 0 01-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112z" /></svg>
                        {{ __('Video URL') }}
                    </button>
                </div>

                {{-- Subsections --}}
                <div x-show="section.subsections && section.subsections.length > 0" class="ml-4 space-y-3 border-l-2 border-purple-200 dark:border-purple-800 pl-4">
                    <template x-for="(sub, subIdx) in section.subsections" :key="sub.id">
                        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                            {{-- Subsection Header --}}
                            <div class="bg-purple-50 dark:bg-purple-900/20 px-3 py-2 flex items-center gap-2">
                                <span class="text-xs font-semibold text-purple-400" x-text="(sIdx + 1) + '.' + (subIdx + 1)"></span>
                                <input
                                    type="text"
                                    x-model="sub.title"
                                    @input="sync()"
                                    placeholder="{{ __('Subsection Title') }}"
                                    class="flex-1 bg-transparent border-0 border-b border-purple-200 dark:border-purple-700 focus:border-purple-500 focus:ring-0 text-sm font-medium px-0 py-0.5"
                                >
                                <button type="button" @click="if(confirm('{{ __('Remove this subsection?') }}')) removeSubsection(sIdx, subIdx)" class="p-1 rounded hover:bg-red-100 dark:hover:bg-red-900/30 text-red-500">
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>

                            {{-- Subsection Content --}}
                            <div class="p-3 space-y-3">
                                <x-mentorship.tiptap-editor
                                    :content="''"
                                    :placeholder="__('Write subsection content...')"
                                    x-on:tiptap-update.stop="sub.content = $event.detail.content; sync()"
                                />

                                {{-- Subsection Media --}}
                                <div x-show="sub.media && sub.media.length > 0" class="space-y-1">
                                    <template x-for="(media, mIdx) in sub.media" :key="mIdx">
                                        <div class="flex items-center gap-2 p-1.5 rounded bg-zinc-50 dark:bg-zinc-800 text-xs">
                                            <span class="flex-1 truncate" x-text="media.name || media.title || media.url"></span>
                                            <button type="button" @click="removeMedia(sIdx, subIdx, mIdx)" class="text-red-500">
                                                <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>

                                {{-- Subsection Add Media --}}
                                <div class="flex flex-wrap gap-1.5">
                                    <label class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded border border-zinc-200 dark:border-zinc-600 hover:bg-zinc-50 dark:hover:bg-zinc-800 cursor-pointer">
                                        {{ __('Image') }}
                                        <input type="file" accept="image/jpeg,image/jpg,image/png,image/gif" class="hidden" @change="handleFileUpload($event, sIdx, subIdx, 'image')">
                                    </label>
                                    <label class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded border border-zinc-200 dark:border-zinc-600 hover:bg-zinc-50 dark:hover:bg-zinc-800 cursor-pointer">
                                        {{ __('Document') }}
                                        <input type="file" accept=".pdf,.doc,.docx" class="hidden" @change="handleFileUpload($event, sIdx, subIdx, 'document')">
                                    </label>
                                    <button type="button" @click="addVideoUrl(sIdx, subIdx)" class="px-2 py-1 text-xs rounded border border-zinc-200 dark:border-zinc-600 hover:bg-zinc-50 dark:hover:bg-zinc-800">
                                        {{ __('Video URL') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Add Subsection Button --}}
                <button type="button" @click="addSubsection(sIdx)" class="inline-flex items-center gap-1.5 text-xs text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 font-medium">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    {{ __('Add Subsection') }}
                </button>
            </div>
        </div>
    </template>

    {{-- Add Section Button --}}
    <button type="button" @click="addSection()" class="w-full py-3 border-2 border-dashed border-zinc-300 dark:border-zinc-600 rounded-xl text-sm font-medium text-zinc-500 dark:text-zinc-400 hover:border-purple-400 hover:text-purple-600 dark:hover:border-purple-500 dark:hover:text-purple-400 transition flex items-center justify-center gap-2">
        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        {{ __('Add Section') }}
    </button>

    {{-- Empty State --}}
    <div x-show="!content.sections || content.sections.length === 0" class="text-center py-8 text-zinc-400">
        <svg class="size-12 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
        <p class="text-sm">{{ __('Click "Add Section" to start building your lesson') }}</p>
    </div>
</div>

@script
<script>
Alpine.data('structuredEditor', (initialContent) => ({
    content: initialContent || { sections: [], metadata: {} },

    init() {
        if (!this.content || !this.content.sections) {
            this.content = { sections: [], metadata: {} };
        }
    },

    addSection() {
        if (!this.content.sections) this.content.sections = [];
        this.content.sections.push({
            id: crypto.randomUUID(),
            title: '',
            content: '',
            media: [],
            media_suggestions: [],
            subsections: [],
        });
        this.updateMetadata();
        this.sync();
    },

    removeSection(idx) {
        this.content.sections.splice(idx, 1);
        this.updateMetadata();
        this.sync();
    },

    moveSection(idx, direction) {
        const newIdx = idx + direction;
        if (newIdx < 0 || newIdx >= this.content.sections.length) return;
        const temp = this.content.sections[idx];
        this.content.sections.splice(idx, 1);
        this.content.sections.splice(newIdx, 0, temp);
        this.sync();
    },

    addSubsection(sIdx) {
        if (!this.content.sections[sIdx].subsections) {
            this.content.sections[sIdx].subsections = [];
        }
        this.content.sections[sIdx].subsections.push({
            id: crypto.randomUUID(),
            title: '',
            content: '',
            media: [],
            media_suggestions: [],
        });
        this.updateMetadata();
        this.sync();
    },

    removeSubsection(sIdx, subIdx) {
        this.content.sections[sIdx].subsections.splice(subIdx, 1);
        this.updateMetadata();
        this.sync();
    },

    addVideoUrl(sIdx, subIdx) {
        const url = prompt('Enter YouTube or Vimeo URL:');
        if (!url) return;

        const media = { type: 'video', url: url, title: 'Video' };
        if (subIdx !== null) {
            if (!this.content.sections[sIdx].subsections[subIdx].media) {
                this.content.sections[sIdx].subsections[subIdx].media = [];
            }
            this.content.sections[sIdx].subsections[subIdx].media.push(media);
        } else {
            if (!this.content.sections[sIdx].media) {
                this.content.sections[sIdx].media = [];
            }
            this.content.sections[sIdx].media.push(media);
        }
        this.updateMetadata();
        this.sync();
    },

    handleFileUpload(event, sIdx, subIdx, type) {
        const file = event.target.files[0];
        if (!file) return;

        // Upload via Livewire
        this.$wire.upload('pendingUpload', file, (uploadedFilename) => {
            // After upload, call the Livewire method to store permanently
            this.$wire.storeUploadedMedia(uploadedFilename, file.name, type).then((path) => {
                if (path) {
                    const media = { type: type, path: path, name: file.name, caption: '' };
                    if (subIdx !== null) {
                        if (!this.content.sections[sIdx].subsections[subIdx].media) {
                            this.content.sections[sIdx].subsections[subIdx].media = [];
                        }
                        this.content.sections[sIdx].subsections[subIdx].media.push(media);
                    } else {
                        if (!this.content.sections[sIdx].media) {
                            this.content.sections[sIdx].media = [];
                        }
                        this.content.sections[sIdx].media.push(media);
                    }
                    this.updateMetadata();
                    this.sync();
                }
            });
        }, () => {
            // Upload error
        }, (event) => {
            // Progress
        });

        // Reset input
        event.target.value = '';
    },

    removeMedia(sIdx, subIdx, mIdx) {
        if (subIdx !== null) {
            const media = this.content.sections[sIdx].subsections[subIdx].media[mIdx];
            if (media.path) {
                this.$wire.removeUploadedMedia(media.path);
            }
            this.content.sections[sIdx].subsections[subIdx].media.splice(mIdx, 1);
        } else {
            const media = this.content.sections[sIdx].media[mIdx];
            if (media.path) {
                this.$wire.removeUploadedMedia(media.path);
            }
            this.content.sections[sIdx].media.splice(mIdx, 1);
        }
        this.updateMetadata();
        this.sync();
    },

    updateMetadata() {
        let sections = this.content.sections || [];
        let subsectionCount = 0, videoCount = 0, imageCount = 0, documentCount = 0;

        sections.forEach(section => {
            subsectionCount += (section.subsections || []).length;
            (section.media || []).forEach(m => {
                if (m.type === 'video') videoCount++;
                if (m.type === 'image') imageCount++;
                if (m.type === 'document') documentCount++;
            });
            (section.subsections || []).forEach(sub => {
                (sub.media || []).forEach(m => {
                    if (m.type === 'video') videoCount++;
                    if (m.type === 'image') imageCount++;
                    if (m.type === 'document') documentCount++;
                });
            });
        });

        this.content.metadata = {
            section_count: sections.length,
            subsection_count: subsectionCount,
            video_count: videoCount,
            image_count: imageCount,
            document_count: documentCount,
        };
    },

    sync() {
        // Trigger Livewire sync via entangle
    },
}))
</script>
@endscript
