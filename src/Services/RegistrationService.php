<?php

namespace Services;

use Models\Registration;
use Models\Event;
use Models\Ticket;

use Exception;
class RegistrationService
{
    public function __construct() {}

    private function formatRegistration(array $registration): array
    {
        if (!empty($registration['customFields']) && is_string($registration['customFields'])) {
            $decoded = json_decode($registration['customFields'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $registration['customFields'] = $decoded;
            }
        }
        return $registration;
    }

    public function registerUserForEvent(Registration $registration)
    {
        $event = Event::where(["id" => $registration->getEventId()])[0] ?? null;
        if(!$event) {
            throw new Exception("Event not found");
        }

        if ((int)$event['organizerId'] === (int)$registration->getUserId()) {
            throw new Exception("Organizer cannot register to their own event");
        }

        if ($this->isUserRegisteredForEvent($registration->getUserId(), $registration->getEventId())) {
            throw new Exception("User is already registered for this event");
        }

        $capacity = (int)($event['capacity'] ?? 0);
        if ($capacity > 0) {
            $registrationCount = $this->getActiveRegistrationCount((int)$registration->getEventId());
            if ($registrationCount >= $capacity) {
                $waitlistEnabled = filter_var($event['waitlistEnabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
                if ($waitlistEnabled) {
                    $registration->setInWaitlist();
                } else {
                    throw new Exception("Event is full. Registration is closed.");
                }
            }
        }

        return $registration->save();
    }

    public function getActiveRegistrationCount(int $eventId): int
    {
        $rows = Registration::query(
            "SELECT COUNT(*) as count FROM Registrations 
             WHERE eventId = :eventId 
               AND status NOT IN ('WAITLIST', 'CANCELLED', 'NOT_GOING')",
            ['eventId' => $eventId]
        );
        return (int)($rows[0]['count'] ?? 0);
    }

    public function isUserRegisteredForEvent($userId, $eventId)
    {
        $registrations = Registration::where(["userId" => $userId, "eventId" => $eventId]);
        return count($registrations) > 0;
    }

    public function getRegistrationById($reg_id)
    {
        $registrations = Registration::query(
            'SELECT r.*, t.ticketCode AS ticketCode
             FROM Registrations r
             LEFT JOIN tickets t ON t.registerId = r.id
             WHERE r.id = :id',
            ['id' => $reg_id]
        );
        return count($registrations) > 0 ? $this->formatRegistration($registrations[0]) : null;
    }

    public function getRegistrationsByEventId($eventId)
    {
        return Registration::where(["eventId" => $eventId]);
    }

    public function getRegistrationsList($eventId)
    {
        $rows = Registration::query('SELECT r.*, t.ticketCode AS ticketCode, u.firstName, u.lastName, u.email
            FROM Registrations r
            LEFT JOIN tickets t ON t.registerId = r.id
            JOIN users u ON r.userId = u.id
            WHERE r.eventId = :eventId', ['eventId' => $eventId]);

        return array_map([$this, 'formatRegistration'], $rows);
        
    }

    public function updateRegistrationStatus($registrationId, $status)
    {
        $registrations = Registration::where(["id" => $registrationId]);
        if (count($registrations) < 1) {
            throw new Exception("Registration not found");
        }
        $updateData =  ["status" => $status];
        if($status == 'GOING') {
            $updateData['chekingTime'] = (new \DateTime('now', new \DateTimeZone('Asia/Colombo')))->format('Y-m-d H:i:s');
        }
        Registration::updateRecord(["id" => $registrationId], $updateData);
    }

    public function getRegistrationByTicketCode($ticketCode)
    {
        $registrations = Registration::query(
            'SELECT r.*, t.ticketCode AS ticketCode
             FROM Registrations r
             JOIN tickets t ON t.registerId = r.id
             WHERE t.ticketCode = :code',
            ['code' => $ticketCode]
        );
        return count($registrations) > 0 ? $this->formatRegistration($registrations[0]) : null;
    }

    /**
     * Create a ticket row for a registration and link it via Registrations.ticketId.
     */
    public function createTicketForRegistration(int $regId, int $eventId, int $userId, ?string $ticketCode = null, int $paymentId = 0)
    {
        $ticketCode = $ticketCode ?: uniqid();
        $ticket = new Ticket($eventId, $userId, $ticketCode, $regId, $paymentId);
        $ticketId = $ticket->save();
        Registration::updateRecord(["id" => $regId], ["ticketId" => $ticketId]);
        return Ticket::where(["id" => $ticketId])[0] ?? null;
    }

    /**
     * Ensure a registration has an INVITE-* ticket row (used by the invitation flow).
     */
    public function ensureInviteTicket(int $regId, int $eventId, int $userId, string $role)
    {
        $tickets = Ticket::where(["registerId" => $regId]);
        $ticket = $tickets[0] ?? null;
        if (!$ticket) {
            $this->createTicketForRegistration($regId, $eventId, $userId, "INVITE-" . $role . "-" . uniqid());
            return;
        }
        if (strpos((string) $ticket["ticketCode"], 'INVITE-') !== 0) {
            Ticket::updateRecord(["id" => $ticket["id"]], ["ticketCode" => "INVITE-" . $role . "-" . uniqid()]);
        }
    }
}