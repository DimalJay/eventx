<?php

namespace Tests\Models;

use DateTime;
use Models\Payment;
use Tests\TestCase;

class PaymentModelTest extends TestCase
{
    public function testConstructorStoresFields(): void
    {
        $payment = new Payment(10, 3, 49.99);

        $this->assertSame(10, $this->prop($payment, 'userId'));
        $this->assertSame(3, $this->prop($payment, 'registerId'));
        $this->assertSame(49.99, $this->prop($payment, 'amount'));
        $this->assertInstanceOf(DateTime::class, $this->prop($payment, 'paymentAt'));
    }

    public function testEmptyReturnsInstance(): void
    {
        $payment = Payment::empty();

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertSame(0, $this->prop($payment, 'userId'));
        $this->assertSame(0, $this->prop($payment, 'registerId'));
        $this->assertSame(0.0, $this->prop($payment, 'amount'));
    }
}