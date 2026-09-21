<?php

namespace Tests\Models;

use DateTime;
use Models\Guest;
use Tests\TestCase;

class GuestTest extends TestCase
{
    public function testConstructorStoresFields(): void
    {
        $guest = new Guest(5, 'g@x.com', 'speaker', 'confirmed');

        $this->assertSame(5, $guest->getEventId());
        $this->assertSame('g@x.com', $guest->getEmail());
        $this->assertSame('speaker', $guest->getRole());
        $this->assertSame('confirmed', $guest->getStatus());
    }

    public function testDefaultsToInvitedStatus(): void
    {
        $guest = new Guest();
        $this->assertSame('invited', $guest->getStatus());
    }

    public function testConstructorInitialisesCreatedAt(): void
    {
        $guest = new Guest(5, 'g@x.com', 'speaker');
        $this->assertInstanceOf(DateTime::class, $this->prop($guest, 'createdAt'));
    }

    public function testEmptyUsesDefaults(): void
    {
        $empty = Guest::empty();

        $this->assertSame(0, $empty->getEventId());
        $this->assertSame('', $empty->getEmail());
        $this->assertSame('', $empty->getRole());
        $this->assertSame('invited', $empty->getStatus());
    }
}