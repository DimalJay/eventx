<?php

namespace Controllers;

use Services\RegistrationService;
use Services\UserService;
use Services\EventService;
use Services\TeamAccessService;
use Services\NotificationService;
use Models\Registration;
use Models\User;
use Helpers\EmailHelper;
use Helpers\QrHelper;

class RegistrationController
{
    /**
     * Accept both a bare ticket code (6aafb…) and a ticket URL/QR payload
     * (…/ticket/6aafb…). URLs yield their last path segment, URI-decoded.
     */
    private static function extractTicketCode(?string $raw): string
    {
        $value = trim((string) $raw);
        if ($value === "") {
            return "";
        }
        $value = preg_split('/[?#]/', $value)[0];
        if (strpos($value, "/") !== false) {
            $segments = explode("/", rtrim($value, "/"));
            $last = rawurldecode(end($segments));
            if ($last !== "") {
                return $last;
            }
        }
        return $value;
    }

    private RegistrationService $registrationService;
    private EventService $eventService;
    private UserService $userService;
    private TeamAccessService $teamAccessService;
    private NotificationService $notificationService;
    public function __construct()
    {
        $this->registrationService = new RegistrationService();
        $this->userService = new UserService();
        $this->eventService = new EventService();
        $this->teamAccessService = new TeamAccessService();
        $this->notificationService = new NotificationService();
    }

