<?php
use Controllers\UserController;
use Middlewares\AuthMiddleware;

$userController = new UserController();

$router->get("/users", [$userController, "listUsers"]);
$router->get("/user", [$userController, "getUserDetails"], [AuthMiddleware::class]);
$router->post("/user", [$userController, "createUser"]);
$router->put("/user", [$userController, "updateUser"], [AuthMiddleware::class]);