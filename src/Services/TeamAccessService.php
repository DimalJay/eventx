<?php

namespace Services;

use Models\TeamAccess;
use Models\Event;
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

    public function hasTeamAccess(int $userId, int $eventId)
    {
        $bpo = $this->bypassOrganizerCheck($userId, $eventId);
        if ($bpo) {
            return true;
        }
        $members = TeamAccess::where(["userId" => $userId, "eventId" => $eventId]);
        if(count($members) < 1) {
            return false;
        }else {
            $status = $members[0]['status'] ?? 'PENDING';
            return $status === 'ACTIVE';
        }
    }

    private function bypassOrganizerCheck(int $userId, int $eventId)
    {
        // Check if the user is the organizer of the event
        $event = Event::where(["id" => $eventId]);
        if (count($event) < 1) {
            throw new Exception("Event does not exist");
        }
        return $event[0]['organizerId'] == $userId;
    }
}