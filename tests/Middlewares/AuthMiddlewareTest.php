<?php

namespace Tests\Middlewares;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Middlewares\AuthMiddleware;
use Tests\TestCase;

class AuthMiddlewareTest extends TestCase
{
    private const SECRET = '0123456789abcdef0123456789abcdef';

    protected function setUp(): void
    {
        $_ENV['JWT_SECRET'] = self::SECRET;
        putenv('JWT_SECRET=' . self::SECRET);
        unset($_COOKIE['auth_token'], $_SERVER['uid']);
    }

    protected function tearDown(): void
    {
        unset($_ENV['JWT_SECRET'], $_COOKIE['auth_token'], $_SERVER['uid']);
        putenv('JWT_SECRET');
        http_response_code(200);
    }

    public function testGenerateTokenReturnsThreePartJwt(): void
    {
        $token = AuthMiddleware::generateToken(42);

        $this->assertIsString($token);
        $this->assertCount(3, explode('.', $token));
    }

    public function testGenerateTokenClaimsAreCorrect(): void
    {
        $token = AuthMiddleware::generateToken(42);
        $payload = JWT::decode($token, new Key(self::SECRET, 'HS256'));

        $this->assertSame('http://localhost', $payload->iss);
        $this->assertSame(42, $payload->data->id);
        $this->assertLessThanOrEqual(time(), $payload->iat);
        $this->assertGreaterThan(time(), $payload->exp);
        // Expiry is exactly 24h after issuance.
        $this->assertSame($payload->iat + 60 * 60 * 24, $payload->exp);
    }

    public function testTokenIsRejectedWithWrongSecret(): void
    {
        $token = AuthMiddleware::generateToken(42);

        $this->expectException(SignatureInvalidException::class);
        JWT::decode($token, new Key(str_repeat('y', 32), 'HS256'));
    }

    public function testHandleDeniesAccessWithoutCookie(): void
    {
        ob_start();
        $result = AuthMiddleware::handle();
        $output = (string) ob_get_clean();

        $this->assertFalse($result);
        $this->assertSame(401, http_response_code());
        $this->assertJson($output);
        $this->assertSame([
            'success' => false,
            'message' => 'Access denied. Please login first.',
        ], json_decode($output, true));
    }

    public function testHandleAllowsValidTokenAndSetsUid(): void
    {
        $_COOKIE['auth_token'] = AuthMiddleware::generateToken(7);

        ob_start();
        $result = AuthMiddleware::handle();
        ob_get_clean();

        $this->assertTrue($result);
        $this->assertSame(7, $_SERVER['uid'] ?? null);
        $this->assertSame(200, http_response_code());
    }

    public function testHandleRejectsExpiredToken(): void
    {
        $now = time();
        $expired = JWT::encode([
            'iss' => 'http://localhost',
            'iat' => $now - 3600,
            'exp' => $now - 60, // already expired
            'data' => ['id' => 1],
        ], self::SECRET, 'HS256');
        $_COOKIE['auth_token'] = $expired;

        ob_start();
        $result = AuthMiddleware::handle();
        $output = (string) ob_get_clean();

        $this->assertFalse($result);
        $this->assertSame(401, http_response_code());
        $this->assertSame('Token expired. Please login again.', json_decode($output, true)['message']);
    }

    public function testHandleRejectsTokenWithInvalidSignature(): void
    {
        $forged = JWT::encode(['data' => ['id' => 1], 'exp' => time() + 3600, 'iat' => time()], str_repeat('z', 32), 'HS256');
        $_COOKIE['auth_token'] = $forged;

        ob_start();
        $result = AuthMiddleware::handle();
        $output = (string) ob_get_clean();

        $this->assertFalse($result);
        $this->assertSame(401, http_response_code());
        $this->assertSame('Invalid token. Please login again.', json_decode($output, true)['message']);
    }

    public function testHandleRejectsMalformedToken(): void
    {
        $_COOKIE['auth_token'] = 'not-a-jwt';

        ob_start();
        $result = AuthMiddleware::handle();
        $output = (string) ob_get_clean();

        $this->assertFalse($result);
        $this->assertSame(401, http_response_code());
        $this->assertSame('Authentication error. Please login again.', json_decode($output, true)['message']);
    }
}