<?php

namespace Tests\Services;

use App\Services\GroqSummaryService;
use PHPUnit\Framework\TestCase;

class GroqSummaryServiceTest extends TestCase
{
    public function test_missing_api_key_fails_gracefully_without_throwing(): void
    {
        $service = new GroqSummaryService(apiKey: '');

        $result = $service->summarize('Violent Conduct', 'The player struck an opponent.');

        $this->assertSame('failed', $result->status);
        $this->assertNull($result->text);
        $this->assertStringContainsString('GROQ_API_KEY', $result->error);
    }

    public function test_missing_title_and_detail_fails_gracefully(): void
    {
        $service = new GroqSummaryService(apiKey: 'fake-key-for-test');

        $result = $service->summarize('   ', null);

        $this->assertSame('failed', $result->status);
    }

    public function test_title_alone_without_detail_is_accepted(): void
    {
        $service = new GroqSummaryService(apiKey: '');

        $result = $service->summarize('Dissent', null);

        $this->assertSame('failed', $result->status);
        $this->assertStringContainsString('GROQ_API_KEY', $result->error);
    }
}
