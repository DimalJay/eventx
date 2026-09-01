<?php
use Controllers\FeedbackController;
use Middlewares\AuthMiddleware;

$userController = new FeedbackController();

$router->post("/feedback", [$userController, "submitFeedback"]);
$router->get("/feedbacks", [$userController, "getFeedbacks"], [AuthMiddleware::class]);
$router->get("/feedback", [$userController, "getFeedback"]);
$router->get("/feedback/rate", [$userController, "rateFromEmail"]);
$router->put("/feedback/complete", [$userController, "completeFeedback"]);
$router->post("/feedback/send", [$userController, "sendFeedbackRequests"], [AuthMiddleware::class]);