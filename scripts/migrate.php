<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Models\User;
use Models\Payment;
use Models\PaymentAccount;
use Models\Ticket;
use Models\Event;
use Models\Registration;
use Models\Checkin;
use Models\Feedback;
use Models\Task;
use Models\TeamAccess;
use Models\Notification;
use Models\Admin;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$tables = [
    User::class,
    Event::class,
    Payment::class,
    PaymentAccount::class,
    TeamAccess::class,
    Ticket::class,
    Registration::class,
    Checkin::class,
    Feedback::class,
    Task::class,
    Notification::class,
    Admin::class,
];

foreach ($tables as $table) {
    try {
        $table::createClass();
        $name = (new \ReflectionClass($table))->getShortName();
        echo "✓ {$name} table created successfully.\n";
    } catch (Exception $e) {
        echo "✗ Error creating table: " . $e->getMessage() . "\n";
    }
}

// Incremental schema changes for existing tables
$db = new database\Database();

// events.category
$db->execute("ALTER TABLE `events` ADD COLUMN IF NOT EXISTS `category` VARCHAR(50) NOT NULL DEFAULT 'General'");
echo "✓ events.category column ensured.\n";

// Backfill category from legacy `[Category: X]` description prefixes
$db->execute(
    "UPDATE `events`
     SET `category` = TRIM(SUBSTRING(`description`,
         LOCATE('[Category:', `description`) + 10,
         LOCATE(']', `description`, LOCATE('[Category:', `description`)) - LOCATE('[Category:', `description`) - 10))
     WHERE `description` LIKE '[Category:%]%'"
);
echo "✓ events.category backfilled from existing descriptions.\n";
