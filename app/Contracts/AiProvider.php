<?php

namespace App\Contracts;

use App\DataObjects\AiResponse;

interface AiProvider
{
    public function chat(array $messages, array $options = []): AiResponse;

    public function chatWithMedia(array $messages, string $mediaPath, string $mimeType, array $options = []): AiResponse;

    public function isConfigured(): bool;

    public function testConnection(): AiResponse;

    public function getAvailableModels(): array;
}
