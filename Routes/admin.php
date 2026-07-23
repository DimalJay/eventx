<?php

use Controllers\AdminController;

$adminController = new AdminController();

$router->get("/admin/dashboard-stats", [$adminController, "getDashboardStats"]);
