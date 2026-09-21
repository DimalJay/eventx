<?php

namespace Controllers;

use Contracts\TeamAccessServiceInterface;
use Contracts\TeamNotifierInterface;
use Services\TeamAccessService;
use Services\Team\TeamNotifier;
use Helpers\APIResponse;
use Helpers\EmailHelper;
use Models\User;
use Exception;

class TeamAccessController
{
    private TeamAccessServiceInterface $teamService;
    private TeamNotifierInterface $notifier;

    public function __construct(?TeamAccessServiceInterface $teamService = null, ?TeamNotifierInterface $notifier = null)
    {
        $this->teamService = $teamService ?? new TeamAccessService();
        $this->notifier = $notifier ?? new TeamNotifier();
    }

    private function parseJsonInput(): array
    {
        $json = file_get_contents('php://input');
        return json_decode($json, true) ?? [];
    }

    private function currentUserId(): int
    {
        return (int) ($_SERVER["uid"] ?? 0);
    }

    /**
     * Returns an error response if the user lacks team access, null on success.
     */
    private function requireManageAccess(int $eventId): ?array
    {
        try {
            if (!$this->teamService->hasTeamAccess($this->currentUserId(), $eventId)) {
                return APIResponse::error("Unauthorized: You do not have access to this event", 403);
            }
        } catch (\Throwable $th) {
            return APIResponse::error("Event not found", 404);
        }
        return null;
    }

    /**
     * Returns an error response if the given user IS the event organizer, null on success.
     */
    private function requireNotOrganizer(int $eventId, int $userId, string $message): ?array
    {
        try {
            if ($this->teamService->isOrganizer($eventId, $userId)) {
                return APIResponse::error($message, 403);
            }
        } catch (\Throwable $th) {
            return APIResponse::error("Event not found", 404);
        }
        return null;
    }

    public function addMember()
    {
        $data = $this->parseJsonInput();
        $email = trim($data["email"] ?? "");
        $eventId = $data["eventId"] ?? "";
        $role = trim($data["role"] ?? "");
        $label = isset($data["label"]) && trim($data["label"]) !== "" ? trim($data["label"]) : null;

        if (empty($eventId) || empty($role) || empty($email)) {
            return APIResponse::error("Missing required fields");
        }

        $denied = $this->requireManageAccess((int) $eventId);
        if ($denied !== null) return $denied;

        $users = User::where(["email" => $email]);
        if (count($users) === 0) {
            return APIResponse::error("Email Not Found.");
        }
        $userId = (int) $users[0]["id"];

        $organizerCheck = $this->requireNotOrganizer(
            (int) $eventId,
            $userId,
            "The event organizer is already on the team as the permanent owner"
        );
        if ($organizerCheck !== null) return $organizerCheck;

        $teamAccessId = 0;
        try {
            $teamAccessId = (int) $this->teamService->addMember($userId, $eventId, $role, $label);
        } catch (Exception $e) {
            return APIResponse::error("Error adding member to the team: " . $e->getMessage(), 500);
        }

        $this->notifier->notifyMemberAdded($userId, (int) $eventId, $role, $teamAccessId);

        return APIResponse::success("Team invitation sent successfully");
    }

    public function respondToTeamInvitation()
    {
        $teamAccessId = $_GET['teamAccessId'] ?? '';
        $response = $_GET['response'] ?? ''; // accept or decline
        $token = $_GET['token'] ?? '';

        if (empty($teamAccessId) || empty($response) || empty($token)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Missing response parameters."]);
            exit;
        }

        $secretKey = \Helpers\Config::requireSecret('APP_SECRET');
        $expectedToken = hash_hmac('sha256', 'team-' . $teamAccessId . '-' . $response, $secretKey);

        if (!hash_equals($expectedToken, $token)) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Invalid security token."]);
            exit;
        }

