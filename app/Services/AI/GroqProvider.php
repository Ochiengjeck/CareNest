<?php

namespace App\Services\AI;

use App\Contracts\AiProvider;
use App\DataObjects\AiResponse;
use App\Services\SettingsService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqProvider implements AiProvider
{
    private const BASE_URL = 'https://api.groq.com/openai/v1';

    private const MODELS = [
        'llama-3.3-70b-versatile' => 'Llama 3.3 70B Versatile',
        'llama-3.1-8b-instant' => 'Llama 3.1 8B Instant',
        'meta-llama/llama-4-scout-17b-16e-instruct' => 'Llama 4 Scout 17B',
        'qwen/qwen3-32b' => 'Qwen 3 32B',
    ];

    public function __construct(private SettingsService $settings) {}

    public function chat(array $messages, array $options = []): AiResponse
    {
        $apiKey = $this->settings->get('groq_api_key');
        if (! $apiKey) {
            return AiResponse::failure('Groq API key not configured.');
        }

        $model = $options['model'] ?? $this->settings->get('groq_default_model', 'llama-3.3-70b-versatile');
        $startTime = microtime(true);

        try {
            $payload = [
                'model' => $model,
                'messages' => $messages,
                'temperature' => (float) ($options['temperature'] ?? 0.7),
                'max_tokens' => (int) ($options['max_tokens'] ?? 2048),
            ];

            if ($options['json_mode'] ?? false) {
                $payload['response_format'] = ['type' => 'json_object'];
            }

            $response = Http::withToken($apiKey)
                ->timeout($options['json_mode'] ?? false ? 60 : 30)
                ->post(self::BASE_URL.'/chat/completions', $payload);

            if ($response->failed()) {
                Log::warning('Groq API error', ['status' => $response->status(), 'body' => $response->body()]);

                return AiResponse::failure('Groq API error: '.$response->json('error.message', 'Unknown error'));
            }

            $data = $response->json();

            return AiResponse::success(
                content: $data['choices'][0]['message']['content'] ?? '',
                model: $model,
                promptTokens: $data['usage']['prompt_tokens'] ?? null,
                completionTokens: $data['usage']['completion_tokens'] ?? null,
                responseTime: round(microtime(true) - $startTime, 3),
            );
        } catch (\Exception $e) {
            Log::error('Groq API exception', ['message' => $e->getMessage()]);

            return AiResponse::failure('Groq connection failed: '.$e->getMessage());
        }
    }

    public function chatWithMedia(array $messages, string $mediaPath, string $mimeType, array $options = []): AiResponse
    {
        return AiResponse::failure('Groq does not support multimodal input. Use Gemini for document analysis.');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->settings->get('groq_api_key'));
    }

    public function testConnection(): AiResponse
    {
        return $this->chat([
            ['role' => 'user', 'content' => 'Reply with exactly: Connection successful'],
        ], ['max_tokens' => 20]);
    }

    public function getAvailableModels(): array
    {
        return self::MODELS;
    }
}
