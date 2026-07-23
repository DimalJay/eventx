<?php

namespace Controllers;

use Models\User;
use Models\Event;
use Models\Registration;

class AdminController
{
    public function getDashboardStats()
    {
        try {
            // Count active users
            $users = User::selectAll();
            $activeUsersCount = 0;
            foreach ($users as $user) {
                if (isset($user['accountStatus']) && strtolower($user['accountStatus']) === 'active') {
                    $activeUsersCount++;
                }
            }

            // Count events
            $events = Event::selectAll();
            $eventsCount = count($events);

            // Count registrations
            $registrations = Registration::selectAll();
            $registrationsCount = count($registrations);

            // Return counts
            return [
                "success" => true,
                "message" => "Admin dashboard stats retrieved successfully",
                "data" => [
                    "activeUsers" => [
                        "value" => number_format($activeUsersCount),
                        "change" => "+5.2%",
                        "isPositive" => true
                    ],
                    "eventsCreated" => [
                        "value" => number_format($eventsCount),
                        "change" => "+18.1%",
                        "isPositive" => true
                    ],
                    "registrations" => [
                        "value" => number_format($registrationsCount),
                        "change" => "0.00%",
                        "isPositive" => true
                    ],
                    "uptime" => [
                        "value" => "99.99%",
                        "change" => "0.00%",
                        "isPositive" => true
                    ]
                ]
            ];
        } catch (\Throwable $th) {
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error retrieving admin stats: " . $th->getMessage()
            ];
        }
    }
}
