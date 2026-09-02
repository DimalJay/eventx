<?php

namespace Controllers;

use Services\NotificationService;
use Services\UserService;

class NotificationController
{
    private NotificationService $notificationService;
    private UserService $userService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
        $this->userService = new UserService();
    }

    public function getNotifications()
    {
        $userId = (int) ($_SERVER["uid"] ?? 0);
        $page = max(1, (int) ($_GET["page"] ?? 1));
        $limit = max(1, min(50, (int) ($_GET["limit"] ?? 10)));

        $user = $this->userService->getUser($userId);
        if (!$user) {
            http_response_code(401);
            return [
                "success" => false,
                "message" => "Unauthorized: User not found"
            ];
        }

        $result = $this->notificationService->getForUser($userId, $page, $limit);

        return [
            "success" => true,
            "message" => "Notifications retrieved successfully",
            "data" => $result["items"],
            "page" => $page,
            "limit" => $limit,
            "total" => $result["total"],
            "totalPages" => (int) ceil($result["total"] / $limit),
        ];
    }

    public function markNotificationAsRead()
    {
        $userId = (int) ($_SERVER["uid"] ?? 0);
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        $id = isset($data["id"]) ? (int) $data["id"] : 0;
        if (empty($id)) {
            return [
                "success" => false,
                "message" => "Notification ID is required"
            ];
        }

        try {
            $updated = $this->notificationService->markAsRead($userId, $id);
            if (!$updated) {
                http_response_code(404);
                return [
                    "success" => false,
                    "message" => "Notification not found or access denied"
                ];
            }
            return [
                "success" => true,
                "message" => "Notification marked as read"
            ];
        } catch (\Throwable $th) {
            return [
                "success" => false,
                "message" => "Error marking notification as read: " . $th->getMessage()
            ];
        }
    }

    public function markAllNotificationsAsRead()
    {
        $userId = (int) ($_SERVER["uid"] ?? 0);

        try {
            $this->notificationService->markAllAsRead($userId);
            return [
                "success" => true,
                "message" => "All notifications marked as read"
            ];
        } catch (\Throwable $th) {
            return [
                "success" => false,
                "message" => "Error marking notifications as read: " . $th->getMessage()
            ];
        }
    }
}
