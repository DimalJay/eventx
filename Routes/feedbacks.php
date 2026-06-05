<?php
use Controllers\FeedbackController;

$userController = new FeedbackController();

$router->post("/feedback", [$userController, "submitFeedback"]);
$router->get("/feedbacks", [$userController, "getFeedbacks"]);
$router->get("/feedback", [$userController, "getFeedback"]);