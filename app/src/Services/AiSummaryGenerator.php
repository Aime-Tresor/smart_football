<?php

namespace App\Services;

/**
 * Generates a deep, thorough AI explanation of a card or appeal decision,
 * grounded in a given title/category and optional detail. Kept as an
 * interface so CardService/AppealDecisionService depend on an abstraction
 * (SOLID: dependency inversion) and tests can inject a fake instead of
 * calling a real LLM.
 */
interface AiSummaryGenerator
{
    public function summarize(string $cardReasonTitle, ?string $detailedReason): AiSummaryResult;
}
