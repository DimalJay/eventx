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

            // Calculate recent activities
            $activities = [];

            // Sort users by createdAt descending
            usort($users, function($a, $b) {
                return strtotime($b['createdAt'] ?? '0') - strtotime($a['createdAt'] ?? '0');
            });
            $latestUsers = array_slice($users, 0, 3);
            foreach ($latestUsers as $u) {
                $timeSec = isset($u['createdAt']) ? strtotime($u['createdAt']) : time();
                $activities[] = [
                    "id" => "user_" . ($u['id'] ?? uniqid()),
                    "type" => "user",
                    "title" => "New User Registered: " . ($u['firstName'] ?? '') . " " . ($u['lastName'] ?? ''),
                    "time" => $this->getRelativeTime($timeSec),
                    "timestamp" => $timeSec
                ];
            }

            // Sort events by createdAt descending
            usort($events, function($a, $b) {
                return strtotime($b['createdAt'] ?? '0') - strtotime($a['createdAt'] ?? '0');
            });
            $latestEvents = array_slice($events, 0, 3);
            foreach ($latestEvents as $e) {
                $timeSec = isset($e['createdAt']) ? strtotime($e['createdAt']) : time();
                $activities[] = [
                    "id" => "event_" . ($e['id'] ?? uniqid()),
                    "type" => "event",
                    "title" => "New Event Created: " . ($e['title'] ?? ''),
                    "time" => $this->getRelativeTime($timeSec),
                    "timestamp" => $timeSec
                ];
            }

            // Sort all activities by timestamp descending
            usort($activities, function($a, $b) {
                return $b['timestamp'] - $a['timestamp'];
            });

            // Return counts and activities
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
                    ],
                    "recentActivities" => $activities
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

    private function getRelativeTime($timestamp)
    {
        $diff = time() - $timestamp;
        if ($diff < 60) {
            return "Just now";
        }
        $diffMins = round($diff / 60);
        if ($diffMins < 60) {
            return $diffMins . " mins ago";
        }
        $diffHours = round($diff / 3600);
        if ($diffHours < 24) {
            return $diffHours . " hour" . ($diffHours > 1 ? "s" : "") . " ago";
        }
        $diffDays = round($diff / 86400);
        return $diffDays . " day" . ($diffDays > 1 ? "s" : "") . " ago";
    }
}
