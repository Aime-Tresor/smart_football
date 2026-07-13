<?php

namespace App\Services;

final class AiSummaryResult
{
    private function __construct(
        public readonly string $status,
        public readonly ?string $text,
        public readonly ?string $error,
    ) {
    }

    public static function success(string $text): self
    {
        return new self('completed', $text, null);
    }

    public static function failure(string $error): self
    {
        return new self('failed', null, $error);
    }
}
