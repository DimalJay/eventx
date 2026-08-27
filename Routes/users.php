<?php
use Controllers\UserController;
use Middlewares\AuthMiddleware;
use Middlewares\AdminAuthMiddleware;

$userController = new UserController();

$router->get("/users", [$userController, "listUsers"], [AdminAuthMiddleware::class]);
$router->get("/user", [$userController, "getUserDetails"], [AuthMiddleware::class]);
$router->post("/user", [$userController, "createUser"]);
$router->put("/user", [$userController, "updateUser"], [AuthMiddleware::class]);
$router->delete("/user", [$userController, "deleteUser"], [AuthMiddleware::class]);
$router->get("/user/registrations", [$userController, "getUserRegistrations"], [AuthMiddleware::class]);
$router->put("/users/status", [$userController, "updateUserStatus"], [AdminAuthMiddleware::class]);
