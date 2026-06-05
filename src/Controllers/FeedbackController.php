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
    $comment = trim($data['comment']) ?? '';
    $sentiment = trim($data['sentiment']) ?? 'Pending';

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
  public function getFeedbacks()
  {
    $eventId = $_GET["eventId"];

    if (empty($eventId)) {

      return [
        "success" => false,
        "message" => "Event ID is required to fetch feedbacks.",
        "data" => null,
      ];
    }

    $feedbacks = $this->feedBackService->getFeedbacks($eventId);

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
  public function getFeedback()
  {
    $id = $_GET["id"] ?? null;

    if (empty($id)) {

      return [
        "success" => false,
        "message" => "Feedback ID is required to fetch feedback details.",
        "data" => null,
      ];
    }

    $feedback = $this->feedBackService->getFeedback($id);

    if (!$feedback) {

      return [
        "success" => false,
        "message" => "Feedback not found for ID: " . $id,
        "data" => null,
      ];
    }

    return [
      "success" => true,
      "message" => "Feedback details for ID: " . $id,
      "data" => $feedback,
    ];
  }
}
