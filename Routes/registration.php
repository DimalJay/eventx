<?php

use Middlewares\AuthMiddleware;
use Controllers\RegistrationController;
use Controllers\InvitationController;

$registrationController = new RegistrationController();
$invitationController = new InvitationController();

$router->get("/registrations", [$registrationController, "listRegistrations"], [AuthMiddleware::class]);
$router->put("/registration/status", [$registrationController, "updateRegistrationStatus"], [AuthMiddleware::class]);
$router->post("/registration/scan", [$registrationController, "scanTicket"], [AuthMiddleware::class]);

$router->get("/ticket", [$registrationController, "getTicketDetails"]);

$router->post("/invitation/send", [$invitationController, "sendInvitations"], [AuthMiddleware::class]);
$router->get("/invitation/respond", [$invitationController, "respondToInvitation"]);
