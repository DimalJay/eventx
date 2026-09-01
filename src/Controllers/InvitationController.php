<?php

namespace Controllers;

use Services\EventService;
use Services\UserService;
use Services\RegistrationService;
use Services\TeamAccessService;
use Helpers\EmailHelper;
use Models\Registration;
use Models\User;

class InvitationController
{
    private EventService $eventService;
    private UserService $userService;
    private RegistrationService $registrationService;
    private TeamAccessService $teamAccessService;

    public function __construct()
    {
        $this->eventService = new EventService();
        $this->userService = new UserService();
        $this->registrationService = new RegistrationService();
        $this->teamAccessService = new TeamAccessService();
    }

    public function sendInvitations()
    {
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        $eventId = $data['eventId'] ?? '';
        $role = $data['role'] ?? ''; // GUEST_SPEAKER or VVIP_VIP
        $emails = $data['emails'] ?? [];

        if (empty($eventId) || empty($role) || empty($emails)) {
            return [
                "success" => false,
                "message" => "Missing required fields: eventId, role, and emails are required."
            ];
        }

        $event = $this->eventService->getEvent($eventId);
        if (!$event) {
            return [
                "success" => false,
                "message" => "Event not found."
            ];
        }

        $canManage = $this->teamAccessService->hasTeamAccess((int) ($_SERVER["uid"] ?? 0), (int) $eventId);
        if (!$canManage) {
            http_response_code(403);
            return [
                "success" => false,
                "message" => "Unauthorized: You do not have access to this event"
            ];
        }

        $roleLabel = $role === 'GUEST_SPEAKER' ? 'Guest Speaker' : 'VVIP / VIP Participant';
        $backendUrl = \Helpers\EmailHelper::backendUrl();
        $secretKey = ($_ENV['APP_SECRET'] ?? getenv('APP_SECRET')) ?: 'secret_key_123';
        
        $startTs = strtotime($event["startDate"]);
        $endTs = strtotime($event["endDate"]);
        
        $formattedDate = $startTs ? date("D, M j, Y", $startTs) : $event["startDate"];
        $formattedTime = $startTs && $endTs ? date("g:i A", $startTs) . " – " . date("g:i A", $endTs) : "";

        // Calendar link
        $calendarStart = $startTs ? gmdate("Ymd\THis\Z", $startTs) : "";
        $calendarEnd = $endTs ? gmdate("Ymd\THis\Z", $endTs) : "";
        $calendarLink = "https://calendar.google.com/calendar/render?action=TEMPLATE"
            . "&text=" . urlencode($roleLabel . ": " . $event["title"])
            . "&dates=" . $calendarStart . "/" . $calendarEnd
            . "&details=" . urlencode("You are cordially invited as a " . $roleLabel . " for " . $event["title"])
            . "&location=" . urlencode($event["location"] ?? "");

        $sentCount = 0;
        foreach ($emails as $email) {
            $email = trim($email);
            if (empty($email)) continue;

            // Generate HMAC tokens for accept and decline
            $acceptToken = hash_hmac('sha256', $eventId . '-' . $email . '-accept', $secretKey);
            $declineToken = hash_hmac('sha256', $eventId . '-' . $email . '-decline', $secretKey);

            $acceptLink = $backendUrl . "/eventx/api/v1/invitation/respond?eventId=" . $eventId 
                . "&email=" . urlencode($email) . "&role=" . urlencode($role) . "&response=accept&token=" . $acceptToken;
            $declineLink = $backendUrl . "/eventx/api/v1/invitation/respond?eventId=" . $eventId 
                . "&email=" . urlencode($email) . "&role=" . urlencode($role) . "&response=decline&token=" . $declineToken;

            $success = EmailHelper::sendWithTemplate($email, "Exclusive Invitation: " . $event["title"], "invitation", [
                "roleLabel" => $roleLabel,
                "eventTitle" => $event["title"],
                "eventDate" => $formattedDate,
                "eventTime" => $formattedTime,
                "eventLocation" => $event["location"] ?? "TBD",
                "acceptLink" => $acceptLink,
                "declineLink" => $declineLink,
                "calendarLink" => $calendarLink
            ]);

            if ($success) {
                $sentCount++;
            }
            usleep(200000); // 0.2s delay
        }

        return [
            "success" => true,
            "message" => "Invitations processed.",
            "data" => [
                "sentCount" => $sentCount
            ]
        ];
    }

    public function respondToInvitation()
    {
        $eventId = $_GET['eventId'] ?? '';
        $email = $_GET['email'] ?? '';
        $role = $_GET['role'] ?? '';
        $response = $_GET['response'] ?? ''; // accept or decline
        $token = $_GET['token'] ?? '';

        if (empty($eventId) || empty($email) || empty($role) || empty($response) || empty($token)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Missing response parameters."]);
            exit;
        }

        $secretKey = ($_ENV['APP_SECRET'] ?? getenv('APP_SECRET')) ?: 'secret_key_123';
        $expectedToken = hash_hmac('sha256', $eventId . '-' . $email . '-' . $response, $secretKey);

        if (!hash_equals($expectedToken, $token)) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Invalid security token."]);
            exit;
        }

        $event = $this->eventService->getEvent($eventId);
        if (!$event) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Event not found."]);
            exit;
        }

        try {
            // Find or create user
            $user = $this->userService->getUserByEmail($email);
            if (!$user) {
                $lastName = $role === 'GUEST_SPEAKER' ? 'Speaker' : 'VIP';
                $userModel = new User($email, "Invited", $lastName, "", null, "temp");
                $userId = $this->userService->createUser($userModel);
            } else {
                $userId = $user['id'];
            }

            // Check if registration exists
            $existing = Registration::where(["userId" => $userId, "eventId" => $eventId])[0] ?? null;
            $status = $response === 'accept' ? 'PENDING' : 'NOT_GOING';

            if ($existing) {
                $updateData = ["status" => $status];
                if (strpos($existing['ticketCode'], 'INVITE-') !== 0) {
                    $updateData['ticketCode'] = "INVITE-" . $role . "-" . uniqid();
                }
                Registration::updateRecord(["id" => $existing['id']], $updateData);
            } else {
                $registration = new Registration($eventId, $userId);
                $regId = $this->registrationService->registerUserForEvent($registration);
                
                $updateData = [
                    "status" => $status,
                    "ticketCode" => "INVITE-" . $role . "-" . uniqid()
                ];
                Registration::updateRecord(["id" => $regId], $updateData);
            }

            // Redirect to Next.js
            $redirectUrl = \Helpers\EmailHelper::frontendUrl() . "/invitation/status?success=true&response=" . $response 
                . "&eventTitle=" . urlencode($event['title']) . "&eventId=" . $eventId;
            header("Location: " . $redirectUrl);
            exit;

        } catch (\Throwable $th) {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Error recording response: " . $th->getMessage()]);
            exit;
        }
    }
}
