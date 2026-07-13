<?php

namespace App\Services;

use App\Config;

/**
 * Picks the AiSummaryGenerator implementation based on the AI_PROVIDER env
 * var (defaults to 'anthropic'). All implementations share the same
 * interface/contract (see AbstractAiSummaryService), so callers never need
 * to know which one is active.
 */
class AiSummaryGeneratorFactory
{
    public static function make(): AiSummaryGenerator
    {
        return match (Config::aiProvider()) {
            'openai' => new OpenAiSummaryService(),
            'gemini' => new GeminiSummaryService(),
            'groq' => new GroqSummaryService(),
            default => new ClaudeAiSummaryService(),
        };
    }
}
