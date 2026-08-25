<?php

use Middlewares\AuthMiddleware;
use Controllers\EventController;
use Controllers\RegistrationController;

$eventController = new EventController();
$registrationController = new RegistrationController();

$router->get("/events", [$eventController, "listEvents"], [AuthMiddleware::class]);
$router->get("/event", [$eventController, "getEventDetails"]);
$router->post("/event", [$eventController, "createEvent"], [AuthMiddleware::class]);
$router->get("/discover-events", [$eventController, "getPublicEvents"]);
$router->get("/event/registrations", [$eventController, "getEventRegistrations"], [AuthMiddleware::class]);
$router->put("/event", [$eventController, "updateEvent"], [AuthMiddleware::class]);

// This route is for registering a user to an event.
$router->post("/join-event", [$registrationController, "joinEvent"]);