    public function joinEvent()
    {
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        $email = $data["email"] ?? "";
        $eventId = $data["eventId"] ?? "";
        $firstName = $data["firstName"] ?? "";
        $lastName = $data["lastName"] ?? "";
        $customFields = $data["customFields"] ?? null;

        if (empty($email) || empty($eventId) || empty($firstName) || empty($lastName)) {
            return [
                "success" => false,
                "message" => "Missing required fields"
            ];
        }

        try {

            $userId = null;
            $user = $this->userService->getUserByEmail($email);
            if (!$user) {
                $user = new User($email, $firstName, $lastName, "", null, "temp");
                $userId = $this->userService->createUser($user);
            } else {
                $userId = $user["id"];
                if (isset($user['accountStatus']) && strtolower($user['accountStatus']) === 'suspended') {
                    http_response_code(403);
                    return [
                        "success" => false,
                        "message" => "Your account has been suspended by the administrator. Registration is not allowed."
                    ];
                }
            }

            // the event organizer cannot register to their own event
            $event = $this->eventService->getEvent($eventId);
            if (!$event) {
                http_response_code(404);
                return [
                    "success" => false,
                    "message" => "Event not found"
                ];
            }

            // A paid event requires purchasing a ticket; free join is blocked
            if ((float)($event["ticketPrice"] ?? 0) > 0) {
                http_response_code(400);
                return [
                    "success" => false,
                    "message" => "This is a paid event. Please purchase a ticket to register."
                ];
            }

            // The event organizer cannot register to their own event
            $authUid = isset($_SERVER['uid']) ? (int)$_SERVER['uid'] : null;
            if ((int)$event["organizerId"] === (int)$userId || ($authUid && (int)$event["organizerId"] === $authUid)) {
                http_response_code(400);
                return [
                    "success" => false,
                    "message" => "Organizer cannot register to their own event"
                ];
            }

            // check if event is suspended
            if ($event && isset($event['status']) && strtolower($event['status']) === 'suspended') {
                http_response_code(403);
                return [
                    "success" => false,
                    "message" => "This event has been suspended by the administrator. Registration is not allowed."
                ];
            }

            // check if the user is already registered for the event
            $existingRegistration = $this->registrationService->isUserRegisteredForEvent($userId, $eventId);
            if ($existingRegistration) {
                http_response_code(400);
                return [
                    "success" => false,
                    "message" => "User is already registered for this event"
                ];
            }

            // Check capacity and waitlist
            $capacity = (int)($event['capacity'] ?? 0);
            $waitlistEnabled = filter_var($event['waitlistEnabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
            if ($capacity > 0) {
                $regCount = $this->registrationService->getActiveRegistrationCount((int)$eventId);
                if ($regCount >= $capacity && !$waitlistEnabled) {
                    http_response_code(400);
                    return [
                        "success" => false,
                        "message" => "Event is full. Registration is closed."
                    ];
                }
            }

            $registration = new Registration($eventId, $userId, $customFields);
            $reg_id = $this->registrationService->registerUserForEvent($registration);
            $this->registrationService->createTicketForRegistration((int) $reg_id, (int) $eventId, (int) $userId);
            $registration = $this->registrationService->getRegistrationById($reg_id);

            if ($event) {
                $startTs = strtotime($event["startDate"]);
                $endTs = strtotime($event["endDate"]);
                $domain = $_ENV['DOMAIN'] ?? getenv('DOMAIN') ?? 'localhost';
                $ticketLink = EmailHelper::frontendUrl() . '/ticket/' . rawurlencode($registration["ticketCode"]);

                EmailHelper::sendWithTemplate($email, "Your Ticket for " . $event["title"], "ticket", [
                    "firstName" => $firstName,
                    "lastName" => $lastName,
                    "eventTitle" => $event["title"],
                    "ticketCode" => $registration["ticketCode"],
                    "eventDate" => $startTs ? date("D, M j, Y", $startTs) : $event["startDate"],
                    "eventTime" => $startTs && $endTs
                        ? date("g:i A", $startTs) . " – " . date("g:i A", $endTs)
                        : "",
                    "eventLocation" => $event["location"] ?? "TBD",
                    "eventType" => $event["eventType"] ?? "General admission",
                    "ticketPrice" => number_format((float)($event["ticketPrice"] ?? 0), 2),
                    "status" => $registration["status"] === "WAITLIST" ? "Waitlisted" : "Valid",
                    "eventLink" => "http://" . $domain . "/event/" . $event["id"],
                    "ticketLink" => $ticketLink,
                    "raw_qrCode" => QrHelper::renderTable($ticketLink),
                ]);

                $attendeeName = trim($firstName . " " . $lastName);
                $this->notificationService->notifyNewRegistration($event, $attendeeName);
            }

            return [
                "success" => true,
                "message" => "User registered for the event successfully",
                "data" => $registration
            ];
        } catch (\Throwable $th) {
            http_response_code(400);
            return [
                "success" => false,
                "message" => $th->getMessage()
            ];
        }
    }

    public function listRegistrations()
    {
        $userId = $_SERVER["uid"];
        $eventId = $_GET["eventId"] ?? "";
        if(empty($eventId)) {
            return [
                "success" => false,
                "message" => "Event ID is required"
            ];
        }
        $event = $this->eventService->getEvent($eventId);
        if (!$event) {
            return [
                "success" => false,
                "message" => "Event not found"
            ];
        }
        if (!$this->teamAccessService->hasTeamAccess($userId, (int) $eventId)) {
            http_response_code(403);
            return [
                "success" => false,
                "message" => "Unauthorized: You do not have access to this event"
            ];
        }
        $registrations = $this->registrationService->getRegistrationsList($eventId);
        return [
            "success" => true,
            "message" => "List of registrations for event ID: " . $eventId,
            "data" => $registrations
        ];
    }

    public function updateRegistrationStatus()
    {
        $userId = $_SERVER["uid"];
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        $registrationId = $data["id"] ?? "";
        $status = $data["status"] ?? "";

        if (empty($registrationId) || empty($status)) {
            return [
                "success" => false,
                "message" => "Missing required fields"
            ];
        }

        $registration = $this->registrationService->getRegistrationById($registrationId);
        if (!$registration) {
            return [
                "success" => false,
                "message" => "Registration not found"
            ];
        }

        $eventId = $registration["eventId"];
        $hasAccess = $this->teamAccessService->hasTeamAccess($userId, $eventId);
        if (!$hasAccess) {
            return [
                "success" => false,
                "message" => "Unauthorized: User does not have access to update registration status for this event"
            ];
        }

        try {
            $this->registrationService->updateRegistrationStatus($registrationId, $status);

            if ($status === 'GOING') {
                $user = $this->userService->getUser($registration['userId']);
                $event = $this->eventService->getEvent($registration['eventId']);
                if ($user && $event) {
                    $domain = $_ENV['DOMAIN'] ?? getenv('DOMAIN') ?? 'localhost';
                    $checkinTime = (new \DateTime('now', new \DateTimeZone('Asia/Colombo')))->format('g:i A');
                    $checkinDate = (new \DateTime('now', new \DateTimeZone('Asia/Colombo')))->format('D, M j, Y');
                    EmailHelper::sendWithTemplate($user['email'], "Attendance Confirmed: " . $event["title"], "attendance", [
                        "firstName" => $user["firstName"],
                        "lastName" => $user["lastName"],
                        "eventTitle" => $event["title"],
                        "checkinTime" => $checkinTime,
                        "checkinDate" => $checkinDate,
                        "eventLocation" => $event["location"] ?? "TBD",
                        "eventLink" => "http://" . $domain . "/event/" . $event["id"],
                    ]);
                }
            }

            return [
                "success" => true,
                "message" => "Registration status updated successfully",
                "data" => null
            ];
        } catch (\Throwable $th) {
            return [
                "success" => false,
                "message" => "Error updating registration status: " . $th->getMessage(),
                "data" => null
            ];
        }
    }

    public function getTicketDetails()
    {
        $code = self::extractTicketCode($_GET["code"] ?? "");
        if (empty($code)) {
            http_response_code(400);
            return [
                "success" => false,
                "message" => "Ticket code is required",
                "data" => null
            ];
        }

        $registration = $this->registrationService->getRegistrationByTicketCode($code);
        if (!$registration) {
            http_response_code(404);
            return [
                "success" => false,
                "message" => "Invalid ticket code",
                "data" => null
            ];
        }

        $event = $this->eventService->getEvent($registration["eventId"]);
        if (!$event) {
            http_response_code(404);
            return [
                "success" => false,
                "message" => "Event not found for this ticket",
                "data" => null
            ];
        }

        $holder = null;
        $user = $this->userService->getUser($registration["userId"]);
        if ($user) {
            $holder = [
                "firstName" => $user["firstName"],
                "lastName" => $user["lastName"],
                "email" => $user["email"]
            ];
        }

        $organizer = null;
        if (!empty($event["organizerId"])) {
            $org = $this->userService->getUser($event["organizerId"]);
            if ($org) {
                $organizer = trim(($org["firstName"] ?? "") . " " . ($org["lastName"] ?? ""));
            }
        }

        return [
            "success" => true,
            "message" => "Ticket details retrieved successfully",
            "data" => [
                "ticketCode" => $registration["ticketCode"],
                "status" => $registration["status"],
                "eventId" => $registration["eventId"],
                "event" => $event,
                "organizer" => $organizer,
                "holder" => $holder
            ]
        ];
    }

    public function scanTicket()
    {
        $userId = $_SERVER["uid"];
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        $ticketCode = self::extractTicketCode($data["ticketCode"] ?? "");

        if (empty($ticketCode)) {
            return [
                "success" => false,
                "message" => "Missing required fields"
            ];
        }

        $registration = $this->registrationService->getRegistrationByTicketCode($ticketCode);
        if (!$registration) {
            return [
                "success" => false,
                "message" => "Invalid ticket code"
            ];
        }

        $eventId = $registration["eventId"];
        $hasAccess = $this->teamAccessService->hasTeamAccess($userId, $eventId);
        if (!$hasAccess) {
            return [
                "success" => false,
                "message" => "Unauthorized: User does not have access to scan tickets for this event"
            ];
        }

        try {
            return [
                "success" => true,
                "message" => "Ticket Details retrieved successfully",
                "data" => $registration
            ];
        } catch (\Throwable $th) {
            return [
                "success" => false,
                "message" => "Error scanning ticket: " . $th->getMessage(),
                "data" => null
            ];
        }
    }

}