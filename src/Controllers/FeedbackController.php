<?php

namespace Controllers;

use Services\FeedBackService;
use Models\FeedBack;

class FeedBackController
{

  private FeedBackService $feedBackService;
  public function __construct()
  {
    $this->feedBackService = new FeedBackService();
  }
  public function submitFeedback()
  {
    $data = json_decode(file_get_contents("php://input"), true);
    $eventId = $data['eventId'] ?? null;
    $participantId = $data['participantId'] ?? null;
    $organizationRating = $data['organizationRating'] ?? null;
    $contentRating = $data['contentRating'] ?? null;
    $experienceRating = $data['experienceRating'] ?? null;
    $comment = $data['comment'] ?? '';
    $sentiment = $data['sentiment'] ?? 'Pending';

    //Basic validation

    if (empty($eventId) || empty($participantId) || empty($organizationRating) || empty($contentRating) || empty($experienceRating)) {

      return [
        "success" => false,
        "message" => "All fields except comment are required",
        "data" => null,
      ];
    }

    //Rating must be between 1 and 5

    if ($organizationRating < 1 || $organizationRating > 5 || $contentRating < 1 || $contentRating > 5 || $experienceRating < 1 || $experienceRating > 5) {

      return [
        "success" => false,
        "message" => "Ratings must be between 1 and 5",
        "data" => null,
      ];
    }

    $feedback = new FeedBack($eventId, $participantId, $organizationRating, $contentRating, $experienceRating, $comment, $sentiment);

    $response = $this->feedBackService->submitFeedback($feedback);

    return [
      "success" => true,
      "message" => "Feedback submitted successfully.",
      "data" => $response
    ];
  }
  public function getAllFeedbacks()
  {
    $eventId = $_GET["eventId"];

    $feedbacks = $this->feedBackService->getAllFeedbacks($eventId);

    if (empty($feedbacks)) {

      return [
        "success" => false,
        "message" => "No feedback found for this event.",
        "data" => null,
      ];
    }

    return [
      "success" => true,
      "message" => "List of feedbacks for event ID: " . $eventId,
      "data" => $feedbacks,
    ];
  }
  public function getFeedbackById()
  {
    $feedbackId = $_GET["id"];

    $feedback = $this->feedBackService->getFeedbackById($feedbackId);

    if (!$feedback) {

      return [
        "success" => false,
        "message" => "Feedback not found for ID: " . $feedbackId,
        "data" => null,
      ];
    }

    return [
      "success" => true,
      "message" => "Feedback details for ID: " . $feedbackId,
      "data" => $feedback,
    ];
  }
}
