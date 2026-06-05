<?php
namespace Services;
use Models\Event;

class EventService
{
    public function __construct()
    {
    }

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
        if (empty($id)) {
           throw new \Exception("Event ID is required");
        }
        $event = $this->getEvent($id);
        if(!$event) {
            throw new \Exception("Event not found");
        }
        return Event::deleteRecord(["id" => $id]);
    }

    public function updateEvent(String $id, array $eventData)
    {
        return Event::updateRecord(["id" => $id], $eventData);
    }
}