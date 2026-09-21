<?php

namespace Tests\Services;

use Services\TaskAuthorizationService;
use Services\TeamAccessService;
use Tests\TestCase;

class TaskAuthorizationServiceTest extends TestCase
{
    private function makeService(?TeamAccessService $teamAccess = null): TaskAuthorizationService
    {
        return new TaskAuthorizationService($teamAccess);
    }

    public function testCanManageEventDelegatesToTeamAccess(): void
    {
        $teamAccess = $this->createMock(TeamAccessService::class);
        $teamAccess->expects($this->once())->method('hasTeamAccess')->with(7, 3)->willReturn(true);

        $this->assertTrue($this->makeService($teamAccess)->canManageEvent(7, 3));
    }

    public function testCanManageEventReturnsFalseWithoutAccess(): void
    {
        $teamAccess = $this->createMock(TeamAccessService::class);
        $teamAccess->method('hasTeamAccess')->willReturn(false);

        $this->assertFalse($this->makeService($teamAccess)->canManageEvent(7, 3));
    }

    public function testOrganizerCanUpdateTask(): void
    {
        $teamAccess = $this->createMock(TeamAccessService::class);
        $teamAccess->expects($this->once())
            ->method('isOrganizer')
            ->with(3, 7)
            ->willReturn(true);

        $this->assertTrue($this->makeService($teamAccess)->canUpdateTask(7, [
            'eventId' => 3,
            'assignedTo' => 0,
        ]));
    }

    public function testNonOrganizerCannotEditOrganizerOwnedTask(): void
    {
        $teamAccess = $this->createMock(TeamAccessService::class);
        $teamAccess->method('isOrganizer')->willReturn(false);

        $this->assertFalse($this->makeService($teamAccess)->canUpdateTask(7, [
            'eventId' => 3,
            'assignedTo' => 0,
        ]));
    }
}