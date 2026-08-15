<?php
use Controllers\FeedbackController;

$userController = new FeedbackController();

$router->post("/feedback", [$userController, "submitFeedback"]);
$router->get("/feedbacks", [$userController, "getFeedbacks"]);
$router->get("/feedback", [$userController, "getFeedback"]);
$router->get("/feedback/rate", [$userController, "rateFromEmail"]);
$router->put("/feedback/complete", [$userController, "completeFeedback"]);
$router->post("/feedback/send", [$userController, "sendFeedbackRequests"]);