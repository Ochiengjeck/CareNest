<?php

use App\Concerns\AiSettingsValidationRules;
use App\Services\SettingsService;
use App\Services\AI\AiManager;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('AI Integration')]
class extends Component {
    use AiSettingsValidationRules;

    // Global
    public bool $ai_enabled = false;

    // Provider settings
    public string $groq_api_key = '';
    public string $groq_default_model = 'llama-3.1-70b-versatile';
    public bool $groq_configured = false;

    public string $gemini_api_key = '';
    public string $gemini_default_model = 'gemini-1.5-flash';
    public bool $gemini_configured = false;

    // Use case editing
    public ?string $editingUseCase = null;
    public array $useCase = [];

    // Connection test results
    public ?string $groqTestResult = null;
    public ?string $geminiTestResult = null;
    public ?bool $groqTestSuccess = null;
    public ?bool $geminiTestSuccess = null;

    // Chatbot settings
    public bool $chatbotEnabled = false;
    public string $chatbotProvider = 'groq';
    public string $chatbotModel = 'llama-3.3-70b-versatile';
    public string $chatbotSystemPrompt = '';

    public function mount(): void
    {
        $settings = app(SettingsService::class);

        $this->ai_enabled = (bool) $settings->get('ai_enabled', false);
        $this->groq_default_model = $settings->get('groq_default_model', 'llama-3.1-70b-versatile') ?? 'llama-3.1-70b-versatile';
        $this->gemini_default_model = $settings->get('gemini_default_model', 'gemini-1.5-flash') ?? 'gemini-1.5-flash';

        $this->groq_configured = !empty($settings->get('groq_api_key'));
        $this->gemini_configured = !empty($settings->get('gemini_api_key'));

        // Chatbot settings
        $this->chatbotEnabled = (bool) $settings->get('chatbot_enabled', false);
        $this->chatbotProvider = $settings->get('chatbot_provider', 'groq') ?? 'groq';
        $this->chatbotModel = $settings->get('chatbot_model', 'llama-3.3-70b-versatile') ?? 'llama-3.3-70b-versatile';
        $this->chatbotSystemPrompt = $settings->get('chatbot_system_prompt', '') ?? '';
    }

