<?php

namespace Controllers;

use Services\NotificationService;

class NotificationController
{
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
    }

    public function markAsRead()
    {
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        $id = $data['id'] ?? $_GET['id'] ?? null;
        if (empty($id)) {
            return [
                "success" => false,
                "message" => "Notification ID is required"
            ];
        }

        NotificationService::markAsRead((int)$id);

        return [
            "success" => true,
            "message" => "Notification marked as read"
        ];
    }

    public function markAllAsRead()
    {
        NotificationService::markAllAsRead();

        return [
            "success" => true,
            "message" => "All notifications marked as read"
        ];
    }

    public function getNotifications()
    {
        $userId = $_SERVER['uid'] ?? null;
        if (!$userId) {
            return [
                "success" => false,
                "message" => "User not authenticated",
                "data" => null,
            ];
        }
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = max(1, min(100, (int)($_GET['limit'] ?? 10)));

        $result = $this->notificationService->getNotifications($userId, $page, $limit);

        return [
            "success" => true,
            "message" => "Notifications retrieved successfully",
            "data" => $result['data'],
            "page" => $result['page'],
            "limit" => $result['limit'],
            "total" => $result['total'],
            "totalPages" => $result['totalPages']
        ];
    }
}
