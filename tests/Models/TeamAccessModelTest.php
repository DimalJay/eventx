<?php

namespace Tests\Models;

use DateTime;
use Models\TeamAccess;
use Tests\TestCase;

class TeamAccessModelTest extends TestCase
{
    public function testConstructorStoresFields(): void
    {
        $access = new TeamAccess(9, 4, 'organizer', 'ACCEPTED', 'Logistics');

        $this->assertSame(9, $this->prop($access, 'userId'));
        $this->assertSame(4, $this->prop($access, 'eventId'));
        $this->assertSame('ORGANIZER', $this->prop($access, 'role'));
        $this->assertSame('ACCEPTED', $this->prop($access, 'status'));
        $this->assertSame('Logistics', $this->prop($access, 'label'));
        $this->assertInstanceOf(DateTime::class, $this->prop($access, 'joinedAt'));
    }

    public function testRoleIsUppercasedButStatusIsNot(): void
    {
        $access = new TeamAccess(1, 2, 'member');

        $this->assertSame('MEMBER', $this->prop($access, 'role'));
        $this->assertSame('PENDING', $this->prop($access, 'status'));
        $this->assertNull($this->prop($access, 'label'));
    }

    public function testEmptyReturnsInstance(): void
    {
        $this->assertInstanceOf(TeamAccess::class, TeamAccess::empty());
    }
}