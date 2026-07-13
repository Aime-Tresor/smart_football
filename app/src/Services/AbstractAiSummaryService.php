<?php

namespace App\Services;

/**
 * Shared input validation and prompt construction for every
 * AiSummaryGenerator implementation (Claude, OpenAI, Gemini), so the three
 * providers behave identically except for the actual HTTP call - same
 * validation, same system instruction, same graceful-failure contract.
 *
 * The referee provides both the Card Reason Title (short, required) and an
 * optional detailed explanation; AI's job is to produce a deep, thorough
 * explanation of the incident (ai_summary) grounded in both - it never
 * generates the title itself.
 */
abstract class AbstractAiSummaryService implements AiSummaryGenerator
{
    protected const SYSTEM_PROMPT = 'You are a football disciplinary assistant. Given the Card Reason Title a '
        . 'referee selected (e.g. "Violent Conduct") and, when available, the referee\'s detailed explanation, '
        . 'write a DEEP, THOROUGH explanation of the incident for the official match record - not a one-line '
        . 'summary. Cover, in 3 to 6 sentences: what the player did, the context of the incident (e.g. on- or '
        . 'off-the-ball, timing relative to play), and why it matches the given Card Reason Title category under '
        . 'the FIFA Laws of the Game where applicable. Stay strictly consistent with the given Card Reason Title - '
        . 'do not contradict or drift away from it, even if the detailed explanation is absent or sparse. Write in '
        . 'clear, professional, neutral language. Do not add opinions, speculation, or sanctions - describe only '
        . 'what was reported.';

    public function summarize(string $cardReasonTitle, ?string $detailedReason): AiSummaryResult
    {
        $cardReasonTitle = trim($cardReasonTitle);
        $detailedReason = $detailedReason !== null ? trim($detailedReason) : null;
        if ($detailedReason === '') {
            $detailedReason = null;
        }

        if ($cardReasonTitle === '' && $detailedReason === null) {
            return AiSummaryResult::failure('No card reason provided to summarize.');
        }

        if (!$this->hasApiKey()) {
            return AiSummaryResult::failure($this->missingKeyMessage());
        }

        try {
            $userContent = $this->buildUserContent($cardReasonTitle, $detailedReason);
            $text = $this->callApi(self::SYSTEM_PROMPT, $userContent, 350);
            return AiSummaryResult::success(trim($text));
        } catch (\Throwable $e) {
            return AiSummaryResult::failure($e->getMessage());
        }
    }

    protected function buildUserContent(string $cardReasonTitle, ?string $detailedReason): string
    {
        $content = 'Card Reason Title: ' . ($cardReasonTitle !== '' ? $cardReasonTitle : '(not given)');
        $content .= "\n" . ($detailedReason !== null
            ? "Referee's detailed explanation: {$detailedReason}"
            : "Referee's detailed explanation: (none provided - base the explanation on the Card Reason Title alone)");
        return $content;
    }

    abstract protected function hasApiKey(): bool;

    abstract protected function missingKeyMessage(): string;

    /**
     * @throws \Throwable on any failure - callers turn it into a failed AiSummaryResult
     */
    abstract protected function callApi(string $systemPrompt, string $userContent, int $maxOutputTokens): string;
}
