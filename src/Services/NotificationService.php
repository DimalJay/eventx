<?php

namespace Services;

use Models\Notification;

class NotificationService
{
    public static function createNotification(int $userId, string $title, string $message, string $type, mixed $extras = null): int
    {
        $notification = new Notification($userId, $title, $message, $type, $extras);
        return $notification->save();
    }

    public static function markAsRead(int $id): void
    {
        Notification::updateRecord(['id' => $id], [
            'isRead' => true,
            'readAt' => date('Y-m-d H:i:s')
        ]);
    }

    public static function markAllAsRead(): void
    {
        Notification::query(
            "UPDATE notifications SET isRead = 1, readAt = NOW() WHERE isRead = 0"
        );
    }

    public function getNotifications(string $userId, int $page = 1, int $limit = 10): array
    {
        $offset = ($page - 1) * $limit;
        $sql = "SELECT * FROM notifications WHERE userId = :userId ORDER BY createdAt DESC LIMIT :limit OFFSET :offset";
        $notifications = Notification::query($sql, ['userId' => $userId, 'limit' => $limit, 'offset' => $offset]);

        $countSql = "SELECT COUNT(*) as total FROM notifications WHERE userId = :userId";
        $totalResult = Notification::query($countSql, ['userId' => $userId]);
        $total = $totalResult['total'] ?? 0;

        return [
            'data' => $notifications,
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => ceil($total / $limit)
        ];
    }
}
