<?php

namespace Services;

use Contracts\TaskAuthorizationInterface;
use Models\TeamAccess;

class TaskAuthorizationService implements TaskAuthorizationInterface
{
    private TeamAccessService $teamAccessService;

    public function __construct(?TeamAccessService $teamAccessService = null)
    {
        $this->teamAccessService = $teamAccessService ?? new TeamAccessService();
    }

    public function canManageEvent(int $userId, int $eventId): bool
    {
        return $this->teamAccessService->hasTeamAccess($userId, $eventId);
    }

    public function canUpdateTask(int $userId, array $task): bool
    {
        $eventId = (int) $task["eventId"];

        if ($this->teamAccessService->isOrganizer($eventId, $userId)) {
            return true;
        }

        $assignedTo = (int) ($task["assignedTo"] ?? -1);
        if ($assignedTo === 0) {
            return false;
        }

        $member = TeamAccess::where(["id" => $assignedTo]);
        return count($member) > 0 && (int) ($member[0]["userId"] ?? 0) === $userId;
    }
}