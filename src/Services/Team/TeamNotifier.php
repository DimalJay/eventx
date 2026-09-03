<?php

namespace Services\Team;

use Contracts\TeamNotifierInterface;
use Services\NotificationService;
use Helpers\EmailHelper;
use Models\User;
use Models\Event;

class TeamNotifier extends NotificationService implements TeamNotifierInterface
{
    public function notifyMemberAdded(int $memberId, int $eventId, string $role): void
    {
        $context = $this->userAndEvent($memberId, $eventId);
        if (!$context) {
            return;
        }
        $eventTitle = $context["event"]["title"];
        $firstName = $context["user"]["firstName"];
        $email = $context["user"]["email"];
        $organizerId = (int) $context["event"]["organizerId"];
        $roleLabel = ucfirst(strtolower($role));

        $this->notifyTeamMemberAdded($memberId, $eventTitle, $eventId);

        EmailHelper::sendWithTemplate(
            $email,
            "You've been added to {$eventTitle}'s team",
            "team_access",
            [
                "firstName" => $firstName,
                "eventTitle" => $eventTitle,
                "roleLabel" => $roleLabel,
                "eventLink" => EmailHelper::frontendUrl() . "/events/{$eventId}",
            ]
        );

        if ($organizerId !== $memberId) {
            $this->notifyOrganizer(
                $organizerId,
                $eventTitle,
                $eventId,
                "New team member",
                "{$firstName} ({$email}) has been added to the team as {$roleLabel}."
            );
        }
    }

    public function notifyMemberRemoved(int $memberId, int $eventId): void
    {
        $context = $this->userAndEvent($memberId, $eventId);
        if (!$context) {
            return;
        }
        $eventTitle = $context["event"]["title"];
        $firstName = $context["user"]["firstName"];
        $email = $context["user"]["email"];
        $organizerId = (int) $context["event"]["organizerId"];
        $currentUser = (int) ($_SERVER["uid"] ?? 0);

        $this->notifyTeamMemberRemoved($memberId, $eventTitle, $eventId);

        EmailHelper::sendWithTemplate(
            $email,
            "You've been removed from {$eventTitle}'s team",
            "team_removed",
            ["firstName" => $firstName, "eventTitle" => $eventTitle]
        );

        if ($organizerId !== $memberId && $organizerId !== $currentUser) {
            $this->notifyOrganizer(
                $organizerId,
                $eventTitle,
                $eventId,
                "Team member removed",
                "{$firstName} ({$email}) has been removed from the team."
            );
        }
    }

    public function notifyMemberRoleChanged(int $memberId, int $eventId, string $oldRole, string $newRole): void
    {
        $context = $this->userAndEvent($memberId, $eventId);
        if (!$context) {
            return;
        }
        $eventTitle = $context["event"]["title"];
        $firstName = $context["user"]["firstName"];
        $email = $context["user"]["email"];
        $organizerId = (int) $context["event"]["organizerId"];
        $roleLabel = ucfirst(strtolower($newRole));

        $this->notifyTeamMemberRoleChanged($memberId, $eventTitle, $eventId, $newRole);

        EmailHelper::sendWithTemplate(
            $email,
            "Your role on {$eventTitle} has been updated",
            "team_role_changed",
            [
                "firstName" => $firstName,
                "eventTitle" => $eventTitle,
                "roleLabel" => $roleLabel,
                "eventLink" => EmailHelper::frontendUrl() . "/events/{$eventId}",
            ]
        );

        if ($organizerId !== $memberId) {
            $this->notifyOrganizer(
                $organizerId,
                $eventTitle,
                $eventId,
                "Team role changed",
                "{$firstName}'s role has been changed from " . ucfirst(strtolower($oldRole)) . " to {$roleLabel}."
            );
        }
    }

    private function userAndEvent(int $userId, int $eventId): ?array
    {
        $users = User::where(["id" => $userId]);
        $events = Event::where(["id" => $eventId]);
        if (count($users) === 0 || count($events) === 0) {
            return null;
        }
        return ["user" => $users[0], "event" => $events[0]];
    }
}
