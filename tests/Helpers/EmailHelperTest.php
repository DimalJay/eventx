<?php

namespace Tests\Helpers;

use Helpers\EmailHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EmailHelperTest extends TestCase
{
    protected function setUp(): void
    {
        foreach (['BACKEND_ENDPOINT', 'DOMAIN', 'FRONTEND_HOST'] as $key) {
            unset($_ENV[$key]);
            putenv($key);
        }
    }

    protected function tearDown(): void
    {
        foreach (['BACKEND_ENDPOINT', 'DOMAIN', 'FRONTEND_HOST'] as $key) {
            unset($_ENV[$key]);
            putenv($key);
        }
    }

    #[DataProvider('backendUrlProvider')]
    public function testBackendUrl(array $env, string $expected): void
    {
        foreach ($env as $key => $value) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
        $this->assertSame($expected, EmailHelper::backendUrl());
    }

    public static function backendUrlProvider(): array
    {
        return [
            'full url keeps scheme' => [
                ['BACKEND_ENDPOINT' => 'https://api.example.com/'],
                'https://api.example.com',
            ],
            'no scheme defaults to http' => [
                ['BACKEND_ENDPOINT' => 'api.example.com'],
                'http://api.example.com',
            ],
            'unset defaults to localhost' => [
                [],
                'http://localhost',
            ],
        ];
    }

    #[DataProvider('frontendUrlProvider')]
    public function testFrontendUrl(array $env, string $expected): void
    {
        foreach ($env as $key => $value) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
        $this->assertSame($expected, EmailHelper::frontendUrl());
    }

    public static function frontendUrlProvider(): array
    {
        return [
            'host with scheme strips trailing slash' => [
                ['FRONTEND_HOST' => 'https://app.example.com/'],
                'https://app.example.com',
            ],
            'bare host gains https' => [
                ['FRONTEND_HOST' => 'app.example.com'],
                'https://app.example.com',
            ],
            'fallback uses domain on port 3000' => [
                ['DOMAIN' => 'example.com'],
                'http://example.com:3000',
            ],
            'fallback defaults to localhost:3000' => [
                [],
                'http://localhost:3000',
            ],
            'frontend host wins over domain' => [
                ['FRONTEND_HOST' => 'https://app.example.com', 'DOMAIN' => 'example.com'],
                'https://app.example.com',
            ],
        ];
    }
}