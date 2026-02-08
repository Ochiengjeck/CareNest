<?php

use App\Services\SettingsService;
use App\Services\AI\AiManager;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts.mentorship')]
#[Title('AI Settings')]
class extends Component {

    // Use case settings
    public array $lessonGeneration = [];
    public array $mentorChat = [];

    // Test results
    public ?string $lessonTestResult = null;
    public ?bool $lessonTestSuccess = null;
    public ?string $chatTestResult = null;
    public ?bool $chatTestSuccess = null;

    public function mount(): void
    {
        $manager = app(AiManager::class);

        $this->lessonGeneration = $manager->getUseCaseConfig('mentorship_lesson_generation');
        $this->mentorChat = $manager->getUseCaseConfig('mentorship_chat');

        // Set defaults if not configured
        $this->lessonGeneration['enabled'] = $this->lessonGeneration['enabled'] ?? false;
        $this->lessonGeneration['provider'] = $this->lessonGeneration['provider'] ?? 'groq';
        $this->lessonGeneration['model'] = $this->lessonGeneration['model'] ?? 'llama-3.3-70b-versatile';
        $this->lessonGeneration['temperature'] = $this->lessonGeneration['temperature'] ?? 0.7;
        $this->lessonGeneration['max_tokens'] = $this->lessonGeneration['max_tokens'] ?? 2048;
        $this->lessonGeneration['system_prompt'] = $this->lessonGeneration['system_prompt'] ?? '';

        $this->mentorChat['enabled'] = $this->mentorChat['enabled'] ?? false;
        $this->mentorChat['provider'] = $this->mentorChat['provider'] ?? 'groq';
        $this->mentorChat['model'] = $this->mentorChat['model'] ?? 'llama-3.3-70b-versatile';
        $this->mentorChat['temperature'] = $this->mentorChat['temperature'] ?? 0.7;
        $this->mentorChat['max_tokens'] = $this->mentorChat['max_tokens'] ?? 1024;
        $this->mentorChat['system_prompt'] = $this->mentorChat['system_prompt'] ?? '';
    }

    #[Computed]
    public function aiEnabled(): bool
    {
        return app(AiManager::class)->isEnabled();
    }

    #[Computed]
    public function groqConfigured(): bool
    {
        return app(AiManager::class)->isConfigured('groq');
    }

    #[Computed]
    public function geminiConfigured(): bool
    {
        return app(AiManager::class)->isConfigured('gemini');
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

    public function saveLessonGeneration(): void
    {
        $settings = app(SettingsService::class);
        $settings->set('ai_usecase_mentorship_lesson_generation', $this->lessonGeneration, 'ai', 'json');

        $this->lessonTestResult = null;
        $this->lessonTestSuccess = null;
        $this->dispatch('lesson-settings-saved');
    }

    public function saveMentorChat(): void
    {
        $settings = app(SettingsService::class);
        $settings->set('ai_usecase_mentorship_chat', $this->mentorChat, 'ai', 'json');

        $this->chatTestResult = null;
        $this->chatTestSuccess = null;
        $this->dispatch('chat-settings-saved');
    }

    public function testLessonConnection(): void
    {
        $provider = $this->lessonGeneration['provider'];
        $result = app(AiManager::class)->provider($provider)->testConnection();

        $this->lessonTestSuccess = $result->success;
        $this->lessonTestResult = $result->success
            ? __('Connected successfully') . " ({$result->responseTime}s)"
            : __('Failed') . ": {$result->error}";
    }

    public function testChatConnection(): void
    {
        $provider = $this->mentorChat['provider'];
        $result = app(AiManager::class)->provider($provider)->testConnection();

        $this->chatTestSuccess = $result->success;
        $this->chatTestResult = $result->success
            ? __('Connected successfully') . " ({$result->responseTime}s)"
            : __('Failed') . ": {$result->error}";
    }
}; ?>

<flux:main>
    <div class="space-y-6 max-w-4xl">
        {{-- Header --}}
        <div>
            <flux:heading size="xl">{{ __('AI Settings') }}</flux:heading>
            <flux:subheading>{{ __('Configure AI features for the Mentorship Platform') }}</flux:subheading>
        </div>

        {{-- Global AI Status --}}
        @if(!$this->aiEnabled)
            <flux:callout variant="warning" icon="exclamation-triangle">
                <flux:callout.heading>{{ __('AI Integration Disabled') }}</flux:callout.heading>
                <flux:callout.text>
                    {{ __('AI features are disabled system-wide. Contact your administrator to enable AI in the main CareNest settings.') }}
                </flux:callout.text>
            </flux:callout>
        @elseif(!$this->groqConfigured && !$this->geminiConfigured)
            <flux:callout variant="warning" icon="exclamation-triangle">
                <flux:callout.heading>{{ __('No AI Provider Configured') }}</flux:callout.heading>
                <flux:callout.text>
                    {{ __('No AI provider API keys are configured. Contact your administrator to set up Groq or Gemini in the main CareNest AI settings.') }}
                </flux:callout.text>
            </flux:callout>
        @endif

        {{-- Lesson Generation Settings --}}
        <flux:card>
            <div class="flex items-start justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-purple-100 dark:bg-purple-900/30">
                        <flux:icon.academic-cap class="size-6 text-purple-600 dark:text-purple-400" />
                    </div>
                    <div>
                        <flux:heading size="lg">{{ __('Lesson Generation') }}</flux:heading>
                        <flux:subheading>{{ __('Generate educational content for topics') }}</flux:subheading>
                    </div>
                </div>
                <flux:switch wire:model.live="lessonGeneration.enabled" :disabled="!$this->aiEnabled" />
            </div>

