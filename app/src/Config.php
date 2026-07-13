<?php

namespace App;

/**
 * Minimal env reader. Loads KEY=VALUE pairs from a .env file (if present)
 * into getenv()/$_ENV once per request, then exposes typed accessors.
 */
class Config
{
    private static bool $loaded = false;

    public static function load(?string $path = null): void
    {
        if (self::$loaded) {
            return;
        }
        self::$loaded = true;

        $path = $path ?? dirname(__DIR__, 2) . '/.env';
        if (!is_file($path)) {
            return;
        }

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if ((str_starts_with($value, '"') && str_ends_with($value, '"'))
                || (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }
            if (getenv($key) === false) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        self::load();
        $value = getenv($key);
        return $value === false ? $default : $value;
    }

    /** Which AiSummaryGenerator implementation to use: 'anthropic' (default), 'openai', 'gemini', or 'groq'. */
    public static function aiProvider(): string
    {
        return strtolower(self::get('AI_PROVIDER', 'anthropic') ?? 'anthropic');
    }

    public static function anthropicApiKey(): ?string
    {
        return self::get('ANTHROPIC_API_KEY');
    }

    public static function anthropicModel(): string
    {
        return self::get('ANTHROPIC_MODEL', 'claude-haiku-4-5') ?? 'claude-haiku-4-5';
    }

    public static function openAiApiKey(): ?string
    {
        return self::get('OPENAI_API_KEY');
    }

    public static function openAiModel(): string
    {
        return self::get('OPENAI_MODEL', 'gpt-4o-mini') ?? 'gpt-4o-mini';
    }

    public static function geminiApiKey(): ?string
    {
        return self::get('GEMINI_API_KEY');
    }

    public static function geminiModel(): string
    {
        return self::get('GEMINI_MODEL', 'gemini-2.0-flash') ?? 'gemini-2.0-flash';
    }

    /** Groq hosts open-source models (Llama, Gemma, etc.) behind an OpenAI-compatible free-tier API. */
    public static function groqApiKey(): ?string
    {
        return self::get('GROQ_API_KEY');
    }

    public static function groqModel(): string
    {
        return self::get('GROQ_MODEL', 'llama-3.3-70b-versatile') ?? 'llama-3.3-70b-versatile';
    }

    public static function dbHost(): string
    {
        return self::get('DB_HOST', 'localhost') ?? 'localhost';
    }

    public static function dbName(): string
    {
        return self::get('DB_NAME', 'fa_db') ?? 'fa_db';
    }

    public static function dbUser(): string
    {
        return self::get('DB_USER', 'root') ?? 'root';
    }

    public static function dbPassword(): string
    {
        return self::get('DB_PASSWORD', '') ?? '';
    }
}
