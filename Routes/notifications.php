<?php

use Controllers\NotificationController;
use Middlewares\AuthMiddleware;

$notificationController = new NotificationController();

$router->get('/notifications', [$notificationController, 'getNotifications'], [AuthMiddleware::class]);
$router->put('/notification/read', [$notificationController, 'markAsRead'], [AuthMiddleware::class]);
$router->put('/notifications/read-all', [$notificationController, 'markAllAsRead'], [AuthMiddleware::class]);
