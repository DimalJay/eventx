<?php

namespace Services;

use Models\User;
use Models\Event;
use Models\PaymentAccount;
use Models\Payment;
use Models\Registration;
use Models\Ticket;
use Models\Checkin;
use Models\TeamAccess;
use Models\Notification;
use Models\Feedback;
use Models\Task;

class UserService
{
    public function __construct()
    {
    }

    public function getAllUsers()
    {
        $users = User::selectAll();
        return array_map(function ($user) {
            unset($user['password']);
            return $user;
        }, $users);
    }

    public function createUser(User $user)
    {
        return $user->save();
    }

    public function getUser(String $id)
    {
        $users = User::where(["id" => $id]);
        return count($users) > 0 ? $users[0] : null;
    }

    public function getUserByEmail(String $email)
    {
        $users = User::where(["email" => $email]);
        return count($users) > 0 ? $users[0] : null;
    }

    public function updateUser(String $id, array $data)
    {
        return User::updateRecord(["id" => $id], $data);
    }

    public function deleteUser(String $id)
    {
        $events = Event::where(["organizerId" => $id]);
        $eventIds = array_map(fn($event) => $event["id"], $events);

        foreach ($eventIds as $eventId) {
            Registration::deleteRecord(["eventId" => $eventId]);
            Ticket::deleteRecord(["eventId" => $eventId]);
            Checkin::deleteRecord(["eventId" => $eventId]);
            TeamAccess::deleteRecord(["eventId" => $eventId]);
            Task::deleteRecord(["eventId" => $eventId]);
            Feedback::deleteRecord(["eventId" => $eventId]);
        }

        Event::deleteRecord(["organizerId" => $id]);

        PaymentAccount::deleteRecord(["userId" => $id]);
        Payment::deleteRecord(["userId" => $id]);
        Registration::deleteRecord(["userId" => $id]);
        Ticket::deleteRecord(["userId" => $id]);
        Checkin::deleteRecord(["userId" => $id]);
        TeamAccess::deleteRecord(["userId" => $id]);
        Feedback::deleteRecord(["participantId" => $id]);
        Notification::deleteRecord(["userId" => $id]);
        Task::deleteRecord(["createdBy" => $id]);
        Task::deleteRecord(["assignedTo" => $id]);
        Task::deleteRecord(["assignedBy" => $id]);

        return User::deleteRecord(["id" => $id]);
    }
}