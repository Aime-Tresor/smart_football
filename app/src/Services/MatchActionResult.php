<?php

namespace App\Services;

final class MatchActionResult
{
    private function __construct(
        public readonly bool $success,
        public readonly ?array $match,
        public readonly ?string $error,
        public readonly bool $needsConfirmation = false,
    ) {
    }

    public static function ok(array $match): self
    {
        return new self(true, $match, null);
    }

    public static function fail(string $error): self
    {
        return new self(false, null, $error);
    }

    public static function needsConfirmation(string $message): self
    {
        return new self(false, null, $message, true);
    }
}
