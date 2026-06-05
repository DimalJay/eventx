<?php

namespace Controllers;

use Services\EventService;
use Models\Event;

class EventController
{
    private EventService $eventService;
    public function __construct()
    {
        $this->eventService = new EventService();
    }

    public function listEvents()
    {
        $events = $this->eventService->get_all_events();
        return [
            "success" => true,
            "message" => "List of events",
            "data" => $events
        ];
    }

    public function getEventDetails()
    {
        $id = $_SERVER["uid"];
        $event = $this->eventService->get_event($id);


        return [
            "success" => true,
            "message" => "Event details for ID: " . $id,
            "data" => $event,
        ];
    }

    public function createEvent()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $title = $data['title'] ?? '';
        $description = $data['description'] ?? '';
        $location = $data['location'] ?? '';
        $startDate = $data['startDate'] ?? '';
        $endDate = $data['endDate'] ?? '';
        $agenda = $data['agenda'] ?? '';
        $capacity = $data['capacity'] ?? 0;
        $eventCategory = $data['eventCategory'] ?? '';
        $registrationDeadline = $data['registrationDeadline'] ?? '';
        $ticketPrice = $data['ticketPrice'] ?? 0.0;
        $isPaid = $data['isPaid'] ?? false;
        $isPublic = $data['isPublic'] ?? 'public';
        $waitlistEnabled = $data['waitlistEnabled'] ?? false;
        $imageUrl = $data['imageUrl'] ?? '';
        $organizerId = $data['organizerId'] ?? 1;

        // Basic validation
        if (empty($title) || empty($startDate) || empty($endDate) || empty($capacity) || empty($eventCategory) || empty($ticketPrice) || empty($isPublic)) {
            return [
                "success" => false,
                "message" => "All fields are required",
                "data" => null,
            ];
        }

        $event = new Event(
            $title, 
            $eventCategory, 
            $description, 
            $startDate, 
            $endDate, 
            $location, 
            $organizerId, 
            $imageUrl, 
            $isPublic, 
            $capacity, 
            $ticketPrice, 
            $registrationDeadline, 
            $agenda, 
            $waitlistEnabled, 
            $isPaid
        );

        $response = $this->eventService->create_event($event);
        return [
            "success" => true,
            "message" => "Event created successfully",
            "data" => $response,
        ];
    }
}
