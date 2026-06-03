<?php
use Controllers\UserController;

$userController = new UserController();

$router->get("/users", [$userController, "listUsers"]);
$router->get("/user", [$userController, "getUserDetails"]);
$router->post("/user", [$userController, "createUser"]);