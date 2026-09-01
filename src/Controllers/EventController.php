<?php

namespace Controllers;

use Services\EventService;
use Services\TeamAccessService;
use Models\Event;

class EventController
{
    private EventService $eventService;
    private TeamAccessService $teamAccessService;
    public function __construct()
    {
        $this->eventService = new EventService();
        $this->teamAccessService = new TeamAccessService();
    }

    public function canManageEvent(array $routeParams = [])
    {
        $userId = $_SERVER["uid"];
        $eventId = trim($routeParams["id"] ?? "");

        if (empty($eventId)) {
            http_response_code(400);
            return [
                "success" => false,
                "message" => "Event ID is required",
                "data" => null,
            ];
        }

        try {
            $canManage = $this->teamAccessService->hasTeamAccess((int) $userId, (int) $eventId);
            return [
                "success" => true,
                "message" => $canManage ? "Access granted" : "Access denied",
                "data" => ["canManage" => $canManage],
            ];
        } catch (\Throwable $th) {
            http_response_code(404);
            return [
                "success" => false,
                "message" => "Event not found",
                "data" => null,
            ];
        }
    }

    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    private const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    private function storeCoverImage(array $file): ?string
    {
        if (!isset($file['tmp_name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            throw new \Exception("Unsupported file type.");
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            throw new \Exception("Unsupported file extension.");
        }

        $uploadDir = realpath(__DIR__ . '/../../uploads/event-covers/') ?: (__DIR__ . '/../../uploads/event-covers/');
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0775, true);
        }

        $fileName = "cover_" . bin2hex(random_bytes(8)) . ".{$ext}";
        $targetFilePath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $targetFilePath)) {
            throw new \Exception("Failed to store file.");
        }
        return '/uploads/event-covers/' . $fileName;
    }

    private function assertManageAccess(int $eventId): bool
    {
        $userId = (int) ($_SERVER["uid"] ?? 0);
        return $this->teamAccessService->hasTeamAccess($userId, $eventId);
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
                $data['coverImage'] = $this->storeCoverImage($_FILES['coverImage']);
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

        if (!$this->assertManageAccess((int) $id)) {
            http_response_code(403);
            return [
                "success" => false,
                "message" => "Unauthorized: You do not have access to this event",
                "data" => null,
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

    public function uploadEventCover()
    {
        try {
            if (!isset($_FILES['cover']) || $_FILES['cover']['error'] !== UPLOAD_ERR_OK) {
                return [
                    "success" => false,
                    "message" => "No cover image uploaded",
                    "data" => null,
                ];
            }

            $coverPath = $this->storeCoverImage($_FILES['cover']);

            return [
                "success" => true,
                "message" => "Cover image uploaded successfully",
                "data" => ["coverImage" => $coverPath],
            ];
        } catch (\Throwable $th) {
            http_response_code(400);
            return [
                "success" => false,
                "message" => "Error uploading cover image: " . $th->getMessage(),
                "data" => null,
            ];
        }
    }

    public function updateEventStatus()
    {
        try {
            $jsonData = file_get_contents('php://input');
            $data = json_decode($jsonData, true);

            $eventId = trim($data["id"] ?? "");
            $status = trim($data["status"] ?? "");

            if (empty($eventId) || empty($status)) {
                return [
                    "success" => false,
                    "message" => "Event ID and status are required",
                    "data" => null,
                ];
            }

            if (!$this->assertManageAccess((int) $eventId)) {
                http_response_code(403);
                return [
                    "success" => false,
                    "message" => "Unauthorized: You do not have access to this event",
                    "data" => null,
                ];
            }

            $allowedStatuses = ['upcoming', 'ongoing', 'completed', 'cancelled', 'draft', 'published'];
            if (!in_array(strtolower($status), $allowedStatuses, true)) {
                return [
                    "success" => false,
                    "message" => "Invalid status value",
                    "data" => null,
                ];
            }

            $this->eventService->updateEventStatus($eventId, strtolower($status));

            return [
                "success" => true,
                "message" => "Event status updated successfully",
                "data" => null
            ];
        } catch (\Throwable $th) {
            return [
                "success" => false,
                "message" => "Error updating event status: " . $th->getMessage(),
                "data" => null,
            ];
        }
    }

    public function deleteEvent(array $routeParams = [])
    {
        try {
            $eventId = trim($routeParams["id"] ?? "");

            if (empty($eventId)) {
                return [
                    "success" => false,
                    "message" => "Event ID is required",
                    "data" => null,
                ];
            }

            if (!$this->assertManageAccess((int) $eventId)) {
                http_response_code(403);
                return [
                    "success" => false,
                    "message" => "Unauthorized: You do not have access to this event",
                    "data" => null,
                ];
            }

            $deleted = $this->eventService->deleteEvent($eventId);
            if (!$deleted) {
                return [
                    "success" => false,
                    "message" => "Event not found",
                    "data" => null,
                ];
            }

            return [
                "success" => true,
                "message" => "Event deleted successfully",
                "data" => null
            ];
        } catch (\Throwable $th) {
            return [
                "success" => false,
                "message" => "Error deleting event: " . $th->getMessage(),
                "data" => null,
            ];
        }
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

        if (!$this->assertManageAccess((int) $eventId)) {
            http_response_code(403);
            return [
                "success" => false,
                "message" => "Unauthorized: You do not have access to this event"
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