    #[Computed]
    public function useCases(): array
    {
        $manager = app(AiManager::class);
        $cases = $manager->getUseCases();

        foreach ($cases as $key => &$case) {
            $case['config'] = $manager->getUseCaseConfig($key);
        }

        return $cases;
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

    public function saveGlobalSettings(): void
    {
        $settings = app(SettingsService::class);
        $settings->set('ai_enabled', $this->ai_enabled ? '1' : '0', 'ai', 'boolean');
        $this->dispatch('global-ai-saved');
    }

    public function saveGroqSettings(): void
    {
        $this->validate($this->aiProviderRules('groq'));

        $settings = app(SettingsService::class);

        if (!empty($this->groq_api_key)) {
            $settings->set('groq_api_key', $this->groq_api_key, 'ai', 'string');
            $this->groq_configured = true;
            $this->groq_api_key = '';
        }

        $settings->set('groq_default_model', $this->groq_default_model, 'ai', 'string');
        $this->groqTestResult = null;
        $this->groqTestSuccess = null;
        $this->dispatch('groq-saved');
    }

    public function saveGeminiSettings(): void
    {
        $this->validate($this->aiProviderRules('gemini'));

        $settings = app(SettingsService::class);

        if (!empty($this->gemini_api_key)) {
            $settings->set('gemini_api_key', $this->gemini_api_key, 'ai', 'string');
            $this->gemini_configured = true;
            $this->gemini_api_key = '';
        }

        $settings->set('gemini_default_model', $this->gemini_default_model, 'ai', 'string');
        $this->geminiTestResult = null;
        $this->geminiTestSuccess = null;
        $this->dispatch('gemini-saved');
    }

    public function testGroqConnection(): void
    {
        $result = app(AiManager::class)->provider('groq')->testConnection();
        $this->groqTestSuccess = $result->success;
        $this->groqTestResult = $result->success
            ? __('Connected successfully') . " ({$result->responseTime}s)"
            : __('Failed') . ": {$result->error}";
    }

    public function testGeminiConnection(): void
    {
        $result = app(AiManager::class)->provider('gemini')->testConnection();
        $this->geminiTestSuccess = $result->success;
        $this->geminiTestResult = $result->success
            ? __('Connected successfully') . " ({$result->responseTime}s)"
            : __('Failed') . ": {$result->error}";
    }

    public function removeGroqKey(): void
    {
        $settings = app(SettingsService::class);
        $settings->set('groq_api_key', null, 'ai', 'string');
        $this->groq_configured = false;
        $this->groqTestResult = null;
        $this->groqTestSuccess = null;
    }

    public function removeGeminiKey(): void
    {
        $settings = app(SettingsService::class);
        $settings->set('gemini_api_key', null, 'ai', 'string');
        $this->gemini_configured = false;
        $this->geminiTestResult = null;
        $this->geminiTestSuccess = null;
    }

    public function editUseCase(string $key): void
    {
        $this->editingUseCase = $key;
        $this->useCase = app(AiManager::class)->getUseCaseConfig($key);
    }

    public function saveUseCase(): void
    {
        $this->validate($this->aiUseCaseRules());

        $settings = app(SettingsService::class);
        $settings->set(
            'ai_usecase_' . $this->editingUseCase,
            $this->useCase,
            'ai',
            'json'
        );

        $this->editingUseCase = null;
        $this->useCase = [];
        $this->dispatch('usecase-saved');
    }

    public function cancelEditUseCase(): void
    {
        $this->editingUseCase = null;
        $this->useCase = [];
    }

    public function modelsForProvider(string $provider): array
    {
        return match ($provider) {
            'groq' => $this->groqModels,
            'gemini' => $this->geminiModels,
            default => [],
        };
    }

    public function updatedChatbotProvider(): void
    {
        $this->chatbotModel = match ($this->chatbotProvider) {
            'groq' => 'llama-3.3-70b-versatile',
            'gemini' => 'gemini-2.0-flash',
            default => 'llama-3.3-70b-versatile',
        };
    }

    public function saveChatbotSettings(): void
    {
        $this->validate([
            'chatbotProvider' => ['required', 'in:groq,gemini'],
            'chatbotModel' => ['required', 'string', 'max:100'],
            'chatbotSystemPrompt' => ['nullable', 'string', 'max:5000'],
        ]);

        $settings = app(SettingsService::class);
        $settings->set('chatbot_enabled', $this->chatbotEnabled ? '1' : '0', 'ai', 'boolean');
        $settings->set('chatbot_provider', $this->chatbotProvider, 'ai', 'string');
        $settings->set('chatbot_model', $this->chatbotModel, 'ai', 'string');
        $settings->set('chatbot_system_prompt', $this->chatbotSystemPrompt, 'ai', 'string');

        $this->dispatch('chatbot-saved');
    }
}; ?>

<flux:main>
    <x-pages.admin.settings-layout
        :heading="__('AI Integration')"
        :subheading="__('Configure AI providers and use case settings')">

        <div class="space-y-8 max-w-3xl">
            {{-- Global AI Toggle --}}
            <flux:card>
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="sm">{{ __('AI Integration') }}</flux:heading>
                        <flux:subheading>{{ __('Enable or disable all AI features system-wide.') }}</flux:subheading>
                    </div>
                    <flux:switch wire:model.live="ai_enabled" wire:change="saveGlobalSettings" />
                </div>
            </flux:card>

            @if($this->ai_enabled)
                {{-- Groq Provider --}}
                <flux:card>
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <flux:heading size="sm">{{ __('Groq') }}</flux:heading>
                            @if($groq_configured)
                                <flux:badge size="sm" color="green">{{ __('Configured') }}</flux:badge>
                            @else
                                <flux:badge size="sm" color="amber">{{ __('Not configured') }}</flux:badge>
                            @endif
                        </div>
                        <flux:subheading class="text-xs">{{ __('Fastest inference — best for text generation') }}</flux:subheading>
                    </div>

                    <form wire:submit="saveGroqSettings" class="space-y-4">
                        <div>
                            <flux:input wire:model="groq_api_key" :label="__('API Key')" type="password"
                                :placeholder="$groq_configured ? '••••••••••••••••' : 'gsk_...'" />
                            @if($groq_configured)
                                <div class="mt-1">
                                    <flux:button variant="ghost" size="xs" wire:click="removeGroqKey" type="button" class="text-red-600 dark:text-red-400">
                                        {{ __('Remove key') }}
                                    </flux:button>
                                </div>
                            @endif
                        </div>

