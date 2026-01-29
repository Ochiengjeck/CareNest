<?php

use App\Services\AI\AiManager;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('AI Chatbot')]
class extends Component {
    public string $testProvider = 'groq';
    public string $testModel = 'llama-3.3-70b-versatile';
    public string $testMessage = '';
    public array $chatHistory = [];
    public bool $isSending = false;

    #[Computed]
    public function aiEnabled(): bool
    {
        return app(AiManager::class)->isEnabled();
    }

    #[Computed]
    public function groqModels(): array
    {
        return app(AiManager::class)->provider('groq')->getAvailableModels();
    }

    #[Computed]
    public function geminiModels(): array
    {
        return app(AiManager::class)->provider('gemini')->getAvailableModels();
    }

    #[Computed]
    public function groqConfigured(): bool
    {
        return app(AiManager::class)->provider('groq')->isConfigured();
    }

    #[Computed]
    public function geminiConfigured(): bool
    {
        return app(AiManager::class)->provider('gemini')->isConfigured();
    }

    public function updatedTestProvider(): void
    {
        $this->testModel = match ($this->testProvider) {
            'groq' => 'llama-3.3-70b-versatile',
            'gemini' => 'gemini-2.0-flash',
            default => '',
        };
    }

    public function sendMessage(): void
    {
        if (empty(trim($this->testMessage))) {
            return;
        }

        $this->chatHistory[] = [
            'role' => 'user',
            'content' => $this->testMessage,
        ];

        $manager = app(AiManager::class);
        $provider = $manager->provider($this->testProvider);

        // Build conversation messages
        $messages = [];
        foreach ($this->chatHistory as $msg) {
            if ($msg['role'] === 'error') {
                continue;
            }
            $messages[] = [
                'role' => $msg['role'],
                'content' => $msg['content'],
            ];
        }

        $result = $provider->chat($messages, [
            'model' => $this->testModel,
            'temperature' => 0.7,
            'max_tokens' => 1024,
        ]);

        if ($result->success) {
            $this->chatHistory[] = [
                'role' => 'assistant',
                'content' => $result->content,
                'model' => $result->model,
                'tokens' => ($result->promptTokens ?? 0) + ($result->completionTokens ?? 0),
                'time' => $result->responseTime,
            ];
        } else {
            $this->chatHistory[] = [
                'role' => 'error',
                'content' => $result->error,
            ];
        }

        $this->testMessage = '';

        $this->dispatch('message-sent');
    }

    public function clearChat(): void
    {
        $this->chatHistory = [];
    }
}; ?>

<flux:main>
    <x-pages.admin.settings-layout
        :heading="__('AI Chatbot')"
        :subheading="__('Test AI providers with a live chat interface')">

        @if(!$this->aiEnabled)
            <flux:card>
                <div class="text-center py-8">
                    <flux:icon name="cpu-chip" class="mx-auto size-12 text-zinc-400" />
                    <flux:heading size="sm" class="mt-4">{{ __('AI Integration Disabled') }}</flux:heading>
                    <flux:subheading class="mt-2">
                        {{ __('Enable AI integration in') }}
                        <flux:link :href="route('admin.settings.ai')" wire:navigate>{{ __('AI settings') }}</flux:link>
                        {{ __('to use the chatbot.') }}
                    </flux:subheading>
                </div>
            </flux:card>
        @else
            <div class="max-w-3xl">
                {{-- Provider & Model Selection --}}
                <flux:card class="mb-4">
                    <div class="flex flex-wrap items-end gap-4">
                        <div class="flex-1 min-w-[150px]">
                            <flux:select wire:model.live="testProvider" :label="__('Provider')">
                                <flux:select.option value="groq">
                                    Groq {{ !$this->groqConfigured ? '(not configured)' : '' }}
                                </flux:select.option>
                                <flux:select.option value="gemini">
                                    Google Gemini {{ !$this->geminiConfigured ? '(not configured)' : '' }}
                                </flux:select.option>
                            </flux:select>
                        </div>

                        <div class="flex-1 min-w-[150px]">
                            <flux:select wire:model="testModel" :label="__('Model')">
                                @if($testProvider === 'groq')
                                    @foreach($this->groqModels as $value => $label)
                                        <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                                    @endforeach
                                @else
                                    @foreach($this->geminiModels as $value => $label)
                                        <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                                    @endforeach
                                @endif
                            </flux:select>
                        </div>

                        <flux:button variant="ghost" wire:click="clearChat" icon="trash">
                            {{ __('Clear') }}
                        </flux:button>
                    </div>
                </flux:card>

                {{-- Chat Messages --}}
                <flux:card class="mb-4">
                    <div class="space-y-4 min-h-[300px] max-h-[500px] overflow-y-auto" id="chat-messages">
                        @forelse($chatHistory as $index => $msg)
                            @if($msg['role'] === 'user')
                                <div class="flex justify-end">
                                    <div class="max-w-[80%] rounded-lg bg-blue-600 px-4 py-2 text-white">
                                        <p class="text-sm whitespace-pre-wrap">{{ $msg['content'] }}</p>
                                    </div>
                                </div>
                            @elseif($msg['role'] === 'assistant')
                                <div class="flex justify-start">
                                    <div class="max-w-[80%] rounded-lg bg-zinc-100 px-4 py-2 dark:bg-zinc-800">
                                        <p class="text-sm whitespace-pre-wrap">{{ $msg['content'] }}</p>
                                        @if(isset($msg['model']))
                                            <div class="mt-2 flex flex-wrap gap-2 text-xs text-zinc-400">
                                                <span>{{ $msg['model'] }}</span>
                                                @if(isset($msg['tokens']))
                                                    <span>&middot; {{ $msg['tokens'] }} tokens</span>
                                                @endif
                                                @if(isset($msg['time']))
                                                    <span>&middot; {{ $msg['time'] }}s</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @elseif($msg['role'] === 'error')
                                <div class="flex justify-start">
                                    <div class="max-w-[80%] rounded-lg border border-red-200 bg-red-50 px-4 py-2 dark:border-red-800 dark:bg-red-900/20">
                                        <p class="text-sm text-red-600 dark:text-red-400">{{ $msg['content'] }}</p>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <div class="flex items-center justify-center h-[300px] text-zinc-400">
                                <div class="text-center">
                                    <flux:icon name="chat-bubble-left-right" class="mx-auto size-10 mb-2" />
                                    <p class="text-sm">{{ __('Send a message to start testing the AI.') }}</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </flux:card>

                {{-- Input --}}
                <form wire:submit="sendMessage" class="flex gap-3">
                    <div class="flex-1">
                        <flux:input wire:model="testMessage" :placeholder="__('Type a message...')"
                            wire:keydown.enter.prevent="sendMessage" autofocus />
                    </div>
                    <flux:button variant="primary" type="submit" icon="paper-airplane"
                        wire:loading.attr="disabled" wire:target="sendMessage">
                        <span wire:loading.remove wire:target="sendMessage">{{ __('Send') }}</span>
                        <span wire:loading wire:target="sendMessage">{{ __('Sending...') }}</span>
                    </flux:button>
                </form>
            </div>
        @endif
    </x-pages.admin.settings-layout>
</flux:main>

@script
<script>
    $wire.on('message-sent', () => {
        setTimeout(() => {
            const el = document.getElementById('chat-messages');
            if (el) el.scrollTop = el.scrollHeight;
        }, 100);
    });
</script>
@endscript
