<?php

namespace Services;

require_once dirname(__DIR__, 2) . '/database/Database.php';

use Models\Event;
use Models\TeamAccess;

class EventService
{
    public function __construct() {}

    public function getEvents()
    {
        return Event::selectAll();
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
            return $organized;
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
        return $result;
    }

    public function getEvent(String $id)
    {
        $events = Event::where(["id" => $id]);
        return count($events) > 0 ? $events[0] : null;
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
        return Event::where(["isPublic" => true]);
    }
}
