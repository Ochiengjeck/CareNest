<?php

namespace App\Concerns;

trait AiSettingsValidationRules
{
    protected function aiGlobalRules(): array
    {
        return [
            'ai_enabled' => ['required', 'boolean'],
        ];
    }

    protected function aiProviderRules(string $provider): array
    {
        return match ($provider) {
            'groq' => [
                'groq_api_key' => ['nullable', 'string', 'max:500'],
                'groq_default_model' => ['required', 'string', 'in:llama-3.3-70b-versatile,llama-3.1-8b-instant,meta-llama/llama-4-scout-17b-16e-instruct,qwen/qwen3-32b'],
            ],
            'gemini' => [
                'gemini_api_key' => ['nullable', 'string', 'max:500'],
                'gemini_default_model' => ['required', 'string', 'in:gemini-2.0-flash,gemini-2.5-flash,gemini-2.5-pro'],
            ],
            default => [],
        };
    }

    protected function aiUseCaseRules(): array
    {
        return [
            'useCase.enabled' => ['required', 'boolean'],
            'useCase.provider' => ['required', 'string', 'in:groq,gemini'],
            'useCase.model' => ['required', 'string', 'max:100'],
            'useCase.temperature' => ['required', 'numeric', 'min:0', 'max:2'],
            'useCase.max_tokens' => ['required', 'integer', 'min:100', 'max:32768'],
            'useCase.system_prompt' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
