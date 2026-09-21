<?php

namespace Tests\Services;

use Services\UserService;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    private UserService $service;

    protected function setUp(): void
    {
        $this->service = new UserService();
    }

    public function testSafeUserStripsSensitiveFields(): void
    {
        $result = $this->invoke($this->service, 'safeUser', [[
            'id' => 1,
            'email' => 'a@b.com',
            'password' => 'hash',
            'resetToken' => 'r',
            'verificationToken' => 'v',
            'firstName' => 'Alice',
        ]]);

        $this->assertArrayNotHasKey('password', $result);
        $this->assertArrayNotHasKey('resetToken', $result);
        $this->assertArrayNotHasKey('verificationToken', $result);
        $this->assertSame(1, $result['id']);
        $this->assertSame('a@b.com', $result['email']);
        $this->assertSame('Alice', $result['firstName']);
    }

    public function testSafeUserToleratesMissingKeys(): void
    {
        $result = $this->invoke($this->service, 'safeUser', [['id' => 2]]);

        $this->assertSame(2, $result['id']);
        $this->assertCount(1, $result);
    }

    public function testSafeUserLeavesOtherColumnsInPlace(): void
    {
        $row = ['id' => 3, 'email' => 'x@y.z', 'loginType' => 'google', 'phoneNumber' => '123'];
        $result = $this->invoke($this->service, 'safeUser', [$row]);

        foreach (['id', 'email', 'loginType', 'phoneNumber'] as $key) {
            $this->assertSame($row[$key], $result[$key]);
        }
    }
}