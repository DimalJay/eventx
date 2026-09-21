<?php

namespace Tests\Models;

use DateTime;
use Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function testConstructorStoresIdentityFields(): void
    {
        $user = new User('a@b.com', 'Alice', 'Doe', 'secret123', 'pic.png');

        $this->assertSame('a@b.com', $this->prop($user, 'email'));
        $this->assertSame('Alice', $this->prop($user, 'firstName'));
        $this->assertSame('Doe', $this->prop($user, 'lastName'));
        $this->assertSame('pic.png', $this->prop($user, 'profilePicture'));
    }

    public function testConstructorDefaultsToStandardLoginType(): void
    {
        $user = new User('a@b.com', 'Alice', 'Doe', 'secret123', null);
        $this->assertSame('standard', $this->prop($user, 'loginType'));
    }

    public function testConstructorHashesPassword(): void
    {
        $user = new User('a@b.com', 'Alice', 'Doe', 'secret123', null);
        $hash = $this->prop($user, 'password');

        $this->assertNotSame('secret123', $hash);
        $this->assertTrue(password_verify('secret123', $hash));
        $this->assertFalse(password_verify('wrong', $hash));
    }

    public function testNewUserIsUnverifiedByDefault(): void
    {
        $user = new User('a@b.com', 'Alice', 'Doe', 'secret123', null);
        $this->assertFalse($this->prop($user, 'isVerified'));
    }

    public function testConstructorInitialisesTimestamps(): void
    {
        $user = new User('a@b.com', 'Alice', 'Doe', 'secret123', null);

        $this->assertInstanceOf(DateTime::class, $this->prop($user, 'createdAt'));
        $this->assertInstanceOf(DateTime::class, $this->prop($user, 'updatedAt'));
        $this->assertInstanceOf(DateTime::class, $this->prop($user, 'lastLogin'));
    }

    public function testEmptyReturnsAUserInstance(): void
    {
        $this->assertInstanceOf(User::class, User::empty());
    }

    #[DataProvider('loginTypes')]
    public function testLoginTypeIsStored(string $loginType): void
    {
        $user = new User('a@b.com', 'Alice', 'Doe', 'secret123', null, $loginType);
        $this->assertSame($loginType, $this->prop($user, 'loginType'));
    }

    public static function loginTypes(): array
    {
        return [
            'standard' => ['standard'],
            'google' => ['google'],
        ];
    }
}