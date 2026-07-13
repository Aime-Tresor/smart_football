<?php

namespace App\Services;

use App\Config;
use RuntimeException;

/**
 * Calls the Anthropic Messages API to generate the Card Reason Title (or,
 * for appeal decisions, a fuller one-sentence summary). Never throws for
 * expected failure modes (missing key, network error, bad response) -
 * callers get a `failed` AiSummaryResult instead, so a card save is never
 * blocked by an AI outage (per requirement).
 */
class ClaudeAiSummaryService extends AbstractAiSummaryService
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';
    private const API_VERSION = '2023-06-01';

    public function __construct(
        private ?string $apiKey = null,
        private ?string $model = null,
        private int $timeoutSeconds = 15,
    ) {
        $this->apiKey = $this->apiKey ?? Config::anthropicApiKey();
        $this->model = $this->model ?? Config::anthropicModel();
    }

    protected function hasApiKey(): bool
    {
        return (bool) $this->apiKey;
    }

    protected function missingKeyMessage(): string
    {
        return 'AI summary is not configured (missing ANTHROPIC_API_KEY).';
    }

    protected function callApi(string $systemPrompt, string $userContent, int $maxOutputTokens): string
    {
        $payload = json_encode([
            'model' => $this->model,
            'max_tokens' => $maxOutputTokens,
            'temperature' => 0.2,
            'system' => $systemPrompt,
            'messages' => [
                ['role' => 'user', 'content' => $userContent],
            ],
        ]);

        $ch = curl_init(self::API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
            CURLOPT_HTTPHEADER => [
                'content-type: application/json',
                'x-api-key: ' . $this->apiKey,
                'anthropic-version: ' . self::API_VERSION,
            ],
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('AI request failed: ' . $curlError);
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new RuntimeException("AI request returned HTTP $httpCode: " . substr($response, 0, 300));
        }

        $decoded = json_decode($response, true);
        $text = $decoded['content'][0]['text'] ?? null;
        if (!$text) {
            throw new RuntimeException('AI response did not contain a summary.');
        }

        return trim($text);
    }
}
