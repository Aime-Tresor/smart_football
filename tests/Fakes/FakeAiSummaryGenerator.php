<?php

namespace Tests\Fakes;

use App\Services\AiSummaryGenerator;
use App\Services\AiSummaryResult;

class FakeAiSummaryGenerator implements AiSummaryGenerator
{
    public int $callCount = 0;
    public ?string $lastTitle = null;
    public ?string $lastDetail = null;

    public function __construct(
        private bool $shouldSucceed = true,
        private string $summaryText = 'Fake deep AI summary explaining the incident in detail.',
    ) {
    }

    public function summarize(string $cardReasonTitle, ?string $detailedReason): AiSummaryResult
    {
        $this->callCount++;
        $this->lastTitle = $cardReasonTitle;
        $this->lastDetail = $detailedReason;

        return $this->shouldSucceed
            ? AiSummaryResult::success($this->summaryText)
            : AiSummaryResult::failure('Simulated AI failure');
    }
}
