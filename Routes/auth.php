<?php
use Controllers\UserController;
use Controllers\AuthController;
use Middlewares\AuthMiddleware;

$userController = new UserController();
$authController = new AuthController();

$router->post("/auth/register", [$userController, "createUser"]);
$router->post("/auth/login", [$authController, "login"]);
$router->post("/auth/admin-login", [$authController, "adminLogin"]);
$router->post("/auth/logout", [$authController, "logout"]);
$router->post("/auth/google-login", [$authController, "googleLogin"]);
$router->post("/auth/change-password", [$authController, "changePassword"], [AuthMiddleware::class]);
