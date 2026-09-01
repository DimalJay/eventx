<?php

namespace Controllers;

use Models\Event;
use Services\RegistrationService;
use Services\UserService;
use Helpers\EmailHelper;

class CronController
{
    private RegistrationService $registrationService;
    private UserService $userService;

    public function __construct()
    {
        $this->registrationService = new RegistrationService();
        $this->userService = new UserService();
    }

    public function sendReminders()
    {
        set_time_limit(0); // Prevent PHP execution timeout
        try {
            // Find events starting tomorrow
            $query = "SELECT * FROM events WHERE DATE(startDate) = DATE(DATE_ADD(NOW(), INTERVAL 1 DAY))";
            $events = Event::query($query);

            $sentCount = 0;
            $eventsProcessed = 0;

            foreach ($events as $event) {
                $eventsProcessed++;
                $registrations = $this->registrationService->getRegistrationsList($event['id']);
                
                foreach ($registrations as $reg) {
                    if ($reg['status'] === 'GOING' || $reg['status'] === 'PENDING') {
                        $user = $this->userService->getUser($reg['userId']);
                        if ($user) {
                            $eventTs = strtotime($event["startDate"]);
                            
                            EmailHelper::sendWithTemplate($user['email'], "Reminder: " . $event["title"] . " is Tomorrow!", "reminder", [
                                "firstName" => $user["firstName"],
                                "lastName" => $user["lastName"],
                                "eventTitle" => $event["title"],
                                "eventDate" => $eventTs ? date("D, M j", $eventTs) : $event["startDate"],
                                "eventYear" => $eventTs ? date("Y", $eventTs) : "",
                                "eventTime" => $eventTs ? date("g:i A", $eventTs) : "",
                                "eventLocation" => $event["location"] ?? "TBD",
                                "eventLink" => EmailHelper::frontendUrl() . "/event/" . $event["id"],
                            ]);
                            $sentCount++;
                            
                            // Add 0.5s delay to prevent SMTP server overload
                            usleep(500000);
                        }
                    }
                }
            }

            return [
                "success" => true,
                "message" => "Cron job completed successfully.",
                "data" => [
                    "eventsProcessed" => $eventsProcessed,
                    "emailsSent" => $sentCount
                ]
            ];
        } catch (\Throwable $th) {
            return [
                "success" => false,
                "message" => "Error running reminders cron: " . $th->getMessage()
            ];
        }
    }
}
