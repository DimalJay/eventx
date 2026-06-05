<?php
use Controllers\FeedbackController;

$userController = new FeedbackController();

$router->post("/feedback", [$userController, "submitFeedback"]);
$router->get("/feedbacks", [$userController, "getAllFeedbacks"]);
$router->get("/feedback", [$userController, "getFeedbackById"]);