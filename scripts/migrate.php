<?php
require 'vendor/autoload.php';
require "src/Models/User.php";

use Models\User;

try {
    User::createClass();
    echo "User Table created successfully.";
} catch (Exception $e) {
    echo "Error creating table: " . $e->getMessage();
}