                        <flux:select wire:model="groq_default_model" :label="__('Default Model')">
                            @foreach($this->groqModels as $value => $label)
                                <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                            @endforeach
                        </flux:select>

                        <div class="flex items-center gap-4">
                            <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
                            <flux:button variant="ghost" type="button" wire:click="testGroqConnection" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="testGroqConnection">{{ __('Test Connection') }}</span>
                                <span wire:loading wire:target="testGroqConnection">{{ __('Testing...') }}</span>
                            </flux:button>
                            <x-action-message on="groq-saved">{{ __('Saved.') }}</x-action-message>
                        </div>

                        @if($groqTestResult)
                            <div class="text-sm {{ $groqTestSuccess ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $groqTestResult }}
                            </div>
                        @endif
                    </form>
                </flux:card>

                {{-- Gemini Provider --}}
                <flux:card>
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <flux:heading size="sm">{{ __('Google Gemini') }}</flux:heading>
                            @if($gemini_configured)
                                <flux:badge size="sm" color="green">{{ __('Configured') }}</flux:badge>
                            @else
                                <flux:badge size="sm" color="amber">{{ __('Not configured') }}</flux:badge>
                            @endif
                        </div>
                        <flux:subheading class="text-xs">{{ __('Multimodal — best for document analysis') }}</flux:subheading>
                    </div>

                    <form wire:submit="saveGeminiSettings" class="space-y-4">
                        <div>
                            <flux:input wire:model="gemini_api_key" :label="__('API Key')" type="password"
                                :placeholder="$gemini_configured ? '••••••••••••••••' : __('Enter Gemini API key')" />
                            @if($gemini_configured)
                                <div class="mt-1">
                                    <flux:button variant="ghost" size="xs" wire:click="removeGeminiKey" type="button" class="text-red-600 dark:text-red-400">
                                        {{ __('Remove key') }}
                                    </flux:button>
                                </div>
                            @endif
                        </div>

                        <flux:select wire:model="gemini_default_model" :label="__('Default Model')">
                            @foreach($this->geminiModels as $value => $label)
                                <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                            @endforeach
                        </flux:select>

                        <div class="flex items-center gap-4">
                            <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
                            <flux:button variant="ghost" type="button" wire:click="testGeminiConnection" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="testGeminiConnection">{{ __('Test Connection') }}</span>
                                <span wire:loading wire:target="testGeminiConnection">{{ __('Testing...') }}</span>
                            </flux:button>
                            <x-action-message on="gemini-saved">{{ __('Saved.') }}</x-action-message>
                        </div>

                        @if($geminiTestResult)
                            <div class="text-sm {{ $geminiTestSuccess ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $geminiTestResult }}
                            </div>
                        @endif
                    </form>
                </flux:card>

                {{-- AI Use Cases --}}
                <div>
                    <flux:heading size="lg">{{ __('AI Use Cases') }}</flux:heading>
                    <flux:subheading class="mb-4">{{ __('Configure how AI is used for each feature in the system.') }}</flux:subheading>
                </div>

                @foreach($this->useCases as $key => $case)
                    <flux:card>
                        @if($editingUseCase === $key)
                            {{-- Edit Mode --}}
                            <form wire:submit="saveUseCase" class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <flux:heading size="sm">{{ $case['label'] }}</flux:heading>
                                    <flux:switch wire:model="useCase.enabled" />
                                </div>

                                <flux:subheading>{{ $case['description'] }}</flux:subheading>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <flux:select wire:model.live="useCase.provider" :label="__('Provider')">
                                        <flux:select.option value="groq">Groq</flux:select.option>
                                        <flux:select.option value="gemini">Google Gemini</flux:select.option>
                                    </flux:select>

                                    <flux:select wire:model="useCase.model" :label="__('Model')">
                                        @foreach($this->modelsForProvider($useCase['provider'] ?? 'groq') as $value => $label)
                                            <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <flux:input wire:model="useCase.temperature" :label="__('Temperature')" type="number" min="0" max="2" step="0.1" />
                                        <flux:subheading class="mt-1 text-xs">{{ __('Lower = more focused, Higher = more creative') }}</flux:subheading>
                                    </div>
                                    <div>
                                        <flux:input wire:model="useCase.max_tokens" :label="__('Max Tokens')" type="number" min="100" max="32768" step="100" />
                                        <flux:subheading class="mt-1 text-xs">{{ __('Maximum length of AI response') }}</flux:subheading>
                                    </div>
                                </div>

