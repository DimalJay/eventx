<?php

namespace Contracts;

interface TeamAccessServiceInterface
{
    public function addMember($userId, $eventId, $role);

    public function removeMember(int $id);

    public function getMember(int $id);

    public function updateMemberRole(int $id, string $role);

    public function hasTeamAccess(int $userId, int $eventId);

    public function isOrganizer(int $eventId, int $userId): bool;

    public function getMembersWithDetails(int $eventId): array;
}
