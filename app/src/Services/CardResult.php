<?php

namespace App\Services;

final class CardResult
{
    private function __construct(
        public readonly bool $success,
        public readonly ?array $card,
        public readonly ?string $error,
    ) {
    }

    public static function ok(array $card): self
    {
        return new self(true, $card, null);
    }

    public static function fail(string $error): self
    {
        return new self(false, null, $error);
    }
}
