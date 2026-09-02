<?php

use Middlewares\AuthMiddleware;
use Controllers\NotificationController;

$notificationController = new NotificationController();

$router->get("/notifications", [$notificationController, "getNotifications"], [AuthMiddleware::class]);
$router->put("/notification/read", [$notificationController, "markNotificationAsRead"], [AuthMiddleware::class]);
$router->put("/notifications/read-all", [$notificationController, "markAllNotificationsAsRead"], [AuthMiddleware::class]);
