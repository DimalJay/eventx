<?php

use Middlewares\AuthMiddleware;
use Controllers\EventController;

$eventController = new EventController();

$router->get("/events", [$eventController, "listEvents"]);
$router->get("/event", [$eventController, "getEventDetails"]);
$router->post("/event", [$eventController, "createEvent"], [AuthMiddleware::class]);
$router->put("/event", [$eventController, "updateEvent"], [AuthMiddleware::class]);
