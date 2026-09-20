<?php

namespace Contracts;

interface TaskAuthorizationInterface
{
    public function canManageEvent(int $userId, int $eventId): bool;

    public function canUpdateTask(int $userId, array $task): bool;
}