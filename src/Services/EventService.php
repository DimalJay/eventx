<?php

namespace Services;

use Models\Event;

class EventService
{
    public function __construct() {}

    public function getEvents()
    {
        return Event::selectAll();
    }

    public function getEventForUserId(String $userId){
        return Event::where(["organizerId" => $userId]);
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
            return $event->delete();
        }
        return false;
    }

    public function updateEvent(String $id, array $eventData)
    {
        return Event::updateRecord(["id" => $id], $eventData);
    }

    public function getPublicEvents()
    {
        return Event::where(["isPublic" => true]);
    }
}
