<?php

namespace Tests\Services;

use App\Services\ClaudeAiSummaryService;
use PHPUnit\Framework\TestCase;

class ClaudeAiSummaryServiceTest extends TestCase
{
    public function test_missing_api_key_fails_gracefully_without_throwing(): void
    {
        $service = new ClaudeAiSummaryService(apiKey: '');

        $result = $service->summarize('Violent Conduct', 'The player struck an opponent.');

        $this->assertSame('failed', $result->status);
        $this->assertNull($result->text);
        $this->assertStringContainsString('ANTHROPIC_API_KEY', $result->error);
    }

    public function test_missing_title_and_detail_fails_gracefully(): void
    {
        $service = new ClaudeAiSummaryService(apiKey: 'fake-key-for-test');

        $result = $service->summarize('   ', null);

        $this->assertSame('failed', $result->status);
    }

    public function test_title_alone_without_detail_is_accepted(): void
    {
        // No detail provided - should still be considered valid input (only
        // fails downstream because there's no real API key in this test),
        // not rejected for "missing reason" the way an empty title+detail is.
        $service = new ClaudeAiSummaryService(apiKey: '');

        $result = $service->summarize('Dissent', null);

        $this->assertSame('failed', $result->status);
        $this->assertStringContainsString('ANTHROPIC_API_KEY', $result->error);
    }
}
