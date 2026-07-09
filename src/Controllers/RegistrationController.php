<?php

namespace Controllers;

use Services\RegistrationService;
use Services\UserService;
use Services\EventService;
use Models\Registration;
use Models\User;

class RegistrationController
{
    private RegistrationService $registrationService;
    private EventService $eventService;
    private UserService $userService;
    public function __construct()
    {
        $this->registrationService = new RegistrationService();
        $this->userService = new UserService();
        $this->eventService = new EventService();
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

}