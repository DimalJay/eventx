<?php

namespace Tests\Models;

use DateTime;
use Models\Checkin;
use Tests\TestCase;

class CheckinTest extends TestCase
{
    public function testConstructorStoresFields(): void
    {
        $checkin = new Checkin(10, 3, 7, 'QR_CODE');

        $this->assertSame(10, $this->prop($checkin, 'registrationId'));
        $this->assertSame(3, $this->prop($checkin, 'eventId'));
        $this->assertSame(7, $this->prop($checkin, 'userId'));
        $this->assertSame('QR_CODE', $this->prop($checkin, 'verifyMethod'));
        $this->assertInstanceOf(DateTime::class, $this->prop($checkin, 'checkinAt'));
    }

    public function testEmptyReturnsInstance(): void
    {
        $this->assertInstanceOf(Checkin::class, Checkin::empty());
    }
}