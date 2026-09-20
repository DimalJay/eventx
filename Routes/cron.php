<?php

use Controllers\CronController;
use Middlewares\CronAuthMiddleware;

$cronController = new CronController();

$router->get("/cron/reminders", [$cronController, "sendReminders"], [CronAuthMiddleware::class]);
$router->get("/cron/analyze-sentiments", [$cronController, "analyzeSentiments"], [CronAuthMiddleware::class]);
