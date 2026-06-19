<?php

use Controllers\EventController;

$eventController = new EventController();

$router->get(
    "/discover-events",
    [$eventController, "getPublicEvents"]
);