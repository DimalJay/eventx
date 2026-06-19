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

    public function getEvent(String $id)
    {
        $events = Event::where(["id" => $id]);
        return count($events) > 0 ? $events[0] : null;
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

    public function updateEvent(String $id, Event $event)
    {
        return Event::updateRecord(["id" => $id], $event->toArray());
    }

    public function getPublicEvents()
    {
        return Event::where(["isPublic" => 1]);
    }
}
