<?php

namespace Tests\Services;

use Helpers\GeminiHelper;
use Services\SentimentService;
use Tests\TestCase;

class SentimentServiceTest extends TestCase
{
    private bool $forcedBlank = false;

    protected function setUp(): void
    {
        $this->forcedBlank = !self::hasConfiguredKey();
        if ($this->forcedBlank) {
            $_ENV['GEMINI_API_KEY'] = '';
            putenv('GEMINI_API_KEY');
        }
    }

    protected function tearDown(): void
    {
        if ($this->forcedBlank) {
            unset($_ENV['GEMINI_API_KEY']);
            putenv('GEMINI_API_KEY');
        }
    }

    private static function hasConfiguredKey(): bool
    {
        $key = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY');
        return is_string($key) && $key !== '';
    }

    public function testClassifyReturnsPendingWhenApiKeyIsMissing(): void
    {
        if (self::hasConfiguredKey()) {
            $this->markTestSkipped('GEMINI_API_KEY configured in .env.test; offline no-key path not exercised.');
        }

        $this->assertSame('Pending', (new SentimentService())->classifyComment('Amazing event!'));
    }

    public function testLabelsAreExposed(): void
    {
        $this->assertSame(
            ['Positive', 'Negative', 'Neutral'],
            array_values(GeminiHelper::LABELS)
        );
    }

    public function testEmptyCommentStillClassifiesViaWhitelist(): void
    {
        if (self::hasConfiguredKey()) {
            $this->markTestSkipped('GEMINI_API_KEY configured in .env.test; offline whitelist path not exercised.');
        }

        $this->assertIsString((new SentimentService())->classifyComment(''));
    }
}