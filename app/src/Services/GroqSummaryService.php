<?php

namespace App\Services;

use App\Config;
use RuntimeException;

/**
 * Calls Groq's OpenAI-compatible Chat Completions API, which hosts fast
 * inference over open-source models (Llama, Gemma, etc.) with a free
 * tier - on the same terms as the other providers (see
 * AbstractAiSummaryService for the shared validation/prompt contract).
 */
class GroqSummaryService extends AbstractAiSummaryService
{
    private const API_URL = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct(
        private ?string $apiKey = null,
        private ?string $model = null,
        private int $timeoutSeconds = 15,
    ) {
        $this->apiKey = $this->apiKey ?? Config::groqApiKey();
        $this->model = $this->model ?? Config::groqModel();
    }

    protected function hasApiKey(): bool
    {
        return (bool) $this->apiKey;
    }

    protected function missingKeyMessage(): string
    {
        return 'AI summary is not configured (missing GROQ_API_KEY).';
    }

    protected function callApi(string $systemPrompt, string $userContent, int $maxOutputTokens): string
    {
        $payload = json_encode([
            'model' => $this->model,
            'max_tokens' => $maxOutputTokens,
            'temperature' => 0.2,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
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
                'authorization: Bearer ' . $this->apiKey,
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
        $text = $decoded['choices'][0]['message']['content'] ?? null;
        if (!$text) {
            throw new RuntimeException('AI response did not contain a summary.');
        }

        return trim($text);
    }
}
