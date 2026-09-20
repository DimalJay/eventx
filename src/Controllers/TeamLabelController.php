<?php

namespace Controllers;

use Services\TeamLabelService;
use Services\TeamAccessService;
use Helpers\APIResponse;

class TeamLabelController
{
    private TeamLabelService $labelService;
    private TeamAccessService $teamAccessService;

    public function __construct()
    {
        $this->labelService = new TeamLabelService();
        $this->teamAccessService = new TeamAccessService();
    }

    private function currentUserId(): int
    {
        return (int) ($_SERVER["uid"] ?? 0);
    }

    private function requireManageAccess(int $eventId): ?array
    {
        try {
            if (!$this->teamAccessService->hasTeamAccess($this->currentUserId(), $eventId)) {
                return APIResponse::error("Unauthorized: You do not have access to this event", 403);
            }
        } catch (\Throwable $th) {
            return APIResponse::error("Event not found", 404);
        }
        return null;
    }

    public function getLabels()
    {
        $eventId = $_GET["eventId"] ?? "";
        if (empty($eventId)) {
            return APIResponse::error("Event ID is required");
        }

        $denied = $this->requireManageAccess((int) $eventId);
        if ($denied !== null) return $denied;

        try {
            $labels = $this->labelService->getLabels((int) $eventId);
        } catch (\Throwable $th) {
            return APIResponse::error("Error fetching labels: " . $th->getMessage(), 500);
        }

        return APIResponse::success("Labels fetched successfully", [
            "eventId" => (int) $eventId,
            "labels" => $labels,
        ]);
    }

    public function updateLabels()
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true) ?? [];

        $eventId = $data["eventId"] ?? "";
        $labels = trim($data["labels"] ?? "");

        if (empty($eventId)) {
            return APIResponse::error("Event ID is required");
        }

        $denied = $this->requireManageAccess((int) $eventId);
        if ($denied !== null) return $denied;

        try {
            $this->labelService->updateLabels((int) $eventId, $labels);
        } catch (\Throwable $th) {
            return APIResponse::error("Error updating labels: " . $th->getMessage(), 500);
        }

        return APIResponse::success("Labels updated successfully", [
            "eventId" => (int) $eventId,
            "labels" => $labels,
        ]);
    }
}