<?php

namespace App\Services\AI;

use App\Contracts\AiProvider;
use App\DataObjects\AiResponse;
use App\Services\SettingsService;

class AiManager
{
    private array $providers = [];

    public function __construct(private SettingsService $settings) {}

    public function provider(string $name): AiProvider
    {
        return $this->providers[$name] ??= match ($name) {
            'groq' => new GroqProvider($this->settings),
            'gemini' => new GeminiProvider($this->settings),
            default => throw new \InvalidArgumentException("Unknown AI provider: {$name}"),
        };
    }

    public function forUseCase(string $useCase): AiProvider
    {
        $config = $this->getUseCaseConfig($useCase);

        return $this->provider($config['provider'] ?? 'groq');
    }

    public function getUseCaseConfig(string $useCase): array
    {
        $key = 'ai_usecase_'.$useCase;

        return $this->settings->get($key, [
            'enabled' => false,
            'provider' => 'groq',
            'model' => 'llama-3.3-70b-versatile',
            'temperature' => 0.7,
            'max_tokens' => 2048,
            'system_prompt' => '',
        ]);
    }

    public function executeForUseCase(string $useCase, string $userMessage, array $extraMessages = []): AiResponse
    {
        if (! $this->isEnabled()) {
            return AiResponse::failure('AI integration is disabled.');
        }

        $config = $this->getUseCaseConfig($useCase);

        if (! ($config['enabled'] ?? false)) {
            return AiResponse::failure("AI use case '{$useCase}' is disabled.");
        }

        $provider = $this->provider($config['provider']);

        if (! $provider->isConfigured()) {
            return AiResponse::failure("Provider '{$config['provider']}' is not configured (missing API key).");
        }

        $messages = [];

        if (! empty($config['system_prompt'])) {
            $messages[] = ['role' => 'system', 'content' => $config['system_prompt']];
        }

        $messages = array_merge($messages, $extraMessages);
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        return $provider->chat($messages, [
            'model' => $config['model'],
            'temperature' => $config['temperature'] ?? 0.7,
            'max_tokens' => $config['max_tokens'] ?? 2048,
        ]);
    }

    public function isEnabled(): bool
    {
        return (bool) $this->settings->get('ai_enabled', false);
    }

    public function getUseCases(): array
    {
        return [
            'report_generation' => [
                'label' => 'Report Generation',
                'description' => 'Generate text-based care reports, summaries, and documentation',
                'icon' => 'document-text',
                'recommended_provider' => 'groq',
            ],
            'document_analysis' => [
                'label' => 'Document Analysis',
                'description' => 'Analyze uploaded documents, images, and medical records',
                'icon' => 'document-magnifying-glass',
                'recommended_provider' => 'gemini',
            ],
            'care_assistant' => [
                'label' => 'Care Assistant',
                'description' => 'AI chatbot to help staff with care questions and procedures',
                'icon' => 'chat-bubble-left-right',
                'recommended_provider' => 'groq',
            ],
            'incident_summarization' => [
                'label' => 'Incident Summarization',
                'description' => 'Summarize incident reports and extract key details automatically',
                'icon' => 'exclamation-triangle',
                'recommended_provider' => 'groq',
            ],
            'therapy_reporting' => [
                'label' => 'Therapy Session Reporting',
                'description' => 'Generate professional therapy session notes and progress reports',
                'icon' => 'clipboard-document-check',
                'recommended_provider' => 'groq',
            ],
            'discharge_reporting' => [
                'label' => 'Discharge Reporting',
                'description' => 'Generate discharge summaries, aftercare instructions, and crisis plans',
                'icon' => 'arrow-right-start-on-rectangle',
                'recommended_provider' => 'groq',
            ],
            'mentorship_lesson_generation' => [
                'label' => 'Mentorship Lesson Generation',
                'description' => 'Generate educational lesson content for mentorship topics',
                'icon' => 'academic-cap',
                'recommended_provider' => 'groq',
            ],
            'mentorship_chat' => [
                'label' => 'Mentorship AI Mentor',
                'description' => 'AI mentor to help staff with learning and professional development',
                'icon' => 'light-bulb',
                'recommended_provider' => 'groq',
            ],
        ];
    }

    public function executeForUseCaseJson(string $useCase, string $userMessage, array $extraMessages = []): AiResponse
    {
        if (! $this->isEnabled()) {
            return AiResponse::failure('AI integration is disabled.');
        }

        $config = $this->getUseCaseConfig($useCase);

        if (! ($config['enabled'] ?? false)) {
            return AiResponse::failure("AI use case '{$useCase}' is disabled.");
        }

        $provider = $this->provider($config['provider']);

        if (! $provider->isConfigured()) {
            return AiResponse::failure("Provider '{$config['provider']}' is not configured (missing API key).");
        }

        $messages = [];

        if (! empty($config['system_prompt'])) {
            $messages[] = ['role' => 'system', 'content' => $config['system_prompt']];
        }

        $messages = array_merge($messages, $extraMessages);
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        return $provider->chat($messages, [
            'model' => $config['model'],
            'temperature' => $config['temperature'] ?? 0.7,
            'max_tokens' => $config['max_tokens'] ?? 4096,
            'json_mode' => true,
        ]);
    }

    public function isUseCaseEnabled(string $useCase): bool
    {
        $config = $this->getUseCaseConfig($useCase);

        return $config['enabled'] ?? false;
    }

    public function getUseCaseProvider(string $useCase): string
    {
        $config = $this->getUseCaseConfig($useCase);

        return $config['provider'] ?? 'groq';
    }

    public function isConfigured(string $provider): bool
    {
        return $this->provider($provider)->isConfigured();
    }
}
