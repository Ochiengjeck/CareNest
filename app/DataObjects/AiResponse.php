<?php

namespace App\DataObjects;

class AiResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $content = null,
        public readonly ?string $error = null,
        public readonly ?string $model = null,
        public readonly ?int $promptTokens = null,
        public readonly ?int $completionTokens = null,
        public readonly ?float $responseTime = null,
    ) {}

    public static function success(
        string $content,
        string $model,
        ?int $promptTokens = null,
        ?int $completionTokens = null,
        ?float $responseTime = null,
    ): self {
        return new self(
            success: true,
            content: $content,
            model: $model,
            promptTokens: $promptTokens,
            completionTokens: $completionTokens,
            responseTime: $responseTime,
        );
    }

    public static function failure(string $error): self
    {
        return new self(success: false, error: $error);
    }
}
