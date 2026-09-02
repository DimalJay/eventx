<?php

namespace Services;

use Models\Notification;
use Models\TeamAccess;
use Models\Event;
use database\Database;

class NotificationService
{
    public function __construct()
    {
    }

    public function create(int $userId, string $title, string $message, string $type = 'General', ?array $extras = null): int
    {
        $notification = new Notification(
            $title,
            $message,
            $userId,
            $type,
            $extras !== null && count($extras) > 0 ? json_encode($extras) : null
        );
        return $notification->save();
    }

    /**
     * Resolve the users to notify for an event: the organizer plus every
     * ACTIVE coordinator in the event's team.
     *
     * @return int[] deduplicated user ids
     */
    public function getEventRecipients(int $eventId): array
    {
        $recipients = [];

        $event = Event::where(["id" => $eventId]);
        if (count($event) > 0 && !empty($event[0]['organizerId'])) {
            $recipients[(int) $event[0]['organizerId']] = true;
        }

        $coordinators = TeamAccess::where([
            "eventId" => $eventId,
            "role" => "COORDINATOR",
            "status" => "ACTIVE",
        ]);
        foreach ($coordinators as $coordinator) {
            $recipients[(int) $coordinator['userId']] = true;
        }

        return array_keys($recipients);
    }

    private function notifyUsers(array $userIds, string $title, string $message, string $type, ?array $extras): void
    {
        foreach ($userIds as $userId) {
            try {
                $this->create((int) $userId, $title, $message, $type, $extras);
            } catch (\Throwable $th) {
                error_log("NotificationService Error: " . $th->getMessage());
            }
        }
    }

    /**
     * Notify the event organizer and all ACTIVE coordinators that a new
     * attendee registered for an event.
     *
     * @param array $event  Event row from EventService::getEvent()
     * @param string $attendeeName  e.g. "John Doe"
     */
    public function notifyNewRegistration(array $event, string $attendeeName): void
    {
        $eventId = (int) $event['id'];
        $recipients = $this->getEventRecipients($eventId);

        $this->notifyUsers(
            $recipients,
            "New registration",
            $attendeeName . " registered for " . ($event['title'] ?? "your event") . ".",
            'Registration',
            ["eventId" => $eventId]
        );
    }

    /**
     * Notify a team member that they were assigned a new task.
     *
     * @param array $task     Task row (must contain id, title, assignedTo, eventId)
     * @param string $eventTitle  Event title the task belongs to
     */
    public function notifyTaskAssigned(array $task, string $eventTitle): void
    {
        $this->notifyUsers(
            [(int) $task['assignedTo']],
            "New Task Assigned",
            "You have been assigned a new task: " . ($task['title'] ?? "") . ".",
            'task_assignment',
            ["taskId" => (int) $task['id'], "eventId" => (int) $task['eventId']]
        );
    }

    /**
     * Notify the event organizer and all ACTIVE coordinators that a task
     * belonging to their event was updated.
     *
     * @param array $task   Task row
     */
    public function notifyTaskUpdated(array $task): void
    {
        $eventId = (int) $task['eventId'];
        $recipients = $this->getEventRecipients($eventId);

        $this->notifyUsers(
            $recipients,
            "Task Updated",
            "Task \"" . ($task['title'] ?? "") . "\" was updated.",
            'task_update',
            ["taskId" => (int) $task['id'], "eventId" => $eventId]
        );
    }

    /**
     * Notify a user that they were added to an event's team.
     */
    public function notifyTeamMemberAdded(int $userId, string $eventTitle, int $eventId): void
    {
        $this->create(
            $userId,
            "Added to team",
            "You have been added to the team for \"{$eventTitle}\".",
            'team_access',
            ["eventId" => $eventId]
        );
    }

    /**
     * Notify a user that they were removed from an event's team.
     */
    public function notifyTeamMemberRemoved(int $userId, string $eventTitle, int $eventId): void
    {
        $this->create(
            $userId,
            "Removed from team",
            "You have been removed from the team for \"{$eventTitle}\".",
            'team_removed',
            ["eventId" => $eventId]
        );
    }

    /**
     * Notify a user that their role was changed on an event's team.
     */
    public function notifyTeamMemberRoleChanged(int $userId, string $eventTitle, int $eventId, string $newRole): void
    {
        $roleLabel = ucfirst(strtolower($newRole));
        $this->create(
            $userId,
            "Role updated",
            "Your role on \"{$eventTitle}\" has been changed to {$roleLabel}.",
            'team_role_changed',
            ["eventId" => $eventId, "role" => $newRole]
        );
    }

    /**
     * Notify the event organizer about a team change.
     */
    public function notifyOrganizer(int $organizerId, string $eventTitle, int $eventId, string $title, string $message): void
    {
        $this->create(
            $organizerId,
            $title,
            $message,
            'team_update',
            ["eventId" => $eventId]
        );
    }

    public function getForUser(int $userId, int $page, int $limit): array
    {
        $offset = ($page - 1) * $limit;

        $db = new Database();
        $total = (int) $db->query(
            "SELECT COUNT(*) AS cnt FROM `notifications` WHERE `userId` = :userId",
            [":userId" => $userId]
        )['cnt'];

        $rows = $db->queryAll(
            "SELECT * FROM `notifications` WHERE `userId` = :userId ORDER BY `createdAt` DESC LIMIT :limit OFFSET :offset",
            [
                ":userId" => $userId,
                ":limit" => (int) $limit,
                ":offset" => $offset,
            ]
        );

        return [
            "total" => $total,
            "items" => array_map([self::class, 'format'], $rows),
        ];
    }

    public function markAsRead(int $userId, int $id): bool
    {
        $db = new Database();
        $stmt = $db->execute(
            "UPDATE `notifications` SET `isRead` = 1, `status` = 'read', `readAt` = NOW() WHERE `id` = :id AND `userId` = :userId",
            [":id" => $id, ":userId" => $userId]
        );
        return $stmt->rowCount() > 0;
    }

    public function markAllAsRead(int $userId): int
    {
        $db = new Database();
        $db->execute(
            "UPDATE `notifications` SET `isRead` = 1, `status` = 'read', `readAt` = NOW() WHERE `userId` = :userId AND `isRead` = 0",
            [":userId" => $userId]
        );
        return $db->query(
            "SELECT COUNT(*) AS cnt FROM `notifications` WHERE `userId` = :userId AND `isRead` = 1",
            [":userId" => $userId]
        )['cnt'];
    }

    private static function format(array $row): array
    {
        return [
            "id" => (int) ($row['id'] ?? 0),
            "title" => $row['title'] ?? "",
            "message" => $row['message'] ?? "",
            "userId" => (int) ($row['userId'] ?? 0),
            "status" => $row['status'] ?? "unread",
            "type" => $row['type'] ?? "General",
            "createdAt" => $row['createdAt'] ?? null,
            "readAt" => $row['readAt'] ?? null,
            "isRead" => (bool) ($row['isRead'] ?? false),
            "extras" => !empty($row['extras']) ? json_decode($row['extras'], true) : null,
        ];
    }
}
