<?php

namespace Services;

use Models\Registration;
use Models\Event;

use Exception;
class RegistrationService
{
    public function __construct() {}

    public function registerUserForEvent(Registration $registration)
    {
        $event = Event::where(["id" => $registration->getEventId()])[0] ?? null;
        if(!$event) {
            throw new Exception("Event not found");
        }
        $capacity =  $event['capacity'] ?? 0;
        if($capacity > 0) {
            $registrationCount = count($this->getRegistrationsByEventId($registration->getEventId()));
            if($registrationCount >= $capacity) {
                if($event['waitlistEnabled'] ?? false) {
                    $registration->setInWaitlist();
                } else {
                    throw new Exception("Event is full and waitlist is not enabled");
                }
            }
        }

        return $registration->save();
    }

    public function isUserRegisteredForEvent($userId, $eventId)
    {
        $registrations = Registration::where(["userId" => $userId, "eventId" => $eventId]);
        return count($registrations) > 0;
    }

    public function getRegistrationById($reg_id)
    {
        $registrations = Registration::where(["id" => $reg_id]);
        return count($registrations) > 0 ? $registrations[0] : null;
    }

    public function getRegistrationsByEventId($eventId)
    {
        return Registration::where(["eventId" => $eventId]);
    }

    public function getRegistrationsList($eventId)
    {
        return Registration::query('SELECT r.*, u.firstName, u.lastName, u.email
            FROM Registrations r
            JOIN users u ON r.userId = u.id
            WHERE r.eventId = :eventId', ['eventId' => $eventId]);
        
    }

    public function updateRegistrationStatus($registrationId, $status)
    {
        $registrations = Registration::where(["id" => $registrationId]);
        if (count($registrations) < 1) {
            throw new Exception("Registration not found");
        }
        $updateData =  ["status" => $status];
        if($status == 'GOING') {
            $updateData['chekingTime'] = (new \DateTime())->format('Y-m-d H:i:s');
        }
        Registration::updateRecord(["id" => $registrationId], $updateData);
    }

    public function getRegistrationByTicketCode($ticketCode)
    {
        $registrations = Registration::where(["ticketCode" => $ticketCode]);
        return count($registrations) > 0 ? $registrations[0] : null;
    }
}