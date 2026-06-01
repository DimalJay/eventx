<?php

require "app/Models/User.php";
use app\Models\User;

$user = new User(
    "test@example.com",
    "John",
    "Doe",
    "password123",
    "profile.jpg"
);
$user->updateRecord([
    "email" => "test@example.com"
], [
    "firstName" => "Jane"
]);