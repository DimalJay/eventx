<?php
require 'vendor/autoload.php';
require 'src/Models/User.php';
require 'src/Models/Payment.php';
require 'src/Models/Ticket.php';
require 'src/Models/Event.php';
require 'src/Models/Registration.php';
require 'src/Models/Checkin.php';


use Models\User;
use Models\Payment;
use Models\Event;
use Models\Ticket;
use Models\Registration;
use Models\Checkin;
use Models\Feedback;
use Models\Task;
use Models\TeamAccess;

try {
    User::createClass();
    echo "User Table created successfully.";
} catch (Exception $e) {
    echo "Error creating table: " . $e->getMessage();
}


try {
    Event::createClass();
    echo "Event Table created successfully.";
} catch (Exception $e) {
    echo "Error creating table: " . $e->getMessage();
}

try {
    Payment::createClass();
    echo "Payment Table created successfully.";
} catch (Exception $e) {
    echo "Error creating table: " . $e->getMessage();
}

try {
    TeamAccess::createClass();
    echo "Team Table created successfully.";
} catch (Exception $e) {
    echo "Error creating table: " . $e->getMessage();
}

try {
    Ticket::createClass();
    echo "Ticket Table created successfully.";
} catch (Exception $e) {
    echo "Error creating table: " . $e->getMessage();
}

try {
    Registration::createClass();
    echo "Registration Table created successfully.";
} catch (Exception $e) {
    echo "Error creating table: " . $e->getMessage();
}

try {
    Checkin::createClass();
    echo "Checkin Table created successfully.";
} catch (Exception $e) {
    echo "Error creating table: " . $e->getMessage();
}

try {
    Feedback::createClass();
    echo "Feedback Table created successfully.";
} catch (Exception $e) {
    echo "Error creating table: " . $e->getMessage();
}

try {
    Feedback::createClass();
    echo "Feedback Table created successfully.";
} catch (Exception $e) {
    echo "Error creating table: " . $e->getMessage();
}

try {
    Task::createClass();
    echo "Task Table created successfully.";
} catch (Exception $e) {
    echo "Error creating table: " . $e->getMessage();
}