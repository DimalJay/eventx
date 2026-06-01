<?php

require "app/Models/User.php";
use app\Models\User;


// $user = new User(
//     "test@example.com",
//     "John",
//     "Doe",
//     "password123",
//     "profile.jpg"
// );
// User::updateRecord([
//     "email" => "test@example.com"
// ], [
//     "firstName" => "Jane"
// ]);

// User::selectAll();
// User::where(["email" => "test@example.com"]);

// $email = "test@example.com";
// $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
// $stmt->execute([$email]);
// $user = $stmt->fetch();
// print_r($user);

$all = User::selectAll();
print_r($all);