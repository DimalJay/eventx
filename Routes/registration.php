<?php

use Middlewares\AuthMiddleware;
use Controllers\RegistrationController;

$registrationController = new RegistrationController();

$router->get("/registrations", [$registrationController, "listRegistrations"], [AuthMiddleware::class]);
$router->put("/registration/status", [$registrationController, "updateRegistrationStatus"], [AuthMiddleware::class]);
$router->post("/registration/scan", [$registrationController, "scanTicket"], [AuthMiddleware::class]);
