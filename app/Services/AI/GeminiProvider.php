<?php

namespace App\Services\AI;

use App\Contracts\AiProvider;
use App\DataObjects\AiResponse;
use App\Services\SettingsService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProvider implements AiProvider
{
    private const BASE_URL = 'https://generativelanguage.googleapis.com/v1beta/models';

    private const MODELS = [
        'gemini-2.0-flash' => 'Gemini 2.0 Flash',
        'gemini-2.5-flash' => 'Gemini 2.5 Flash',
        'gemini-2.5-pro' => 'Gemini 2.5 Pro',
    ];

    public function __construct(private SettingsService $settings) {}

    public function chat(array $messages, array $options = []): AiResponse
    {
        $apiKey = $this->settings->get('gemini_api_key');
        if (! $apiKey) {
            return AiResponse::failure('Gemini API key not configured.');
        }

        $model = $options['model'] ?? $this->settings->get('gemini_default_model', 'gemini-2.0-flash');
        $startTime = microtime(true);

        $contents = $this->convertMessages($messages);

        try {
            $response = Http::timeout(60)
                ->post(self::BASE_URL."/{$model}:generateContent?key={$apiKey}", [
                    'contents' => $contents,
                    'generationConfig' => [
                        'temperature' => (float) ($options['temperature'] ?? 0.7),
                        'maxOutputTokens' => (int) ($options['max_tokens'] ?? 2048),
                    ],
                ]);

            if ($response->failed()) {
                Log::warning('Gemini API error', ['status' => $response->status(), 'body' => $response->body()]);

                return AiResponse::failure('Gemini API error: '.$response->json('error.message', 'Unknown error'));
            }

            $data = $response->json();
            $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $usage = $data['usageMetadata'] ?? [];

            return AiResponse::success(
                content: $content,
                model: $model,
                promptTokens: $usage['promptTokenCount'] ?? null,
                completionTokens: $usage['candidatesTokenCount'] ?? null,
                responseTime: round(microtime(true) - $startTime, 3),
            );
        } catch (\Exception $e) {
            Log::error('Gemini API exception', ['message' => $e->getMessage()]);

            return AiResponse::failure('Gemini connection failed: '.$e->getMessage());
        }
    }

    public function chatWithMedia(array $messages, string $mediaPath, string $mimeType, array $options = []): AiResponse
    {
        $apiKey = $this->settings->get('gemini_api_key');
        if (! $apiKey) {
            return AiResponse::failure('Gemini API key not configured.');
        }

        $model = $options['model'] ?? 'gemini-2.5-pro';
        $startTime = microtime(true);

        $fileData = base64_encode(file_get_contents($mediaPath));
        $textParts = collect($messages)->where('role', 'user')->pluck('content')->implode("\n");

        try {
            $response = Http::timeout(120)
                ->post(self::BASE_URL."/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [[
                        'parts' => [
                            ['text' => $textParts],
                            ['inline_data' => [
                                'mime_type' => $mimeType,
                                'data' => $fileData,
                            ]],
                        ],
                    ]],
                    'generationConfig' => [
                        'temperature' => (float) ($options['temperature'] ?? 0.2),
                        'maxOutputTokens' => (int) ($options['max_tokens'] ?? 8192),
                    ],
                ]);

            if ($response->failed()) {
                return AiResponse::failure('Gemini API error: '.$response->json('error.message', 'Unknown error'));
            }

            $data = $response->json();

            return AiResponse::success(
                content: $data['candidates'][0]['content']['parts'][0]['text'] ?? '',
                model: $model,
                promptTokens: $data['usageMetadata']['promptTokenCount'] ?? null,
                completionTokens: $data['usageMetadata']['candidatesTokenCount'] ?? null,
                responseTime: round(microtime(true) - $startTime, 3),
            );
        } catch (\Exception $e) {
            return AiResponse::failure('Gemini multimodal failed: '.$e->getMessage());
        }
    }

    public function isConfigured(): bool
    {
        return ! empty($this->settings->get('gemini_api_key'));
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

    private function convertMessages(array $messages): array
    {
        $contents = [];
        $systemInstruction = null;

        foreach ($messages as $msg) {
            if ($msg['role'] === 'system') {
                $systemInstruction = $msg['content'];

                continue;
            }
            $contents[] = [
                'role' => $msg['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $msg['content']]],
            ];
        }

        // Prepend system instruction to first user message
        if ($systemInstruction && ! empty($contents)) {
            $contents[0]['parts'][0]['text'] = $systemInstruction."\n\n".$contents[0]['parts'][0]['text'];
        }

        return $contents;
    }
}
