<?php

use Controllers\AdminController;
use Middlewares\AdminAuthMiddleware;

$adminController = new AdminController();

$router->get("/admin/dashboard-stats", [$adminController, "getDashboardStats"]);
$router->get("/admin/dashboard-stats", [$adminController, "getDashboardStats"], [AdminAuthMiddleware::class]);
$router->get("/admin/activities", [$adminController, "getAllActivities"], [AdminAuthMiddleware::class]);

