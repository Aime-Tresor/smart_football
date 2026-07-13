<?php

namespace Tests\Services;

use App\Services\AiSummaryGeneratorFactory;
use App\Services\ClaudeAiSummaryService;
use App\Services\GeminiSummaryService;
use App\Services\GroqSummaryService;
use App\Services\OpenAiSummaryService;
use PHPUnit\Framework\TestCase;

class AiSummaryGeneratorFactoryTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('AI_PROVIDER');
    }

    public function test_selects_anthropic_explicitly(): void
    {
        // Note: a true "AI_PROVIDER is completely unset" scenario can't be
        // tested in isolation once a real .env defines it - Config::load()
        // only runs once per process and would apply the file's value the
        // first time any test touches it, regardless of tearDown() unsetting
        // the runtime override afterwards. The default-to-Claude fallback
        // for an unrecognized value is covered separately below.
        putenv('AI_PROVIDER=anthropic');
        $this->assertInstanceOf(ClaudeAiSummaryService::class, AiSummaryGeneratorFactory::make());
    }

    public function test_selects_openai(): void
    {
        putenv('AI_PROVIDER=openai');
        $this->assertInstanceOf(OpenAiSummaryService::class, AiSummaryGeneratorFactory::make());
    }

    public function test_selects_gemini(): void
    {
        putenv('AI_PROVIDER=gemini');
        $this->assertInstanceOf(GeminiSummaryService::class, AiSummaryGeneratorFactory::make());
    }

    public function test_selects_groq(): void
    {
        putenv('AI_PROVIDER=groq');
        $this->assertInstanceOf(GroqSummaryService::class, AiSummaryGeneratorFactory::make());
    }

    public function test_is_case_insensitive(): void
    {
        putenv('AI_PROVIDER=OpenAI');
        $this->assertInstanceOf(OpenAiSummaryService::class, AiSummaryGeneratorFactory::make());
    }

    public function test_unknown_provider_falls_back_to_claude(): void
    {
        putenv('AI_PROVIDER=some-unknown-provider');
        $this->assertInstanceOf(ClaudeAiSummaryService::class, AiSummaryGeneratorFactory::make());
    }
}
