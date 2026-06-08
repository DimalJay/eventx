<?php

namespace Services;

use Models\TeamAccess;
use Exception;

class TeamAccessService
{
    public function __construct()
    {
    }

    public function addMember($userId, $eventId, $role)
    {
        // $member = new TeamAccess($userId, $eventId, $role);
        $members = TeamAccess::where(["userId" => $userId, "eventId" => $eventId]);
        if (count($members) > 0) {
            throw new Exception("User is already a member of the team");
        }
        $member = new TeamAccess($userId, $eventId, $role);
        return $member->save();
        
    }

    public function removeMember(int $id)
    {

        $ret = TeamAccess::deleteRecord(["id" => $id]);
        if ($ret < 1) {
            throw new Exception("Team member does not exist");
        }
        return $ret;
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