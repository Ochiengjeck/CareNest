@php
    $aiEnabled = system_setting('ai_enabled', false);
    $mentorChatEnabled = system_setting('ai_usecase_mentorship_chat.enabled', false);
@endphp

@auth
@if($aiEnabled && $mentorChatEnabled)
<div
    x-data="{
        open: false,
        message: '',
        messages: [],
        isTyping: false,
        csrfToken: '{{ csrf_token() }}',
        topicContext: null,

        init() {
            this.open = sessionStorage.getItem('mentor-chat-open') === 'true';
            const saved = sessionStorage.getItem('mentor-chat-messages');
            if (saved) {
                try { this.messages = JSON.parse(saved); } catch (e) {}
            }
        },

        toggle() {
            this.open = !this.open;
            sessionStorage.setItem('mentor-chat-open', this.open);
            if (this.open) {
                this.$nextTick(() => {
                    this.$refs.input?.focus();
                    this.scrollToBottom();
                });
            }
        },

        close() {
            this.open = false;
            sessionStorage.setItem('mentor-chat-open', 'false');
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const el = this.$refs.messages;
                if (el) el.scrollTop = el.scrollHeight;
            });
        },

        saveMessages() {
            sessionStorage.setItem('mentor-chat-messages', JSON.stringify(this.messages));
        },

        async sendMessage() {
            const text = this.message.trim();
            if (!text || this.isTyping) return;

            this.messages.push({ role: 'user', content: text });
            this.message = '';
            this.isTyping = true;
            this.saveMessages();
            this.scrollToBottom();

            try {
                const response = await fetch('{{ route('mentorship.ai-mentor.send') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        message: text,
                        history: this.messages,
                        topic_context: this.topicContext,
                    }),
                });

                const data = await response.json();
                this.isTyping = false;

                this.messages.push({
                    role: data.success ? 'assistant' : 'error',
                    content: data.content || 'Sorry, something went wrong.',
                });
            } catch (e) {
                this.isTyping = false;
                this.messages.push({
                    role: 'error',
                    content: 'Sorry, something went wrong. Please try again.',
                });
            }

            this.saveMessages();
            this.scrollToBottom();
            this.$nextTick(() => this.$refs.input?.focus());
        },

        clearChat() {
            this.messages = [];
            sessionStorage.removeItem('mentor-chat-messages');
        },
    }"
    class="fixed bottom-6 right-6 z-50"
>
    {{-- Floating Button - Purple/Indigo Theme --}}
    <button
        @click="toggle()"
        type="button"
        class="flex items-center justify-center w-14 h-14 rounded-full text-white shadow-lg transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 bg-gradient-to-br from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700"
    >
        <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M12 14l9-5-9-5-9 5 9 5z" />
            <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
        </svg>
        <svg x-show="open" x-cloak xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    {{-- Chat Panel --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        class="absolute bottom-20 right-0 w-80 sm:w-96 bg-white dark:bg-zinc-800 rounded-2xl shadow-2xl border border-zinc-200 dark:border-zinc-700 overflow-hidden"
    >
        {{-- Header - Purple Gradient --}}
        <div class="flex items-center justify-between px-4 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                <span class="font-medium text-sm">{{ __('AI Mentor') }}</span>
            </div>
            <div class="flex items-center gap-1">
                <button
                    type="button"
                    @click="clearChat()"
                    class="p-1.5 rounded-lg transition-colors hover:bg-white/10"
                    title="{{ __('Clear chat') }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
                <button
                    type="button"
                    @click="close()"
                    class="p-1.5 rounded-lg transition-colors hover:bg-white/10"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Messages --}}
        <div x-ref="messages" class="h-80 overflow-y-auto p-4 space-y-3 bg-zinc-50 dark:bg-zinc-900">
            {{-- Empty State --}}
            <template x-if="messages.length === 0 && !isTyping">
                <div class="flex flex-col items-center justify-center h-full text-center text-zinc-500 dark:text-zinc-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mb-3 opacity-50 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                    </svg>
                    <p class="text-sm">{{ __('Hello! I\'m your AI Mentor.') }}</p>
                    <p class="text-xs mt-1 opacity-75">{{ __('Ask me about DBT, clinical skills, or mentorship topics.') }}</p>
                </div>
            </template>

            {{-- Message Bubbles --}}
            <template x-for="(msg, i) in messages" :key="i">
                <div>
                    {{-- User Message --}}
                    <template x-if="msg.role === 'user'">
                        <div class="flex justify-end">
                            <div class="max-w-[85%] bg-gradient-to-br from-purple-600 to-indigo-600 text-white rounded-2xl rounded-br-md px-4 py-2 shadow-sm">
                                <p class="text-sm whitespace-pre-wrap break-words" x-text="msg.content"></p>
                            </div>
                        </div>
                    </template>

                    {{-- Assistant Message --}}
                    <template x-if="msg.role === 'assistant'">
                        <div class="flex justify-start">
                            <div class="max-w-[85%] bg-white dark:bg-zinc-800 border border-purple-200 dark:border-purple-800 rounded-2xl rounded-bl-md px-4 py-2 shadow-sm">
                                <p class="text-sm text-zinc-800 dark:text-zinc-200 whitespace-pre-wrap break-words" x-text="msg.content"></p>
                            </div>
                        </div>
                    </template>

                    {{-- Error Message --}}
                    <template x-if="msg.role === 'error'">
                        <div class="flex justify-start">
                            <div class="max-w-[85%] bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl rounded-bl-md px-4 py-2">
                                <p class="text-sm text-red-600 dark:text-red-400" x-text="msg.content"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Typing Indicator --}}
            <div x-show="isTyping" x-cloak class="flex justify-start">
                <div class="bg-white dark:bg-zinc-800 border border-purple-200 dark:border-purple-800 rounded-2xl rounded-bl-md px-4 py-3 shadow-sm">
                    <div class="flex items-center gap-1">
                        <div class="w-2 h-2 bg-purple-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                        <div class="w-2 h-2 bg-purple-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                        <div class="w-2 h-2 bg-purple-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Input --}}
        <div class="p-3 border-t border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
            <div class="flex items-center gap-2">
                <input
                    x-ref="input"
                    type="text"
                    x-model="message"
                    @keydown.enter.prevent="sendMessage()"
                    :disabled="isTyping"
                    placeholder="{{ __('Ask your mentor...') }}"
                    class="flex-1 px-4 py-2 text-sm border border-zinc-300 dark:border-zinc-600 rounded-full bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent disabled:opacity-50"
                />
                <button
                    type="button"
                    @click="sendMessage()"
                    :disabled="isTyping"
                    class="flex items-center justify-center w-10 h-10 text-white rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 bg-gradient-to-br from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 disabled:opacity-50"
                >
                    <svg x-show="!isTyping" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    <svg x-show="isTyping" x-cloak class="animate-spin w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
@endif
@endauth
