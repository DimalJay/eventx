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
        $userId = $_SERVER["uid"];
        $events = $this->eventService->getEventForUserId($userId);
        return [
            "success" => true,
            "message" => "Events Retrieved Successfully",
            "data" => $events
        ];
    }
    public function getPublicEvents()
    {
        $events = $this->eventService->getPublicEvents();

        return [
            "success" => true,
            "message" => "Public events retrieved successfully",
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
        try {
            $id = $_SERVER["uid"];
            $data = $_POST;

            if (isset($_FILES['coverImage']) && $_FILES['coverImage']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../uploads/event-covers/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $fileName = basename($_FILES['coverImage']['name']);
                $fileName = "cover_" . time() . "_" . $fileName;

                $targetFilePath = $uploadDir . $fileName;
                move_uploaded_file($_FILES['coverImage']['tmp_name'], $targetFilePath);
                $data['coverImage'] = '/uploads/event-covers/' . $fileName; // Store relative path
            } else {
                $data['coverImage'] = null; // No image uploaded
            }

            if (empty($data["title"]) || empty($data["startDate"]) || empty($data["endDate"])) {
                return [
                    "success" => false,
                    "message" => "All fields are required",
                    "data" => null,
                ];
            }
            $event = new Event(
                trim($data["title"]),
                trim($data["eventType"]),
                trim($data["description"]),
                $data["startDate"],
                $data["endDate"],
                trim($data["location"]),
                $id,
                $data["coverImage"] ?? null,
                isset($data["isPublic"]) ? filter_var($data["isPublic"], FILTER_VALIDATE_BOOLEAN) : false,
                $data["capacity"] ?? 0,
                $data["ticketPrice"] ?? 0.0,
                $data["regDeadline"] ?? null,
                $data["agenda"] ?? null,
                $data["waitlistEnabled"] ?? false,
            );

            $lastId = $this->eventService->createEvent($event);
            $res = $this->eventService->getEvent($lastId);
            return [
                "success" => true,
                "message" => "Event created successfully",
                "data" => $res
            ];
        } catch (\Throwable $th) {
            return [
                "success" => false,
                "message" => "Error creating event: " . $th->getMessage(),
                "data" => null,
            ];
        }
    }

    public function updateEvent()
    {
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        $id = trim($data["id"]) ?? "";

        $title = $data["title"] ?? "";
        $description = $data["description"] ?? "";
        $location = $data["location"] ?? "";
        $startDate = $data["startDate"] ?? "";
        $endDate = $data["endDate"] ?? "";
        $agenda = $data["agenda"] ?? "";
        $capacity = $data["capacity"] ?? "";
        $regDeadline = $data["regDeadline"] ?? "";
        $ticketPrice = $data["ticketPrice"] ?? "";
        $isPublic = $data["isPublic"] ?? false;
        $waitlistEnabled = $data["waitlistEnabled"] ?? false;
        $coverImage = $data["coverImage"] ?? "";

        if (empty($id)) {
            return [
                "success" => false,
                "message" => "Event ID is required"
            ];
        }

        $eventData = [];
        if (!empty($title)) {
            $eventData["title"] = trim($title);
        }
        if (!empty($startDate)) {
            $eventData["startDate"] = trim($startDate);
        }
        if (!empty($endDate)) {
            $eventData["endDate"] = trim($endDate);
        }
        if (!empty($capacity)) {
            $eventData["capacity"] = trim($capacity);
        }
        if (!empty($ticketPrice)) {
            $eventData["ticketPrice"] = trim($ticketPrice);
        }
        if (isset($data["isPublic"])) {
            $eventData["isPublic"] = filter_var($data["isPublic"], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
        }
        if (!empty($regDeadline)) {
            $eventData["regDeadline"] = trim($regDeadline);
        }
        if (!empty($waitlistEnabled)) {
            $eventData["waitlistEnabled"] = trim($waitlistEnabled);
        }
        if (!empty($description)) {
            $eventData["description"] = trim($description);
        }
        if (!empty($location)) {
            $eventData["location"] = trim($location);
        }
        if (!empty($agenda)) {
            $eventData["agenda"] = trim($agenda);
        }
        if (!empty($coverImage)) {
            $eventData["coverImage"] = trim($coverImage);
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

    public function getEventRegistrations()
    {
        $eventId = $_GET["eventId"] ?? "";
        if (empty($eventId)) {
            return [
                "success" => false,
                "message" => "Event ID is required"
            ];
        }

        $q = "SELECT r.*, u.firstName, u.lastName, u.email, u.profilePicture 
              FROM Registrations r 
              JOIN users u ON r.userId = u.id 
              WHERE r.eventId = ?";
        
        $attendees = Event::query($q, [$eventId]);

        return [
            "success" => true,
            "message" => "List of attendees for event ID: " . $eventId,
            "data" => $attendees
        ];
    }
}
