<?php

namespace Controllers;

use Services\TeamAccessService;
use Services\UserService;
use Services\EventService;
use Exception;
use Models\User;
use database\Database;

class TeamAccessController
{
    private TeamAccessService $teamService;
    private UserService $userService;
    private EventService $eventService;

    public function __construct()
    {
        $this->teamService = new TeamAccessService();
        $this->userService = new UserService();
        $this->eventService = new EventService();
    }

    private function canManage(int $eventId): bool
    {
        $userId = (int) ($_SERVER["uid"] ?? 0);
        return $this->teamService->hasTeamAccess($userId, $eventId);
    }

    public function addMember() // Logic to add a team
    {
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        $email = $data["email"] ?? "";
        $eventId = $data["eventId"] ?? "";
        $role = trim($data["role"]) ?? "";

        if (empty($eventId) || empty($role) || empty($email)) {
            return [
                "success" => false,
                "message" => "Missing required fields"
            ];
        }

        try {
            if (!$this->canManage((int) $eventId)) {
                http_response_code(403);
                return [
                    "success" => false,
                    "message" => "Unauthorized: You do not have access to this event"
                ];
            }
        } catch (\Throwable $th) {
            http_response_code(404);
            return [
                "success" => false,
                "message" => "Event not found"
            ];
        }

        try {

            if (empty($email)) {
                return [
                    "success" => false,
                    "message" => "Email is required"
                ];
            }

            $user = User::where(["email" => $email]);
            if (count($user) === 0) {
                return [
                    "success" => false,
                    "message" => "Email Not Found."
                ];
            }
            $userId = $user[0]["id"];


            $this->teamService->addMember($userId, $eventId, $role);

            return [
                "success" => true,
                "message" => "Member added to the team successfully",
                "data" => null
            ];
        } catch (Exception $e) {
            return [
                "success" => false,
                "message" => "Error adding member to the team: " . $e->getMessage()
            ];
        }
    }


    public function removeMember() // Logic to remove a member from team
    {
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        $id = $data["id"] ?? "";

        if (empty($id)) {
            return [
                "success" => false,
                "message" => "Missing required fields"
            ];
        }

        $member = $this->teamService->getMember((int) $id);
        if (!$member) {
            http_response_code(404);
            return [
                "success" => false,
                "message" => "Team member not found"
            ];
        }

        try {
            if (!$this->canManage((int) $member["eventId"])) {
                http_response_code(403);
                return [
                    "success" => false,
                    "message" => "Unauthorized: You do not have access to this event"
                ];
            }
        } catch (\Throwable $th) {
            http_response_code(404);
            return [
                "success" => false,
                "message" => "Event not found"
            ];
        }

        try {
            $this->teamService->removeMember($id);
            return [
                "success" => true,
                "message" => "Member removed from the team successfully",
                "data" => null
            ];
        } catch (Exception $e) {
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error removing member from the team: " . $e->getMessage()
            ];
        }
    }

    public function getMembers() // Logic to get team members for an event
    {
        $userId = $_SERVER["uid"];
        $eventId = $_GET["eventId"] ?? "";

        $user = $this->userService->getUser($userId);

        if (!$user) {
            return [
                "success" => false,
                "message" => "User not found"
            ];
        }

        try {
            if (!$this->canManage((int) $eventId)) {
                http_response_code(403);
                return [
                    "success" => false,
                    "message" => "Unauthorized: You do not have access to this event"
                ];
            }
        } catch (\Throwable $th) {
            http_response_code(404);
            return [
                "success" => false,
                "message" => "Event not found"
            ];
        }

        try {
            $db = new Database();
            // team_access සහ users tables JOIN කර අවශ්‍ය දත්ත ලබා ගැනීම
            $sql = "SELECT 
                        ta.id, 
                        ta.role, 
                        ta.joinedAt, 
                        u.email, 
                        CONCAT(u.firstName, ' ', u.lastName) as name 
                    FROM team_access ta
                    JOIN users u ON ta.userId = u.id
                    WHERE ta.eventId = :eventId";

            $members = $db->queryAll($sql, [":eventId" => $eventId]);

            $formattedMembers = array_map(function ($member) {
                return [
                    "id" => $member["id"],
                    "name" => $member["name"],
                    "email" => $member["email"],
                    "role" => ucfirst(strtolower($member["role"])),
                ];
            }, $members);

            return [
                "success" => true,
                "message" => "Team members fetched successfully",
                "data" => $formattedMembers
            ];
        } catch (Exception $e) {
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error fetching team members: " . $e->getMessage()
            ];
        }
    }

    public function updateMemberRole() // Logic to update a team member's role
    {
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        $id = $data["id"] ?? "";
        $role = trim($data["role"]) ?? "";

        if (empty($id) || empty($role)) {
            return [
                "success" => false,
                "message" => "Missing required fields"
            ];
        }

        $member = $this->teamService->getMember((int) $id);
        if (!$member) {
            http_response_code(404);
            return [
                "success" => false,
                "message" => "Team member not found"
            ];
        }

        try {
            if (!$this->canManage((int) $member["eventId"])) {
                http_response_code(403);
                return [
                    "success" => false,
                    "message" => "Unauthorized: You do not have access to this event"
                ];
            }
        } catch (\Throwable $th) {
            http_response_code(404);
            return [
                "success" => false,
                "message" => "Event not found"
            ];
        }

        try {
            $this->teamService->updateMemberRole($id, $role);
            return [
                "success" => true,
                "message" => "Team member updated successfully",
                "data" => null
            ];
        } catch (Exception $e) {
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error updating team member: " . $e->getMessage()
            ];
        }
    }
}
