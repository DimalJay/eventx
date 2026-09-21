<?php

namespace Contracts;

interface TeamNotifierInterface
{
    public function notifyMemberAdded(int $memberId, int $eventId, string $role, int $teamAccessId = 0): void;

    public function notifyMemberRemoved(int $memberId, int $eventId): void;

    public function notifyMemberRoleChanged(int $memberId, int $eventId, string $oldRole, string $newRole): void;
}
