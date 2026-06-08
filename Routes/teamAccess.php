<?php
use Controllers\TeamAccessController;

$teamAccessController = new TeamAccessController();

$router->get("/team-access", [$teamAccessController, "getMembers"]);
$router->post("/team-access", [$teamAccessController, "addMember"]);
$router->put("/team-access", [$teamAccessController, "updateMemberRole"]);
$router->delete("/team-access", [$teamAccessController, "removeMember"]);