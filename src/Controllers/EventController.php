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
        try {
            $id = $_SERVER["uid"];
            $data = $_POST;
            
            if(isset($_FILES['coverImage']) && $_FILES['coverImage']['error'] === UPLOAD_ERR_OK) {
                echo "File uploaded successfully.";
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
                echo "No image uploaded or there was an upload error.";
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
                $data["isPublic"] ?? false,
                $data["capacity"] ?? 0,
                $data["ticketPrice"] ?? 0.0,
                $data["regDeadline"] ?? null,
                $data["agenda"] ?? null,
                $data["waitlistEnabled"] ?? false,
            );

            $this->eventService->createEvent($event);
            return [
                "success" => true,
                "message" => "Event created successfully",
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

        if (empty($id)) {
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
        if (!empty($registrationDeadline)) {
            $eventData["registrationDeadline"] = $registrationDeadline;
        }
        if (!empty($waitlistEnabled)) {
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
