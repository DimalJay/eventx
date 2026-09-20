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
use Models\TeamLabel;
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
    TeamLabel::class,
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

// events.category (cross-compatible with MySQL and MariaDB)
$hasCategory = $db->query(
    "SELECT COUNT(*) AS c FROM information_schema.columns
     WHERE table_schema = DATABASE() AND table_name = 'events' AND column_name = 'category'"
)['c'] ?? 0;

if ((int) $hasCategory === 0) {
    $db->execute("ALTER TABLE `events` ADD COLUMN `category` VARCHAR(50) NOT NULL DEFAULT 'General'");
    echo "✓ events.category column added.\n";
} else {
    echo "✓ events.category column already exists.\n";
}

// Backfill category from legacy `[Category: X]` description prefixes
$db->execute(
    "UPDATE `events`
     SET `category` = TRIM(SUBSTRING(`description`,
         LOCATE('[Category:', `description`) + 10,
         LOCATE(']', `description`, LOCATE('[Category:', `description`)) - LOCATE('[Category:', `description`) - 10))
     WHERE `description` LIKE '[Category:%]%'"
);
echo "✓ events.category backfilled from existing descriptions.\n";

// team_access.label (cross-compatible with MySQL and MariaDB)
$hasTeamLabel = $db->query(
    "SELECT COUNT(*) AS c FROM information_schema.columns
     WHERE table_schema = DATABASE() AND table_name = 'team_access' AND column_name = 'label'"
)['c'] ?? 0;

if ((int) $hasTeamLabel === 0) {
    $db->execute("ALTER TABLE `team_access` ADD COLUMN `label` VARCHAR(50) NULL");
    echo "✓ team_access.label column added.\n";
} else {
    echo "✓ team_access.label column already exists.\n";
}

// events.customFields (JSON of {name, key, type} field definitions)
$hasEventCustomFields = $db->query(
    "SELECT COUNT(*) AS c FROM information_schema.columns
     WHERE table_schema = DATABASE() AND table_name = 'events' AND column_name = 'customFields'"
)['c'] ?? 0;

if ((int) $hasEventCustomFields === 0) {
    $db->execute("ALTER TABLE `events` ADD COLUMN `customFields` TEXT NULL");
    echo "✓ events.customFields column added.\n";
} else {
    echo "✓ events.customFields column already exists.\n";
}

// Registrations.customFields (JSON of submitted key => value data)
$hasRegistrationCustomFields = $db->query(
    "SELECT COUNT(*) AS c FROM information_schema.columns
     WHERE LOWER(table_schema) = LOWER(DATABASE())
       AND LOWER(table_name) = 'registrations'
       AND LOWER(column_name) = 'customfields'"
)['c'] ?? 0;

if ((int) $hasRegistrationCustomFields === 0) {
    $db->execute("ALTER TABLE `Registrations` ADD COLUMN `customFields` TEXT NULL");
    echo "✓ Registrations.customFields column added.\n";
} else {
    echo "✓ Registrations.customFields column already exists.\n";
}
