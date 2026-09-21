<?php

namespace Services;

require_once dirname(__DIR__, 2) . '/database/Database.php';

use Models\Event;
use Models\TeamAccess;

class EventService
{
    public function __construct() {}

    private function formatEvent(array $event): array
    {
        if (!empty($event['customFields']) && is_string($event['customFields'])) {
            $decoded = json_decode($event['customFields'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $event['customFields'] = $decoded;
            }
        }
        return $event;
    }

    public function getEvents()
    {
        return array_map([$this, 'formatEvent'], Event::selectAll());
    }

    public function getEventForUserId(String $userId){
        $organized = Event::where(["organizerId" => $userId]);

        // Events where the user is a team member (any role)
        $memberships = TeamAccess::where(["userId" => $userId]);
        $teamEventIds = array_values(array_unique(array_map(
            fn($m) => (int) $m["eventId"],
            $memberships
        )));

        if (count($teamEventIds) === 0) {
            return array_map([$this, 'formatEvent'], $organized);
        }

        // Pull full event rows for those ids via direct query
        $in = implode(",", $teamEventIds);
        $teamEvents = Event::query(
            "SELECT * FROM `events` WHERE `id` IN ($in)"
        );

        // Merge and dedupe by id (organized first so organizer wins positional ordering)
        $result = $organized;
        $seen = [];
        foreach ($organized as $ev) {
            $seen[(int) $ev["id"]] = true;
        }
        foreach ($teamEvents as $ev) {
            $eid = (int) $ev["id"];
            if (!isset($seen[$eid])) {
                $seen[$eid] = true;
                $result[] = $ev;
            }
        }
        return array_map([$this, 'formatEvent'], $result);
    }

    /**
     * Events the user registered for (joined) — excludes events they created.
     * Each item is the event row enriched with registration context:
     *   registeredAt, registrationStatus, ticketCode.
     */
    public function getRegisteredEventsForUser(String $userId)
    {
        $rows = Event::query(
            "SELECT e.*, r.registeredAt, r.status AS registrationStatus, t.ticketCode AS ticketCode
             FROM Registrations r
             JOIN events e ON e.id = r.eventId
             LEFT JOIN tickets t ON t.registerId = r.id
             WHERE r.userId = ?
               AND e.organizerId <> ?
             ORDER BY r.registeredAt DESC",
            [(int) $userId, (int) $userId]
        );

        return array_map([$this, 'formatEvent'], $rows);
    }

    public function getEvent(String $id)
    {
        $events = Event::where(["id" => $id]);
        return count($events) > 0 ? $this->formatEvent($events[0]) : null;
    }

    public function getEventWithUserId(String $userId, String $eventId)
    {
        return Event::where(["id" => $eventId, "organizerId" => $userId]);
    }

    public function createEvent(Event $event)
    {
        return $event->save();
    }

    public function deleteEvent(String $id)
    {
        $event = $this->getEvent($id);
        if ($event) {
            return Event::deleteRecord(["id" => $id]);
        }
        return false;
    }

    public function updateEvent(String $id, array $eventData)
    {
        return Event::updateRecord(["id" => $id], $eventData);
    }

    public function updateEventStatus(String $id, String $status)
    {
        return Event::updateRecord(["id" => $id], ["status" => $status]);
    }

    public function getPublicEvents()
    {
        return array_map([$this, 'formatEvent'], Event::where(["isPublic" => true]));
    }
}
