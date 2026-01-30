<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

<style>
    .pcb-root { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999; font-family: ui-sans-serif, system-ui, sans-serif; }
    .pcb-btn { display: flex; align-items: center; justify-content: center; width: 3.5rem; height: 3.5rem; border-radius: 9999px; background: #2563eb; color: #fff; border: none; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: all 0.2s; }
    .pcb-btn:hover { background: #1d4ed8; transform: scale(1.05); }
    .pcb-btn svg { width: 1.5rem; height: 1.5rem; }
    .pcb-panel { position: absolute; bottom: 5rem; right: 0; width: 22rem; background: #fff; border-radius: 1rem; box-shadow: 0 8px 30px rgba(0,0,0,0.12); border: 1px solid #e4e4e7; overflow: hidden; }
    .pcb-header { display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 1rem; background: #2563eb; color: #fff; }
    .pcb-header-title { display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; font-weight: 500; }
    .pcb-dot { width: 0.5rem; height: 0.5rem; background: #4ade80; border-radius: 9999px; animation: pcb-pulse 2s infinite; }
    .pcb-header-actions { display: flex; gap: 0.25rem; }
    .pcb-header-btn { padding: 0.375rem; background: none; border: none; color: #fff; cursor: pointer; border-radius: 0.5rem; transition: background 0.15s; }
    .pcb-header-btn:hover { background: #1d4ed8; }
    .pcb-header-btn svg { width: 1rem; height: 1rem; }
    .pcb-messages { height: 20rem; overflow-y: auto; padding: 1rem; background: #fafafa; display: flex; flex-direction: column; gap: 0.75rem; }
    .pcb-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; text-align: center; color: #71717a; }
    .pcb-empty svg { width: 3rem; height: 3rem; margin-bottom: 0.75rem; opacity: 0.5; }
    .pcb-empty p { font-size: 0.875rem; }
    .pcb-empty .pcb-sub { font-size: 0.75rem; margin-top: 0.25rem; opacity: 0.75; }
    .pcb-msg-row { display: flex; }
    .pcb-msg-row.user { justify-content: flex-end; }
    .pcb-msg-row.bot { justify-content: flex-start; }
    .pcb-bubble { max-width: 85%; padding: 0.5rem 1rem; font-size: 0.875rem; line-height: 1.4; white-space: pre-wrap; word-break: break-word; }
    .pcb-bubble.user { background: #2563eb; color: #fff; border-radius: 1rem 1rem 0.25rem 1rem; }
    .pcb-bubble.bot { background: #fff; color: #27272a; border: 1px solid #e4e4e7; border-radius: 1rem 1rem 1rem 0.25rem; }
    .pcb-bubble.error { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; border-radius: 1rem 1rem 1rem 0.25rem; }
    .pcb-typing { display: flex; justify-content: flex-start; }
    .pcb-typing-inner { background: #fff; border: 1px solid #e4e4e7; border-radius: 1rem; padding: 0.75rem 1rem; display: flex; gap: 0.25rem; }
    .pcb-typing-dot { width: 0.5rem; height: 0.5rem; background: #a1a1aa; border-radius: 9999px; animation: pcb-bounce 1.4s infinite; }
    .pcb-typing-dot:nth-child(2) { animation-delay: 0.15s; }
    .pcb-typing-dot:nth-child(3) { animation-delay: 0.3s; }
    .pcb-input-area { padding: 0.75rem; border-top: 1px solid #e4e4e7; background: #fff; display: flex; align-items: center; gap: 0.5rem; }
    .pcb-input { flex: 1; padding: 0.5rem 1rem; font-size: 0.875rem; border: 1px solid #d4d4d8; border-radius: 9999px; background: #fafafa; outline: none; font-family: inherit; }
    .pcb-input:focus { border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,0.2); }
    .pcb-input:disabled { opacity: 0.5; }
    .pcb-send { display: flex; align-items: center; justify-content: center; width: 2.5rem; height: 2.5rem; border-radius: 9999px; background: #2563eb; color: #fff; border: none; cursor: pointer; transition: background 0.15s; }
    .pcb-send:hover { background: #1d4ed8; }
    .pcb-send:disabled { background: #93c5fd; cursor: not-allowed; }
    .pcb-send svg { width: 1.25rem; height: 1.25rem; }
    .pcb-hidden { display: none !important; }
    .pcb-fade-in { animation: pcb-fade-in 0.2s ease-out; }
    .pcb-fade-out { animation: pcb-fade-out 0.15s ease-in forwards; }
    @keyframes pcb-pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
    @keyframes pcb-bounce { 0%, 80%, 100% { transform: translateY(0); } 40% { transform: translateY(-0.375rem); } }
    @keyframes pcb-fade-in { from { opacity: 0; transform: translateY(0.5rem) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }
    @keyframes pcb-fade-out { from { opacity: 1; transform: translateY(0) scale(1); } to { opacity: 0; transform: translateY(0.5rem) scale(0.95); } }
    @keyframes pcb-spin { to { transform: rotate(360deg); } }
    .pcb-spinner { animation: pcb-spin 1s linear infinite; }

    @media (max-width: 640px) { .pcb-panel { width: 18rem; } }
    @media (prefers-color-scheme: dark) {
        .pcb-panel { background: #27272a; border-color: #3f3f46; }
        .pcb-messages { background: #18181b; }
        .pcb-empty { color: #a1a1aa; }
        .pcb-bubble.bot { background: #27272a; color: #e4e4e7; border-color: #3f3f46; }
        .pcb-bubble.error { background: rgba(153,27,27,0.2); color: #f87171; border-color: #7f1d1d; }
        .pcb-typing-inner { background: #27272a; border-color: #3f3f46; }
        .pcb-input-area { background: #27272a; border-color: #3f3f46; }
        .pcb-input { background: #18181b; border-color: #3f3f46; color: #e4e4e7; }
    }
</style>

<div class="pcb-root"
    x-data="{
        open: false,
        message: '',
        messages: [],
        isTyping: false,

        init() {
            this.open = sessionStorage.getItem('pcb-open') === 'true';
            const saved = sessionStorage.getItem('pcb-messages');
            if (saved) {
                try { this.messages = JSON.parse(saved); } catch (e) {}
            }
        },

        toggle() {
            this.open = !this.open;
            sessionStorage.setItem('pcb-open', this.open);
            if (this.open) {
                this.$nextTick(() => {
                    this.$refs.input?.focus();
                    this.scrollToBottom();
                });
            }
        },

        close() {
            this.open = false;
            sessionStorage.setItem('pcb-open', 'false');
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const el = this.$refs.messages;
                if (el) el.scrollTop = el.scrollHeight;
            });
        },

        saveMessages() {
            sessionStorage.setItem('pcb-messages', JSON.stringify(this.messages));
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
                const token = document.querySelector('meta[name=csrf-token]')?.content
                    || '{{ csrf_token() }}';

                const response = await fetch('{{ route('public-chatbot.send') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        message: text,
                        history: this.messages,
                    }),
                });

                if (response.status === 429) {
                    this.isTyping = false;
                    this.messages.push({ role: 'error', content: 'You are sending messages too quickly. Please wait a moment.' });
                    this.saveMessages();
                    this.scrollToBottom();
                    return;
                }

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
            sessionStorage.removeItem('pcb-messages');
        },
    }"
>
    {{-- Floating Button --}}
    <button @click="toggle()" type="button" class="pcb-btn">
        <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>
        <svg x-show="open" style="display:none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    {{-- Chat Panel --}}
    <div x-show="open" x-transition style="display:none" class="pcb-panel pcb-fade-in">
        {{-- Header --}}
        <div class="pcb-header">
            <div class="pcb-header-title">
                <div class="pcb-dot"></div>
                <span>{{ system_setting('system_name', 'CareNest') }} Assistant</span>
            </div>
            <div class="pcb-header-actions">
                <button type="button" @click="clearChat()" class="pcb-header-btn" title="Clear chat">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
                <button type="button" @click="close()" class="pcb-header-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Messages --}}
        <div x-ref="messages" class="pcb-messages">
            {{-- Empty State --}}
            <template x-if="messages.length === 0 && !isTyping">
                <div class="pcb-empty">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <p>Hi! Welcome to {{ system_setting('system_name', 'CareNest') }}.</p>
                    <p class="pcb-sub">Ask about our services, visiting hours, or anything about our care home.</p>
                </div>
            </template>

            {{-- Message Bubbles --}}
            <template x-for="(msg, i) in messages" :key="i">
                <div>
                    <template x-if="msg.role === 'user'">
                        <div class="pcb-msg-row user">
                            <div class="pcb-bubble user" x-text="msg.content"></div>
                        </div>
                    </template>
                    <template x-if="msg.role === 'assistant'">
                        <div class="pcb-msg-row bot">
                            <div class="pcb-bubble bot" x-text="msg.content"></div>
                        </div>
                    </template>
                    <template x-if="msg.role === 'error'">
                        <div class="pcb-msg-row bot">
                            <div class="pcb-bubble error" x-text="msg.content"></div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Typing Indicator --}}
            <div x-show="isTyping" style="display:none" class="pcb-typing">
                <div class="pcb-typing-inner">
                    <div class="pcb-typing-dot"></div>
                    <div class="pcb-typing-dot"></div>
                    <div class="pcb-typing-dot"></div>
                </div>
            </div>
        </div>

        {{-- Input --}}
        <div class="pcb-input-area">
            <input
                x-ref="input"
                type="text"
                x-model="message"
                @keydown.enter.prevent="sendMessage()"
                :disabled="isTyping"
                placeholder="Type a message..."
                class="pcb-input"
            />
            <button type="button" @click="sendMessage()" :disabled="isTyping" class="pcb-send">
                <svg x-show="!isTyping" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
                <svg x-show="isTyping" style="display:none" class="pcb-spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle opacity="0.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path opacity="0.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </button>
        </div>
    </div>
</div>
