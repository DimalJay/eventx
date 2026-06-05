<?php

use Middlewares\AuthMiddleware;
use Controllers\EventController;

$eventController = new EventController();

$router->get("/events", [$eventController, "listEvents"]);
$router->get("/event", [$eventController, "getEventDetails"], [AuthMiddleware::class]);
$router->post("/event", [$eventController, "createEvent"]);