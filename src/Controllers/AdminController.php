<?php

namespace Controllers;

use Models\User;
use Models\Event;
use Models\Registration;
use Models\Payment;
use Models\Admin;

class AdminController
{
    public function getDashboardStats()
    {
        try {
            $range = $_GET['range'] ?? 'week';

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

            // Fetch payments
            $payments = Payment::selectAll();

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
                    "timestamp" => $timeSec,
                    "email" => $u['email'] ?? null,
                    "accountStatus" => $u['accountStatus'] ?? 'active'
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
                    "timestamp" => $timeSec,
                    "description" => $e['description'] ?? null,
                    "location" => $e['location'] ?? null,
                    "ticketPrice" => isset($e['ticketPrice']) ? floatval($e['ticketPrice']) : 0.0
                ];
            }

            // Sort all activities by timestamp descending
            usort($activities, function($a, $b) {
                return $b['timestamp'] - $a['timestamp'];
            });

            // Calculate chart data based on range
            $chartData = [];
            $dailyCounts = [];

            if ($range === 'year') {
                for ($i = 11; $i >= 0; $i--) {
                    $dateStr = date('Y-m', strtotime("-$i months"));
                    $label = date('M', strtotime("-$i months"));
                    $dailyCounts[$dateStr] = [
                        "label" => $label,
                        "registrations" => 0,
                        "revenue" => 0
                    ];
                }

                foreach ($registrations as $r) {
                    if (isset($r['registeredAt'])) {
                        $regMonth = date('Y-m', strtotime($r['registeredAt']));
                        if (array_key_exists($regMonth, $dailyCounts)) {
                            $dailyCounts[$regMonth]["registrations"]++;
                        }
                    }
                }

                foreach ($payments as $p) {
                    $paymentTime = isset($p['paymentAt']) ? $p['paymentAt'] : null;
                    if ($paymentTime) {
                        $timeStr = ($paymentTime instanceof \DateTime) ? $paymentTime->format('Y-m') : date('Y-m', strtotime($paymentTime));
                        if (array_key_exists($timeStr, $dailyCounts)) {
                            $dailyCounts[$timeStr]["revenue"] += (float)$p['amount'];
                        }
                    }
                }
            } elseif ($range === 'month') {
                for ($i = 3; $i >= 0; $i--) {
                    $label = "Week " . (4 - $i);
                    $dailyCounts[$i] = [
                        "label" => $label,
                        "registrations" => 0,
                        "revenue" => 0,
                        "start" => strtotime("-" . (($i + 1) * 7) . " days"),
                        "end" => strtotime("-" . ($i * 7) . " days")
                    ];
                }

                foreach ($registrations as $r) {
                    if (isset($r['registeredAt'])) {
                        $regTime = strtotime($r['registeredAt']);
                        foreach ($dailyCounts as $key => $bucket) {
                            if ($regTime >= $bucket["start"] && $regTime < $bucket["end"]) {
                                $dailyCounts[$key]["registrations"]++;
                            }
                        }
                    }
                }

                foreach ($payments as $p) {
                    $paymentTime = isset($p['paymentAt']) ? $p['paymentAt'] : null;
                    if ($paymentTime) {
                        $payTime = ($paymentTime instanceof \DateTime) ? $paymentTime->getTimestamp() : strtotime($paymentTime);
                        foreach ($dailyCounts as $key => $bucket) {
                            if ($payTime >= $bucket["start"] && $payTime < $bucket["end"]) {
                                $dailyCounts[$key]["revenue"] += (float)$p['amount'];
                            }
                        }
                    }
                }
            } else {
                for ($i = 6; $i >= 0; $i--) {
                    $dateStr = date('Y-m-d', strtotime("-$i days"));
                    $label = date('D', strtotime("-$i days"));
                    $dailyCounts[$dateStr] = [
                        "label" => $label,
                        "registrations" => 0,
                        "revenue" => 0
                    ];
                }

                foreach ($registrations as $r) {
                    if (isset($r['registeredAt'])) {
                        $regDate = date('Y-m-d', strtotime($r['registeredAt']));
                        if (array_key_exists($regDate, $dailyCounts)) {
                            $dailyCounts[$regDate]["registrations"]++;
                        }
                    }
                }

                foreach ($payments as $p) {
                    $paymentTime = isset($p['paymentAt']) ? $p['paymentAt'] : null;
                    if ($paymentTime) {
                        $timeStr = ($paymentTime instanceof \DateTime) ? $paymentTime->format('Y-m-d') : date('Y-m-d', strtotime($paymentTime));
                        if (array_key_exists($timeStr, $dailyCounts)) {
                            $dailyCounts[$timeStr]["revenue"] += (float)$p['amount'];
                        }
                    }
                }
            }

