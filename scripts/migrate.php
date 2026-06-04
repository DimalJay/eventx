<?php
require 'vendor/autoload.php';

use Models\User;
use Models\Event;

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