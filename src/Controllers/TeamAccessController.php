<?php

namespace Controllers;

use Services\TeamAccessService;
use Services\UserService;
use Services\EventService;
use Exception;

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

        try {
            $this->teamService->addMember($userId, $eventId, $role);

            return [
                "success" => true,
                "message" => "Member added to the team succesfully",
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

        try {
            $this->teamService->removeMember($id);
            return [
                "success" => true,
                "message" => "Member removed from the team succesfully",
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
        $event = $this->eventService->getEventWithUserId($userId, $eventId);
        if (!$user) {
            return [
                "success" => false,
                "message" => "User not found"
            ];
        }

        if (!$event) {
            return [
                "success" => false,
                "message" => "Event not found"
            ];
        }

        try {
            // $members = $this->teamService->getMembers($eventId);
            $members = $this->userService->getAllUsers();
            $members = array_map(function($member) {
                return [
                    "id" => $member["id"],
                    "firstName" => $member["firstName"],
                    "lastName" => $member["lastName"],
                ];
            }, $members);
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
