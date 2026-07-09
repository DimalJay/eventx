<?php

use Middlewares\AuthMiddleware;
use Controllers\RegistrationController;

$registrationController = new RegistrationController();

$router->get("/registrations", [$registrationController, "listRegistrations"], [AuthMiddleware::class]);
