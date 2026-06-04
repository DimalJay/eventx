<?php
use Controllers\UserController;

$userController = new UserController();

$router->post("/auth/register", [$userController, "createUser"]);