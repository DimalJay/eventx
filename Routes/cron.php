<?php

use Controllers\CronController;

$cronController = new CronController();

$router->get("/cron/reminders", [$cronController, "sendReminders"]);