        $member = $this->teamService->getMember((int) $teamAccessId);
        if (!$member) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Team invitation record not found."]);
            exit;
        }

        $eventId = (int) $member['eventId'];
        $event = (new \Services\EventService())->getEvent($eventId);
        $eventTitle = $event ? $event['title'] : 'the event';

        try {
            if ($response === 'accept') {
                \Models\TeamAccess::updateRecord(["id" => (int) $teamAccessId], ["status" => "ACTIVE"]);
            } else {
                $this->teamService->removeMember((int) $teamAccessId);
            }

            // Redirect to Next.js frontend status page
            $redirectUrl = EmailHelper::frontendUrl() . "/invitation/status?success=true&response=" . $response 
                . "&eventTitle=" . urlencode($eventTitle) . "&eventId=" . $eventId . "&type=team";
            header("Location: " . $redirectUrl);
            exit;

        } catch (\Throwable $th) {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Error recording response: " . $th->getMessage()]);
            exit;
        }
    }

    public function removeMember()
    {
        $data = $this->parseJsonInput();
        $id = $data["id"] ?? null;

        if ($id === null || $id === "") {
            return APIResponse::error("Missing required fields");
        }

        if ((int) $id === 0) {
            return APIResponse::error("The event organizer is permanent and cannot be removed", 403);
        }

        $member = $this->teamService->getMember((int) $id);
        if (!$member) {
            return APIResponse::error("Team member not found", 404);
        }

        $denied = $this->requireManageAccess((int) $member["eventId"]);
        if ($denied !== null) return $denied;

        $organizerCheck = $this->requireNotOrganizer(
            (int) $member["eventId"],
            (int) $member["userId"],
            "The event organizer cannot be removed"
        );
        if ($organizerCheck !== null) return $organizerCheck;

        $this->notifier->notifyMemberRemoved((int) $member["userId"], (int) $member["eventId"]);

        try {
            $this->teamService->removeMember($id);
        } catch (Exception $e) {
            return APIResponse::error("Error removing member from the team: " . $e->getMessage(), 500);
        }

        return APIResponse::success("Member removed from the team successfully");
    }

    public function getMembers()
    {
        $eventId = $_GET["eventId"] ?? "";
        if (empty($eventId)) {
            return APIResponse::error("Event ID is required");
        }

        $denied = $this->requireManageAccess((int) $eventId);
        if ($denied !== null) return $denied;

        try {
            $members = $this->teamService->getMembersWithDetails((int) $eventId);
        } catch (Exception $e) {
            return APIResponse::error("Error fetching team members: " . $e->getMessage(), 500);
        }

        return APIResponse::success("Team members fetched successfully", $members);
    }

    public function updateMemberRole()
    {
        $data = $this->parseJsonInput();
        $id = $data["id"] ?? null;
        $role = isset($data["role"]) ? strtoupper(trim($data["role"])) : "";

        if ($id === null || $id === "" || $role === "") {
            return APIResponse::error("Missing required fields");
        }

        if ((int) $id === 0) {
            return APIResponse::error("The event organizer's role cannot be changed", 403);
        }

        $member = $this->teamService->getMember((int) $id);
        if (!$member) {
            return APIResponse::error("Team member not found", 404);
        }

        $denied = $this->requireManageAccess((int) $member["eventId"]);
        if ($denied !== null) return $denied;

        $organizerCheck = $this->requireNotOrganizer(
            (int) $member["eventId"],
            (int) $member["userId"],
            "The event organizer's role cannot be changed"
        );
        if ($organizerCheck !== null) return $organizerCheck;

        $oldRole = $member["role"];
        try {
            $this->teamService->updateMemberRole($id, $role);
        } catch (Exception $e) {
            return APIResponse::error("Error updating team member: " . $e->getMessage(), 500);
        }

        $this->notifier->notifyMemberRoleChanged((int) $member["userId"], (int) $member["eventId"], $oldRole, $role);

        return APIResponse::success("Team member updated successfully");
    }

    public function updateMemberLabel()
    {
        $data = $this->parseJsonInput();
        $id = $data["id"] ?? null;
        $label = isset($data["label"]) ? trim($data["label"]) : "";

        if ($id === null || $id === "") {
            return APIResponse::error("Missing required fields");
        }

        if ((int) $id === 0) {
            return APIResponse::error("The event organizer's label cannot be changed", 403);
        }

        $member = $this->teamService->getMember((int) $id);
        if (!$member) {
            return APIResponse::error("Team member not found", 404);
        }

        $denied = $this->requireManageAccess((int) $member["eventId"]);
        if ($denied !== null) return $denied;

        $organizerCheck = $this->requireNotOrganizer(
            (int) $member["eventId"],
            (int) $member["userId"],
            "The event organizer's label cannot be changed"
        );
        if ($organizerCheck !== null) return $organizerCheck;

        try {
            $this->teamService->updateMemberLabel((int) $id, $label);
        } catch (Exception $e) {
            return APIResponse::error("Error updating team member label: " . $e->getMessage(), 500);
        }

        return APIResponse::success("Team member label updated successfully", ["label" => $label]);
    }
}
