<?php

namespace Controllers;

use Services\TeamAccessService;
use Services\UserService;
use Services\EventService;
use Services\NotificationService;
use Exception;
use Models\User;
use Models\Event;
use Helpers\EmailHelper;
use database\Database;

class TeamAccessController
{
    private TeamAccessService $teamService;
    private UserService $userService;
    private EventService $eventService;
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->teamService = new TeamAccessService();
        $this->userService = new UserService();
        $this->eventService = new EventService();
        $this->notificationService = new NotificationService();
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

            // --- Notification + Email ---
            $addedUser = User::where(["id" => $userId]);
            $event = Event::where(["id" => $eventId]);
            if (count($addedUser) > 0 && count($event) > 0) {
                $eventTitle = $event[0]['title'];
                $firstName = $addedUser[0]['firstName'];
                $addedEmail = $addedUser[0]['email'];
                $roleLabel = ucfirst(strtolower($role));

                // Notify the added member
                $this->notificationService->notifyTeamMemberAdded((int) $userId, $eventTitle, (int) $eventId);

                // Send email to the added member
                EmailHelper::sendWithTemplate(
                    $addedEmail,
                    "You've been added to {$eventTitle}'s team",
                    "team_access",
                    [
                        "firstName" => $firstName,
                        "eventTitle" => $eventTitle,
                        "roleLabel" => $roleLabel,
                        "eventLink" => EmailHelper::frontendUrl() . "/events/{$eventId}",
                    ]
                );

                // Notify the organizer
                $organizerId = (int) $event[0]['organizerId'];
                if ($organizerId !== (int) $userId) {
                    $this->notificationService->notifyOrganizer(
                        $organizerId,
                        $eventTitle,
                        (int) $eventId,
                        "New team member",
                        "{$firstName} ({$addedEmail}) has been added to the team as {$roleLabel}."
                    );
                }
            }

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
            // --- Notification + Email (before removal) ---
            $removedUser = User::where(["id" => $member["userId"]]);
            $event = Event::where(["id" => $member["eventId"]]);
            if (count($removedUser) > 0 && count($event) > 0) {
                $eventTitle = $event[0]['title'];
                $firstName = $removedUser[0]['firstName'];
                $removedEmail = $removedUser[0]['email'];

                // Notify the removed member
                $this->notificationService->notifyTeamMemberRemoved((int) $member["userId"], $eventTitle, (int) $member["eventId"]);

                // Send email to the removed member
                EmailHelper::sendWithTemplate(
                    $removedEmail,
                    "You've been removed from {$eventTitle}'s team",
                    "team_removed",
                    [
                        "firstName" => $firstName,
                        "eventTitle" => $eventTitle,
                    ]
                );

                // Notify the organizer
                $organizerId = (int) $event[0]['organizerId'];
                $currentUser = $_SERVER["uid"] ?? 0;
                if ($organizerId !== (int) $member["userId"] && $organizerId !== (int) $currentUser) {
                    $this->notificationService->notifyOrganizer(
                        $organizerId,
                        $eventTitle,
                        (int) $member["eventId"],
                        "Team member removed",
                        "{$firstName} ({$removedEmail}) has been removed from the team."
                    );
                }
            }

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
            $oldRole = $member["role"];
            $this->teamService->updateMemberRole($id, $role);

            // --- Notification + Email ---
            $memberUser = User::where(["id" => $member["userId"]]);
            $event = Event::where(["id" => $member["eventId"]]);
            if (count($memberUser) > 0 && count($event) > 0) {
                $eventTitle = $event[0]['title'];
                $firstName = $memberUser[0]['firstName'];
                $memberEmail = $memberUser[0]['email'];
                $roleLabel = ucfirst(strtolower($role));

                // Notify the member whose role changed
                $this->notificationService->notifyTeamMemberRoleChanged(
                    (int) $member["userId"],
                    $eventTitle,
                    (int) $member["eventId"],
                    $role
                );

                // Send email to the member
                EmailHelper::sendWithTemplate(
                    $memberEmail,
                    "Your role on {$eventTitle} has been updated",
                    "team_role_changed",
                    [
                        "firstName" => $firstName,
                        "eventTitle" => $eventTitle,
                        "roleLabel" => $roleLabel,
                        "eventLink" => EmailHelper::frontendUrl() . "/events/" . $member["eventId"],
                    ]
                );

                // Notify the organizer
                $organizerId = (int) $event[0]['organizerId'];
                if ($organizerId !== (int) $member["userId"]) {
                    $this->notificationService->notifyOrganizer(
                        $organizerId,
                        $eventTitle,
                        (int) $member["eventId"],
                        "Team role changed",
                        "{$firstName}'s role has been changed from " . ucfirst(strtolower($oldRole)) . " to {$roleLabel}."
                    );
                }
            }

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
