<?php

namespace Tests\Models;

use DateTime;
use Models\PaymentAccount;
use Tests\TestCase;

class PaymentAccountTest extends TestCase
{
    public function testConstructorStoresFields(): void
    {
        $account = new PaymentAccount(5, 'acct_123', 'org@example.com');

        $this->assertSame(5, $this->prop($account, 'userId'));
        $this->assertSame('acct_123', $this->prop($account, 'accountId'));
        $this->assertSame('org@example.com', $this->prop($account, 'email'));
    }

    public function testNewAccountIsNotConnected(): void
    {
        $account = new PaymentAccount(5, 'acct_123', 'org@example.com');
        $this->assertFalse($this->prop($account, 'isConnected'));
    }

    public function testConstructorInitialisesTimestamps(): void
    {
        $account = new PaymentAccount(5, 'acct_123', 'org@example.com');

        $this->assertInstanceOf(DateTime::class, $this->prop($account, 'createdAt'));
        $this->assertInstanceOf(DateTime::class, $this->prop($account, 'updatedAt'));
    }

    public function testEmptyReturnsInstance(): void
    {
        $this->assertInstanceOf(PaymentAccount::class, PaymentAccount::empty());
    }
}