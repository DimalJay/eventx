<?php
namespace Services;
use Models\Event;

class EventService
{
    public function __construct()
    {
    }

    public function get_all_events()
    {
        return Event::selectAll();
    }

    public function get_event(String $id)
    {
        $events = Event::where(["id" => $id]);
        return count($events) > 0 ? $events[0] : null;
    }

    public function create_event(Event $event)
    {
        return $event->save();
    }

    public function delete_event(String $id)
    {
        $event = $this->get_event($id);
        if($event) {
            return $event->delete();
        }
        return false;
    }

    public function update_event(String $id, Event $event)
    {
        return Event::updateRecord(["id" => $id], $event->toArray());
    }
}