            $maxReg = 0;
            $maxRev = 0;
            foreach ($dailyCounts as $day) {
                if ($day["registrations"] > $maxReg) {
                    $maxReg = $day["registrations"];
                }
                if ($day["revenue"] > $maxRev) {
                    $maxRev = $day["revenue"];
                }
            }

            foreach ($dailyCounts as $day) {
                $regPercentage = $maxReg > 0 ? round(($day["registrations"] / $maxReg) * 100) : 0;
                $revPercentage = $maxRev > 0 ? round(($day["revenue"] / $maxRev) * 100) : 0;

                $chartData[] = [
                    "label" => $day["label"],
                    "registrations" => $day["registrations"],
                    "regPercentage" => $regPercentage,
                    "revenue" => round($day["revenue"], 2),
                    "revPercentage" => $revPercentage
                ];
            }

            // Return counts, activities, and chartData
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
                    "recentActivities" => $activities,
                    "chartData" => $chartData
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

    public function getAllActivities()
    {
        try {
            $users = User::selectAll();
            $events = Event::selectAll();

            $activities = [];

            foreach ($users as $u) {
                $timeSec = isset($u['createdAt']) ? strtotime($u['createdAt']) : time();
                $activities[] = [
                    "id" => "user_" . ($u['id'] ?? uniqid()),
                    "type" => "user",
                    "title" => "New User Registered: " . ($u['firstName'] ?? '') . " " . ($u['lastName'] ?? ''),
                    "time" => $this->getRelativeTime($timeSec),
                    "timestamp" => $timeSec,
                    "date" => isset($u['createdAt']) ? $u['createdAt'] : date('Y-m-d H:i:s'),
                    "email" => $u['email'] ?? null,
                    "accountStatus" => $u['accountStatus'] ?? 'active'
                ];
            }

            foreach ($events as $e) {
                $timeSec = isset($e['createdAt']) ? strtotime($e['createdAt']) : time();
                $activities[] = [
                    "id" => "event_" . ($e['id'] ?? uniqid()),
                    "type" => "event",
                    "title" => "New Event Created: " . ($e['title'] ?? ''),
                    "time" => $this->getRelativeTime($timeSec),
                    "timestamp" => $timeSec,
                    "date" => isset($e['createdAt']) ? $e['createdAt'] : date('Y-m-d H:i:s'),
                    "description" => $e['description'] ?? null,
                    "location" => $e['location'] ?? null,
                    "ticketPrice" => isset($e['ticketPrice']) ? floatval($e['ticketPrice']) : 0.0
                ];
            }

            // Sort all activities by timestamp descending
            usort($activities, function($a, $b) {
                return $b['timestamp'] - $a['timestamp'];
            });

            return [
                "success" => true,
                "message" => "All activities retrieved successfully",
                "data" => $activities
            ];
        } catch (\Throwable $th) {
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error retrieving activities: " . $th->getMessage()
            ];
        }
    }

    public function updatePassword()
    {
        try {
            $id = $_SERVER["uid"] ?? null;
            if (!$id) {
                http_response_code(401);
                return [
                    "success" => false,
                    "message" => "Unauthorized"
                ];
            }

            $data = json_decode(file_get_contents('php://input'), true);
            $currentPassword = $data['currentPassword'] ?? '';
            $newPassword = $data['newPassword'] ?? '';

            if (empty($currentPassword) || empty($newPassword)) {
                http_response_code(400);
                return [
                    "success" => false,
                    "message" => "All fields are required"
                ];
            }

            $admin = Admin::where(["id" => $id])[0] ?? null;
            if (!$admin) {
                http_response_code(404);
                return [
                    "success" => false,
                    "message" => "Admin user not found"
                ];
            }

            // Verify current password
            if (!password_verify($currentPassword, $admin['password'])) {
                http_response_code(400);
                return [
                    "success" => false,
                    "message" => "Incorrect current password"
                ];
            }

            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            // Update database
            Admin::updateRecord(["id" => $id], ["password" => $hashedPassword]);

            return [
                "success" => true,
                "message" => "Password updated successfully"
            ];
        } catch (\Throwable $th) {
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error updating password: " . $th->getMessage()
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
