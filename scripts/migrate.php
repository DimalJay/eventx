<?php
require 'vendor/autoload.php';
require "src/Models/User.php";
require "src/Models/Team.php";

use Models\User;

try {
    User::createClass();
    echo "User Table created successfully.";
} catch (Exception $e) {
    echo "Error creating table: " . $e->getMessage();
}

use Models\Team;

try {
    Team::createClass();
    echo "Team Table created successfully.";
} catch (Exception $e) {
    echo "Error creating table: " . $e->getMessage();
}