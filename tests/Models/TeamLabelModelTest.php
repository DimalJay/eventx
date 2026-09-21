<?php

namespace Tests\Models;

use DateTime;
use Models\TeamLabel;
use Tests\TestCase;

class TeamLabelModelTest extends TestCase
{
    public function testConstructorStoresFields(): void
    {
        $label = new TeamLabel(5, '["Logistics","Sound"]');

        $this->assertSame(5, $this->prop($label, 'eventId'));
        $this->assertSame('["Logistics","Sound"]', $this->prop($label, 'labels'));
        $this->assertInstanceOf(DateTime::class, $this->prop($label, 'updatedAt'));
    }

    public function testDefaultsWhenCalledWithNoArguments(): void
    {
        $label = new TeamLabel();

        $this->assertSame(0, $this->prop($label, 'eventId'));
        $this->assertNull($this->prop($label, 'labels'));
    }

    public function testEmptyReturnsInstance(): void
    {
        $this->assertInstanceOf(TeamLabel::class, TeamLabel::empty());
    }
}