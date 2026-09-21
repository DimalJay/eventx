<?php

namespace Tests\Models;

use Models\Ticket;
use Tests\TestCase;

class TicketTest extends TestCase
{
    public function testConstructorStoresFields(): void
    {
        $ticket = new Ticket(11, 22, 'TICKET-ABC', 33, 44);

        $this->assertSame(11, $this->prop($ticket, 'eventId'));
        $this->assertSame(22, $this->prop($ticket, 'userId'));
        $this->assertSame('TICKET-ABC', $this->prop($ticket, 'ticketCode'));
        $this->assertSame(33, $this->prop($ticket, 'registerId'));
        $this->assertSame(44, $this->prop($ticket, 'paymentId'));
    }

    public function testPaymentIdDefaultsToZero(): void
    {
        $ticket = new Ticket(11, 22, 'TICKET-ABC', 33);
        $this->assertSame(0, $this->prop($ticket, 'paymentId'));
    }

    public function testEmptyReturnsInstance(): void
    {
        $this->assertInstanceOf(Ticket::class, Ticket::empty());
        $this->assertSame('', $this->prop(Ticket::empty(), 'ticketCode'));
    }
}