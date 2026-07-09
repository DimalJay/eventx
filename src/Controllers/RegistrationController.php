<?php

namespace Controllers;

use Services\RegistrationService;
use Services\UserService;
use Services\EventService;
use Services\TeamAccessService;
use Models\Registration;
use Models\User;

class RegistrationController
{
    private RegistrationService $registrationService;
    private EventService $eventService;
    private UserService $userService;
    private TeamAccessService $teamAccessService;
    public function __construct()
    {
        $this->registrationService = new RegistrationService();
        $this->userService = new UserService();
        $this->eventService = new EventService();
        $this->teamAccessService = new TeamAccessService();
    }

    public function joinEvent()
    {
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        $email = $data["email"] ?? "";
        $eventId = $data["eventId"] ?? "";
        $firstName = $data["firstName"] ?? "";
        $lastName = $data["lastName"] ?? "";

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
            }


            // check if the user is already registered for the event
            $existingRegistration = $this->registrationService->isUserRegisteredForEvent($userId, $eventId);
            if ($existingRegistration) {
                return [
                    "success" => false,
                    "message" => "User is already registered for this event"
                ];
            }

            $registration = new Registration($eventId, $userId);
            $reg_id = $this->registrationService->registerUserForEvent($registration);
            $registration = $this->registrationService->getRegistrationById($reg_id);

            // TODO: Send email to the user with the ticket code and event details.

            return [
                "success" => true,
                "message" => "User registered for the event successfully",
                "data" => $registration
            ];
        } catch (\Throwable $th) {
            return [
                "success" => false,
                "message" => "Error registering user for the event: " . $th->getMessage()
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
        $event = $this->eventService->getEventWithUserId($userId, $eventId);
        if (!$event) {
            return [
                "success" => false,
                "message" => "Event not found"
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

    public function scanTicket()
    {
        $userId = $_SERVER["uid"];
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        $ticketCode = $data["ticketCode"] ?? "";

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