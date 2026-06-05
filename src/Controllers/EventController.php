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

    public function updatEvent()
    {
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        $id = trim($data["id"]) ?? "";
        $title = trim($data["title"]) ?? "";
        $description = trim($data["description"]) ?? "";
        $location = trim($data["location"]) ?? "";
        $startDate = trim($data["startDate"]) ?? "";
        $endDate = trim($data["endDate"]) ?? "";
        $agenda = trim($data["agenda"]) ?? "";
        $capacity = trim($data["capacity"]) ?? "";
        $eventCategory = trim($data["eventCategory"]) ?? "";
        $registrationDeadline = trim($data["registrationDeadline"]) ?? "";
        $ticketPrice = trim($data["ticketPrice"]) ?? "";
        $isPaid = trim($data["isPaid"]) ?? "";
        $isPublic = trim($data["isPublic"]) ?? "";
        $waitlistEnabled = trim($data["waitlistEnabled"]) ?? "";
        $imageUrl = trim($data["imageUrl"]) ?? "";

        if(empty($id)) {
            return [
                "success" => false,
                "message" => "Event ID is required"
            ];
        }

        $eventData = [];
        if (!empty($title)) {
            $eventData["title"] = $title;
        }
        if (!empty($startDate)) {
            $eventData["startDate"] = $startDate;
        }
        if (!empty($endDate)) {
            $eventData["endDate"] = $endDate;
        }
        if (!empty($capacity)) {
            $eventData["capacity"] = $capacity;
        }
        if (!empty($eventCategory)) {
            $eventData["eventCategory"] = $eventCategory;
        }
        if (!empty($ticketPrice)) {
            $eventData["ticketPrice"] = $ticketPrice;
        }
        if (!empty($isPublic)) {
            $eventData["isPublic"] = $isPublic;
        }
        if (!empty($isPaid)) {
            $eventData["isPaid"] = $isPaid;
        }
        if (!empty($registrationDeadline)){
            $eventData["registrationDeadline"] = $registrationDeadline;
        }
        if (!empty($waitlistEnabled)){
            $eventData["waitlistEnabled"] = $waitlistEnabled;
        }
        if (!empty($description)) {
            $eventData["description"] = $description;
        }
        if (!empty($location)) {
            $eventData["location"] = $location;
        }
        if (!empty($agenda)) {
            $eventData["agenda"] = $agenda;
        }
        If (!empty($imageUrl)) {
            $eventData["imageUrl"] = $imageUrl;
        }

        try {
            $this->eventService->update_event($id, $eventData);
        } catch (\Throwable $th) {
            return [
                "success" => false,
                "message" => "Error updating event: " . $th->getMessage()
            ];
        }

        return [
            "success" => true,
            "message" => "Event updated successfully",
            "data" => null
        ];
    }
}