                                <flux:textarea wire:model="useCase.system_prompt" :label="__('System Prompt')" rows="4"
                                    :placeholder="__('Instructions that define how the AI should behave for this use case...')" />

                                <div class="flex items-center gap-4">
                                    <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
                                    <flux:button variant="ghost" type="button" wire:click="cancelEditUseCase">{{ __('Cancel') }}</flux:button>
                                    <x-action-message on="usecase-saved">{{ __('Saved.') }}</x-action-message>
                                </div>
                            </form>
                        @else
                            {{-- Summary Mode --}}
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <flux:icon :name="$case['icon']" class="size-5 text-zinc-500" />
                                    <div>
                                        <flux:heading size="sm">{{ $case['label'] }}</flux:heading>
                                        <flux:subheading class="text-xs">{{ $case['description'] }}</flux:subheading>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    @if($case['config']['enabled'] ?? false)
                                        <flux:badge size="sm" color="green">{{ __('Enabled') }}</flux:badge>
                                    @else
                                        <flux:badge size="sm" color="zinc">{{ __('Disabled') }}</flux:badge>
                                    @endif
                                    <flux:badge size="sm">{{ ucfirst($case['config']['provider'] ?? 'groq') }}</flux:badge>
                                    <flux:button variant="ghost" size="sm" wire:click="editUseCase('{{ $key }}')" icon="pencil">
                                        {{ __('Configure') }}
                                    </flux:button>
                                </div>
                            </div>
                        @endif
                    </flux:card>
                @endforeach

                {{-- Chatbot Configuration --}}
                <div class="pt-4">
                    <flux:heading size="lg">{{ __('Chatbot Configuration') }}</flux:heading>
                    <flux:subheading class="mb-4">{{ __('Configure the floating AI assistant available to all users.') }}</flux:subheading>
                </div>

                <flux:card>
                    <form wire:submit="saveChatbotSettings" class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <flux:heading size="sm">{{ __('Enable Chatbot') }}</flux:heading>
                                <flux:subheading>{{ __('Show floating chat assistant to all authenticated users.') }}</flux:subheading>
                            </div>
                            <flux:switch wire:model="chatbotEnabled" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <flux:select wire:model.live="chatbotProvider" :label="__('Provider')">
                                <flux:select.option value="groq">Groq {{ $groq_configured ? '' : '(Not configured)' }}</flux:select.option>
                                <flux:select.option value="gemini">Google Gemini {{ $gemini_configured ? '' : '(Not configured)' }}</flux:select.option>
                            </flux:select>

                            <flux:select wire:model="chatbotModel" :label="__('Model')">
                                @foreach($this->modelsForProvider($chatbotProvider) as $value => $label)
                                    <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>

                        <flux:textarea
                            wire:model="chatbotSystemPrompt"
                            :label="__('System Prompt')"
                            rows="3"
                            :placeholder="__('You are a helpful assistant for CareNest, a care home management system. Help users with questions about residents, care plans, medications, and daily operations.')"
                        />
                        <flux:subheading class="text-xs">{{ __('Define the chatbot\'s personality and context. Leave empty for default behavior.') }}</flux:subheading>

                        <div class="flex items-center gap-4">
                            <flux:button variant="primary" type="submit">{{ __('Save Chatbot Settings') }}</flux:button>
                            <x-action-message on="chatbot-saved">{{ __('Saved.') }}</x-action-message>
                        </div>
                    </form>
                </flux:card>
            @else
                <flux:card>
                    <div class="text-center py-8">
                        <flux:icon name="cpu-chip" class="mx-auto size-12 text-zinc-400" />
                        <flux:heading size="sm" class="mt-4">{{ __('AI Integration Disabled') }}</flux:heading>
                        <flux:subheading class="mt-2">{{ __('Enable AI integration above to configure providers and use cases.') }}</flux:subheading>
                    </div>
                </flux:card>
            @endif
        </div>
    </x-pages.admin.settings-layout>
</flux:main>