            @if($lessonGeneration['enabled'])
                <div class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:select wire:model.live="lessonGeneration.provider" :label="__('Provider')">
                            @if($this->groqConfigured)
                                <option value="groq">Groq</option>
                            @endif
                            @if($this->geminiConfigured)
                                <option value="gemini">Google Gemini</option>
                            @endif
                        </flux:select>

                        <flux:select wire:model="lessonGeneration.model" :label="__('Model')">
                            @if($lessonGeneration['provider'] === 'groq')
                                @foreach($this->groqModels as $model)
                                    <option value="{{ $model }}">{{ $model }}</option>
                                @endforeach
                            @else
                                @foreach($this->geminiModels as $model)
                                    <option value="{{ $model }}">{{ $model }}</option>
                                @endforeach
                            @endif
                        </flux:select>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <flux:label>{{ __('Temperature') }}: {{ number_format($lessonGeneration['temperature'], 1) }}</flux:label>
                            <input type="range" wire:model.live="lessonGeneration.temperature" min="0" max="2" step="0.1"
                                class="w-full h-2 bg-zinc-200 rounded-lg appearance-none cursor-pointer dark:bg-zinc-700 mt-2">
                            <p class="text-xs text-zinc-500 mt-1">{{ __('Higher = more creative, Lower = more focused') }}</p>
                        </div>

                        <flux:input type="number" wire:model="lessonGeneration.max_tokens" :label="__('Max Tokens')" min="100" max="8192" />
                    </div>

                    <flux:textarea
                        wire:model="lessonGeneration.system_prompt"
                        :label="__('Custom System Prompt (Optional)')"
                        :description="__('Leave empty to use the default lesson generation prompt')"
                        rows="4"
                    />

                    <div class="flex items-center justify-between pt-4 border-t border-zinc-200 dark:border-zinc-700">
                        <div>
                            @if($lessonTestResult)
                                <span class="{{ $lessonTestSuccess ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $lessonTestResult }}
                                </span>
                            @endif
                        </div>
                        <div class="flex gap-2">
                            <flux:button variant="ghost" wire:click="testLessonConnection">
                                {{ __('Test Connection') }}
                            </flux:button>
                            <flux:button variant="primary" wire:click="saveLessonGeneration">
                                {{ __('Save') }}
                            </flux:button>
                        </div>
                    </div>
                </div>
            @endif
        </flux:card>

        {{-- AI Mentor Chat Settings --}}
        <flux:card>
            <div class="flex items-start justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-indigo-100 dark:bg-indigo-900/30">
                        <flux:icon.light-bulb class="size-6 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <div>
                        <flux:heading size="lg">{{ __('AI Mentor Chat') }}</flux:heading>
                        <flux:subheading>{{ __('AI assistant for staff learning and development') }}</flux:subheading>
                    </div>
                </div>
                <flux:switch wire:model.live="mentorChat.enabled" :disabled="!$this->aiEnabled" />
            </div>

            @if($mentorChat['enabled'])
                <div class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:select wire:model.live="mentorChat.provider" :label="__('Provider')">
                            @if($this->groqConfigured)
                                <option value="groq">Groq</option>
                            @endif
                            @if($this->geminiConfigured)
                                <option value="gemini">Google Gemini</option>
                            @endif
                        </flux:select>

                        <flux:select wire:model="mentorChat.model" :label="__('Model')">
                            @if($mentorChat['provider'] === 'groq')
                                @foreach($this->groqModels as $model)
                                    <option value="{{ $model }}">{{ $model }}</option>
                                @endforeach
                            @else
                                @foreach($this->geminiModels as $model)
                                    <option value="{{ $model }}">{{ $model }}</option>
                                @endforeach
                            @endif
                        </flux:select>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <flux:label>{{ __('Temperature') }}: {{ number_format($mentorChat['temperature'], 1) }}</flux:label>
                            <input type="range" wire:model.live="mentorChat.temperature" min="0" max="2" step="0.1"
                                class="w-full h-2 bg-zinc-200 rounded-lg appearance-none cursor-pointer dark:bg-zinc-700 mt-2">
                        </div>

                        <flux:input type="number" wire:model="mentorChat.max_tokens" :label="__('Max Tokens')" min="100" max="4096" />
                    </div>

                    <flux:textarea
                        wire:model="mentorChat.system_prompt"
                        :label="__('Custom System Prompt (Optional)')"
                        :description="__('Customize the AI Mentor personality. Leave empty for the default behavioral health mentor prompt.')"
                        rows="6"
                    />

                    <div class="flex items-center justify-between pt-4 border-t border-zinc-200 dark:border-zinc-700">
                        <div>
                            @if($chatTestResult)
                                <span class="{{ $chatTestSuccess ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $chatTestResult }}
                                </span>
                            @endif
                        </div>
                        <div class="flex gap-2">
                            <flux:button variant="ghost" wire:click="testChatConnection">
                                {{ __('Test Connection') }}
                            </flux:button>
                            <flux:button variant="primary" wire:click="saveMentorChat">
                                {{ __('Save') }}
                            </flux:button>
                        </div>
                    </div>
                </div>
            @endif
        </flux:card>
    </div>

    @script
    <script>
        $wire.on('lesson-settings-saved', () => {
            Flux.toast({ text: '{{ __('Lesson generation settings saved.') }}', heading: '{{ __('Success') }}', variant: 'success' })
        })
        $wire.on('chat-settings-saved', () => {
            Flux.toast({ text: '{{ __('AI Mentor settings saved.') }}', heading: '{{ __('Success') }}', variant: 'success' })
        })
    </script>
    @endscript
</flux:main>
