<?php

namespace Controllers;

use Services\FeedbackService;
use Services\RegistrationService;
use Services\UserService;
use Services\EventService;
use Helpers\EmailHelper;
use Models\FeedBack;

class FeedbackController
{

  private FeedbackService $feedBackService;
  public function __construct()
  {
    $this->feedBackService = new FeedbackService();
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

  public function rateFromEmail()
  {
    $eventId = $_GET['eventId'] ?? '';
    $participantId = $_GET['participantId'] ?? '';
    $rating = (int)($_GET['rating'] ?? 0);
    $token = $_GET['token'] ?? '';

    if (empty($eventId) || empty($participantId) || empty($rating) || empty($token)) {
      header("Content-Type: application/json");
      echo json_encode(["success" => false, "message" => "Missing required fields"]);
      exit;
    }

    // Validate stateless token
    $secretKey = ($_ENV['APP_SECRET'] ?? getenv('APP_SECRET')) ?: 'secret_key_123';
    $expectedToken = hash_hmac('sha256', $eventId . '-' . $participantId, $secretKey);

    if (!hash_equals($expectedToken, $token)) {
      header("Content-Type: application/json");
      echo json_encode(["success" => false, "message" => "Invalid security token"]);
      exit;
    }

    // Check if feedback already exists
    $feedbacks = FeedBack::where(["eventId" => $eventId, "participantId" => $participantId]);
    
    $sentiment = 'Pending';

    if (count($feedbacks) === 0) {
      $feedback = new FeedBack($eventId, $participantId, 0, 0, $rating, '', $sentiment);
      $this->feedBackService->submitFeedback($feedback);
    } else {
      FeedBack::updateRecord(
        ["eventId" => $eventId, "participantId" => $participantId],
        ["experienceRating" => $rating, "sentiment" => $sentiment]
      );
    }

    // Redirect to Next.js landing page
    $domain = $_ENV['DOMAIN'] ?? getenv('DOMAIN') ?? 'localhost';
    $redirectUrl = "http://" . $domain . ":3000/feedback?eventId=" . $eventId . "&participantId=" . $participantId . "&rating=" . $rating . "&token=" . $token;
    header("Location: " . $redirectUrl);
    exit;
  }

  public function completeFeedback()
  {
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);

    $eventId = $data['eventId'] ?? '';
    $participantId = $data['participantId'] ?? '';
    $organizationRating = (int)($data['organizationRating'] ?? 0);
    $contentRating = (int)($data['contentRating'] ?? 0);
    $comment = trim($data['comment'] ?? '');
    $token = $data['token'] ?? '';

    if (empty($eventId) || empty($participantId) || empty($token)) {
      return [
        "success" => false,
        "message" => "Missing required fields"
      ];
    }

    // Validate stateless token
    $secretKey = ($_ENV['APP_SECRET'] ?? getenv('APP_SECRET')) ?: 'secret_key_123';
    $expectedToken = hash_hmac('sha256', $eventId . '-' . $participantId, $secretKey);

    if (!hash_equals($expectedToken, $token)) {
      return [
        "success" => false,
        "message" => "Invalid security token"
      ];
    }

    // Update the existing feedback record
    $updateData = [
      "organizationRating" => $organizationRating,
      "contentRating" => $contentRating,
      "comment" => $comment
    ];

    FeedBack::updateRecord(
      ["eventId" => $eventId, "participantId" => $participantId],
      $updateData
    );

    return [
      "success" => true,
      "message" => "Feedback completed successfully"
    ];
  }

  public function sendFeedbackRequests()
  {
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);
    $eventId = $data['eventId'] ?? '';

    if (empty($eventId)) {
      return [
        "success" => false,
        "message" => "Event ID is required"
      ];
    }

    $registrationService = new RegistrationService();
    $userService = new UserService();
    $eventService = new EventService();

    $event = $eventService->getEvent($eventId);
    if (!$event) {
      return [
        "success" => false,
        "message" => "Event not found"
      ];
    }

    $registrations = $registrationService->getRegistrationsList($eventId);
    $sentCount = 0;
    
    $domain = $_ENV['DOMAIN'] ?? getenv('DOMAIN') ?? 'localhost';
    $secretKey = ($_ENV['APP_SECRET'] ?? getenv('APP_SECRET')) ?: 'secret_key_123';
    
    // Base API URL for rate links
    $baseUrl = "http://" . $domain . "/eventx/api/v1/feedback/rate";

    foreach ($registrations as $reg) {
      if ($reg['status'] === 'GOING') {
        $user = $userService->getUser($reg['userId']);
        if ($user) {
          $participantId = $user['id'];
          // Generate token
          $token = hash_hmac('sha256', $eventId . '-' . $participantId, $secretKey);
          
          // Generate rating links
          $ratingLinks = [];
          for ($i = 1; $i <= 5; $i++) {
              $ratingLinks["rate" . $i] = $baseUrl . "?eventId=" . $eventId . "&participantId=" . $participantId . "&rating=" . $i . "&token=" . $token;
          }

          EmailHelper::sendWithTemplate($user['email'], "We'd love your feedback on " . $event["title"], "feedback_request", [
              "firstName" => $user["firstName"],
              "lastName" => $user["lastName"],
              "eventTitle" => $event["title"],
              "rate1" => $ratingLinks["rate1"],
              "rate2" => $ratingLinks["rate2"],
              "rate3" => $ratingLinks["rate3"],
              "rate4" => $ratingLinks["rate4"],
              "rate5" => $ratingLinks["rate5"],
          ]);
          $sentCount++;
          
          // Add small delay to prevent SMTP server overload
          usleep(300000); // 0.3s
        }
      }
    }

    return [
      "success" => true,
      "message" => "Feedback requests sent successfully",
      "data" => [
        "emailsSent" => $sentCount
      ]
    ];
  }
}

