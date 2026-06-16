<?php
use Controllers\UserController;
use Controllers\AuthController;

$userController = new UserController();
$authController = new AuthController();

$router->post("/auth/register", [$userController, "createUser"]);
$router->post("/auth/login", [$authController, "login"]);
$router->post("/auth/logout", [$authController, "logout"]);