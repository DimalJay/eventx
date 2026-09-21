<?php

namespace Tests\Models;

use DateTime;
use Models\Admin;
use Tests\TestCase;

class AdminTest extends TestCase
{
    public function testConstructorStoresLoginFields(): void
    {
        $admin = new Admin('root@x.com', 'Root', 'Admin', 's3cret');

        $this->assertSame('root@x.com', $this->prop($admin, 'email'));
        $this->assertSame('Root', $this->prop($admin, 'firstName'));
        $this->assertSame('Admin', $this->prop($admin, 'lastName'));
    }

    public function testPasswordIsHashed(): void
    {
        $admin = new Admin('root@x.com', 'Root', 'Admin', 's3cret');
        $hash = $this->prop($admin, 'password');

        $this->assertNotSame('s3cret', $hash);
        $this->assertTrue(password_verify('s3cret', $hash));
    }

    public function testConstructorInitialisesTimestamps(): void
    {
        $admin = new Admin('root@x.com', 'Root', 'Admin', 's3cret');

        $this->assertInstanceOf(DateTime::class, $this->prop($admin, 'createdAt'));
        $this->assertInstanceOf(DateTime::class, $this->prop($admin, 'updatedAt'));
    }

    public function testEmptyReturnsInstance(): void
    {
        $this->assertInstanceOf(Admin::class, Admin::empty());
    }
}