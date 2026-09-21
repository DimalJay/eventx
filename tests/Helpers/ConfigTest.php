<?php

namespace Tests\Helpers;

use Helpers\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class ConfigTest extends TestCase
{
    protected function setUp(): void
    {
        unset($_ENV['TEST_CONFIG_KEY']);
        putenv('TEST_CONFIG_KEY');
    }

    protected function tearDown(): void
    {
        unset($_ENV['TEST_CONFIG_KEY']);
        putenv('TEST_CONFIG_KEY');
    }

    public function testGetReturnsDefaultWhenNothingIsSet(): void
    {
        $this->assertNull(Config::get('TEST_CONFIG_KEY'));
        $this->assertSame('fallback', Config::get('TEST_CONFIG_KEY', 'fallback'));
    }

    public function testGetReturnsValueFromEnv(): void
    {
        $_ENV['TEST_CONFIG_KEY'] = 'from-env';
        $this->assertSame('from-env', Config::get('TEST_CONFIG_KEY'));
    }

    public function testGetReturnsValueFromGetenv(): void
    {
        putenv('TEST_CONFIG_KEY=from-getenv');
        $this->assertSame('from-getenv', Config::get('TEST_CONFIG_KEY'));
    }

    public function testGetReturnsDefaultForEmptyString(): void
    {
        $_ENV['TEST_CONFIG_KEY'] = '';
        $this->assertSame('fallback', Config::get('TEST_CONFIG_KEY', 'fallback'));
    }

    public function testRequireSecretReturnsValueWhenLongEnough(): void
    {
        $_ENV['TEST_CONFIG_KEY'] = str_repeat('a', 16);
        $this->assertSame(str_repeat('a', 16), Config::requireSecret('TEST_CONFIG_KEY'));
    }

    #[DataProvider('invalidSecrets')]
    public function testRequireSecretThrowsForMissingOrShortValues(?string $value): void
    {
        if ($value === null) {
            unset($_ENV['TEST_CONFIG_KEY']);
            putenv('TEST_CONFIG_KEY');
        } else {
            $_ENV['TEST_CONFIG_KEY'] = $value;
            putenv('TEST_CONFIG_KEY=' . $value);
        }

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing required secret: TEST_CONFIG_KEY');
        Config::requireSecret('TEST_CONFIG_KEY');
    }

    public static function invalidSecrets(): array
    {
        return [
            'not set' => [null],
            'short value' => ['short'],
            'exactly-15-chars' => [str_repeat('b', 15)],
        ];
    }
}