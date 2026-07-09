<?php
use Controllers\TeamAccessController;
use Middlewares\AuthMiddleware;

$teamAccessController = new TeamAccessController();

$router->get("/team-access", [$teamAccessController, "getMembers"], [AuthMiddleware::class]);
$router->post("/team-access", [$teamAccessController, "addMember"], [AuthMiddleware::class]);
$router->put("/team-access", [$teamAccessController, "updateMemberRole"], [AuthMiddleware::class]);
$router->delete("/team-access", [$teamAccessController, "removeMember"], [AuthMiddleware::class]);