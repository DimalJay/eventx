<?php

namespace Controllers;

use Models\TeamAccess;
use Services\TeamAccessService;
use Exception;

class TeamController
{

    private TeamAccessService $teamService;
    public function __construct()
    {
        $this->teamService = new TeamAccessService();
    }

    public function addMember() // Logic to add a team
    {
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        $userId = $data["userId"] ?? "";
        $eventId = $data["eventId"] ?? "";
        $role = trim($data["role"]) ?? "";

        if (empty($userId) || empty($eventId) || empty($role)) {
            return [
                "success" => false,
                "message" => "Missing required fields"
            ];
        }

        $team = new TeamAccess($userId, $eventId, $role);
        try {
            $this->teamService->addMember($team);
        } catch (Exception $e) {
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error adding member to the team: " . $e->getMessage()
            ];
        }

        return [
            "success" => true,
            "message" => "Member added to the team succesfully",
            "data" => null
        ];
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

        try {
            $this->teamService->removeMember($id);
        } catch (Exception $e) {
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error removing member from the team: " . $e->getMessage()
            ];
        }

        return [
            "success" => true,
            "message" => "Member removed from the team succesfully",
            "data" => null
        ];
    }

    public function getMembers() // Logic to get team members for an event
    {
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        $eventId = $data["eventId"] ?? "";

        if (empty($eventId)) {
            return [
                "success" => false,
                "message" => "Missing required fields"
            ];
        }

        try {
            $members = $this->teamService->getMembers($eventId);
            return [
                "success" => true,
                "message" => "Team members fetched succesfully",
                "data" => $members
            ];
        } catch (Exception $e) {
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error fetching team members: " . $e->getMessage()
            ];
        }
    }

    public function updateUserRole() // Logic to update a team member's role
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
    
            try {
                $this->teamService->updateMemberRole($id, $role);
                return [
                    "success" => true,
                    "message" => "Team member updated succesfully",
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