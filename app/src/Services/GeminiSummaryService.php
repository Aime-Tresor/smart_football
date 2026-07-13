<?php

namespace App\Services;

use App\Config;
use RuntimeException;

/**
 * Calls the Google Gemini generateContent API to produce a one-sentence
 * summary of a card, on the same terms as ClaudeAiSummaryService (see
 * AbstractAiSummaryService for the shared validation/prompt contract).
 */
class GeminiSummaryService extends AbstractAiSummaryService
{
    private const API_BASE = 'https://generativelanguage.googleapis.com/v1beta/models/';

    public function __construct(
        private ?string $apiKey = null,
        private ?string $model = null,
        private int $timeoutSeconds = 15,
    ) {
        $this->apiKey = $this->apiKey ?? Config::geminiApiKey();
        $this->model = $this->model ?? Config::geminiModel();
    }

    protected function hasApiKey(): bool
    {
        return (bool) $this->apiKey;
    }

    protected function missingKeyMessage(): string
    {
        return 'AI summary is not configured (missing GEMINI_API_KEY).';
    }

    protected function callApi(string $systemPrompt, string $userContent, int $maxOutputTokens): string
    {
        $payload = json_encode([
            'system_instruction' => [
                'parts' => [['text' => $systemPrompt]],
            ],
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => $userContent]]],
            ],
            'generationConfig' => ['maxOutputTokens' => $maxOutputTokens, 'temperature' => 0.2],
        ]);

        $url = self::API_BASE . rawurlencode($this->model) . ':generateContent?key=' . rawurlencode($this->apiKey);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
            CURLOPT_HTTPHEADER => ['content-type: application/json'],
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
        $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!$text) {
            throw new RuntimeException('AI response did not contain a summary.');
        }

        return trim($text);
    }
}
