<?php

namespace Services;

use Contracts\TeamAccessServiceInterface;
use Models\TeamAccess;
use Models\Event;
use database\Database;
use Exception;

class TeamAccessService implements TeamAccessServiceInterface
{
    public function addMember($userId, $eventId, $role)
    {
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

    public function getMember(int $id)
    {
        $members = TeamAccess::where(["id" => $id]);
        return count($members) > 0 ? $members[0] : null;
    }

    public function updateMemberRole(int $id, string $role)
    {
        $members = TeamAccess::where(["id" => $id]);
        if (count($members) < 1) {
            throw new Exception("Team member does not exist");
        }
        TeamAccess::updateRecord(["id" => $id], ["role" => strtoupper($role), "status" => "ACTIVE"]);
    }

    public function hasTeamAccess(int $userId, int $eventId)
    {
        if ($this->isOrganizer($userId, $eventId)) {
            return true;
        }
        $members = TeamAccess::where(["userId" => $userId, "eventId" => $eventId]);
        return count($members) > 0;
    }

    public function getMembersWithDetails(int $eventId): array
    {
        $organizerId = $this->getOrganizerId($eventId);

        $db = new Database();
        $sql = "SELECT 
                    ta.id, 
                    ta.userId,
                    ta.role, 
                    ta.joinedAt, 
                    u.email, 
                    CONCAT(u.firstName, ' ', u.lastName) as name 
                FROM team_access ta
                JOIN users u ON ta.userId = u.id
                WHERE ta.eventId = :eventId";

        $members = $db->queryAll($sql, [":eventId" => $eventId]);

        if ($organizerId !== null) {
            $members = array_filter($members, function ($m) use ($organizerId) {
                return (int) ($m["userId"] ?? 0) !== $organizerId;
            });
        }

        $formatted = array_values(array_map(function ($member) {
            return [
                "id" => (int) $member["id"],
                "name" => $member["name"],
                "email" => $member["email"],
                "role" => strtoupper(trim($member["role"])),
                "isOrganizer" => false,
            ];
        }, $members));

        if ($organizerId !== null) {
            $organizer = \Models\User::where(["id" => $organizerId]);
            if (count($organizer) > 0) {
                array_unshift($formatted, [
                    "id" => 0,
                    "name" => trim(($organizer[0]["firstName"] ?? "") . " " . ($organizer[0]["lastName"] ?? "")),
                    "email" => $organizer[0]["email"] ?? "",
                    "role" => "ORGANIZER",
                    "isOrganizer" => true,
                ]);
            }
        }

        return $formatted;
    }

    public function getOrganizerId(int $eventId): ?int
    {
        $event = Event::where(["id" => $eventId]);
        if (count($event) < 1) {
            return null;
        }
        return !empty($event[0]["organizerId"]) ? (int) $event[0]["organizerId"] : null;
    }

    public function isOrganizer(int $eventId, int $userId): bool
    {
        $event = Event::where(["id" => $eventId]);
        if (count($event) < 1) {
            throw new Exception("Event does not exist");
        }
        return (int) ($event[0]["organizerId"] ?? 0) === $userId;
    }
}
