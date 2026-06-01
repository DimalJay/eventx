<?php
require 'vendor/autoload.php';
require "app/Models/User.php";

use app\Models\User;

try {
    User::createClass();
    echo "User Table created successfully.";
} catch (Exception $e) {
    echo "Error creating table: " . $e->getMessage();
}