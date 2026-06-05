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
        $events = $this->eventService->getEvents();
        return [
            "success" => true,
            "message" => "Events Retrieved Successfully",
            "data" => $events
        ];
    }

    public function getEventDetails()
    {
        $id = $_GET["id"];
        $event = $this->eventService->getEvent($id);

        return [
            "success" => true,
            "message" => "Event details retrieved successfully",
            "data" => $event,
        ];
    }

    public function createEvent()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $title = trim($data["title"]) ?? "";
        $description = trim($data['description']) ?? '';
        $location = trim($data['location']) ?? '';
        $startDate = trim($data['startDate']) ?? '';
        $endDate = trim($data['endDate']) ?? '';
        $agenda = trim($data['agenda']) ?? '';
        $capacity = $data['capacity'] ?? 0;
        $eventCategory = trim($data['eventCategory']) ?? '';
        $registrationDeadline = trim($data['registrationDeadline']) ?? '';
        $ticketPrice = $data['ticketPrice'] ?? 0.0;
        $isPaid = $data['isPaid'] ?? false;
        $isPublic = $data['isPublic'] ?? false;
        $waitlistEnabled = $data['waitlistEnabled'] ?? false;
        $imageUrl = trim($data['imageUrl']) ?? '';
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

        $response = $this->eventService->createEvent($event);
        return [
            "success" => true,
            "message" => "Event created successfully",
            "data" => $response,
        ];
    }

    public function deleteEvent()
    {
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        $id = trim($data["id"]) ?? "";
        if (empty($id)) {
            return [
                "success" => false,
                "message" => "Event ID is required"
            ];
        }
        try {
            $ret = $this->eventService->deleteEvent($id);
            if ($ret > 0) {
                return [
                    "success" => true,
                    "message" => "Event deleted successfully",
                    "data" => null
                ];
            } else {
                return [
                    "success" => false,
                    "message" => "Event not found",
                    "data" => null
                ];
            }
        } catch (\Throwable $th) {
            return [
                "success" => false,
                "message" => "Error deleting event: " . $th->getMessage()
            ];
        }
    }

    public function updateEvent()
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
        $isPaid = $data["isPaid"] ?? false;
        $isPublic = $data["isPublic"] ?? false;
        $waitlistEnabled = $data["waitlistEnabled"] ?? false;
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
        if (!empty($imageUrl)) {
            $eventData["imageUrl"] = $imageUrl;
        }

        try {
            $this->eventService->updateEvent($id, $eventData);
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
