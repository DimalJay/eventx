<?php
use Controllers\TeamLabelController;
use Middlewares\AuthMiddleware;

$teamLabelController = new TeamLabelController();

$router->get("/team-labels", [$teamLabelController, "getLabels"], [AuthMiddleware::class]);
$router->put("/team-labels", [$teamLabelController, "updateLabels"], [AuthMiddleware::class]);