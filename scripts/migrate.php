<?php
require 'vendor/autoload.php';

use Models\User;
use Models\Team;

try {
    User::createClass();
    echo "User Table created successfully.";
} catch (Exception $e) {
    echo "Error creating table: " . $e->getMessage();
}


try {
    Team::createClass();
    echo "Team Table created successfully.";
} catch (Exception $e) {
    echo "Error creating table: " . $e->getMessage();
}