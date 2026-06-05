<?php

namespace Services;

use Models\TeamAccess;
use Exception;

class TeamAccessService
{
    public function __construct()
    {
    }

    public function addMember(TeamAccess $member)
    {
        return $member->save();
    }

    public function removeMember(int $id)
    {
        return TeamAccess::deleteRecord(["id" => $id]);
    }

    public function getMembers(int $eventId)
    {
        return TeamAccess::where(["eventId" => $eventId]);
    }

    public function updateMemberRole(int $id, string $role)
    {
        $members = TeamAccess::where(["id" => $id]);
        if (count($members) < 1) {
            throw new Exception("Team member does not exist");
        }
        TeamAccess::updateRecord(["id" => $id], ["role" => $role]);
    }
}