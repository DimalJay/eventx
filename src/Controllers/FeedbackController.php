<?php

namespace Controllers;

use Services\FeedbackService;
use Services\RegistrationService;
use Services\UserService;
use Services\EventService;
use Helpers\EmailHelper;
use Helpers\Config;
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

    $teamAccessService = new \Services\TeamAccessService();
    $canManage = $teamAccessService->hasTeamAccess((int) ($_SERVER["uid"] ?? 0), (int) $eventId);
    if (!$canManage) {
      http_response_code(403);
      return [
        "success" => false,
        "message" => "Unauthorized: You do not have access to this event",
        "data" => null,
      ];
    }

    $feedbacks = \Models\Feedback::query(
      "SELECT f.*, u.firstName, u.lastName, u.email
       FROM feedbacks f
       JOIN users u ON f.participantId = u.id
       WHERE f.eventId = :eventId
       ORDER BY f.createdAt DESC",
      ["eventId" => $eventId]
    );

    if (empty($feedbacks)) {

      return [
        "success" => true,
        "message" => "No feedback found for this event.",
        "data" => [],
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
    $secretKey = Config::requireSecret('APP_SECRET');
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

    // Redirect to Next.js feedback page
    $redirectUrl = EmailHelper::frontendUrl() . "/feedback?eventId=" . $eventId . "&participantId=" . $participantId . "&rating=" . $rating . "&token=" . $token;
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
    $experienceRating = (int)($data['experienceRating'] ?? 0);
    $comment = trim($data['comment'] ?? '');
    $token = $data['token'] ?? '';

    if (empty($eventId) || empty($participantId) || empty($token)) {
      return [
        "success" => false,
        "message" => "Missing required fields"
      ];
    }

    if ($organizationRating < 1 || $organizationRating > 5 || $contentRating < 1 || $contentRating > 5) {
      return [
        "success" => false,
        "message" => "Ratings must be between 1 and 5"
      ];
    }

    // Validate stateless token
    $secretKey = Config::requireSecret('APP_SECRET');
    $expectedToken = hash_hmac('sha256', $eventId . '-' . $participantId, $secretKey);

    if (!hash_equals($expectedToken, $token)) {
      return [
        "success" => false,
        "message" => "Invalid security token"
      ];
    }

    $existing = FeedBack::where(["eventId" => $eventId, "participantId" => $participantId]);

    if (count($existing) === 0) {
      $feedback = new FeedBack($eventId, $participantId, $organizationRating, $contentRating, $experienceRating > 0 ? $experienceRating : 0, $comment, 'Pending');
      $this->feedBackService->submitFeedback($feedback);
    } else {
      $updateData = [
        "organizationRating" => $organizationRating,
        "contentRating" => $contentRating,
        "comment" => $comment
      ];
      if ($experienceRating > 0) {
        $updateData["experienceRating"] = $experienceRating;
      }

      FeedBack::updateRecord(
        ["eventId" => $eventId, "participantId" => $participantId],
        $updateData
      );
    }

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
    $teamAccessService = new \Services\TeamAccessService();

    $event = $eventService->getEvent($eventId);
    if (!$event) {
      return [
        "success" => false,
        "message" => "Event not found"
      ];
    }

    $canManage = $teamAccessService->hasTeamAccess((int) ($_SERVER["uid"] ?? 0), (int) $eventId);
    if (!$canManage) {
      http_response_code(403);
      return [
        "success" => false,
        "message" => "Unauthorized: You do not have access to this event"
      ];
    }

    $registrations = $registrationService->getRegistrationsList($eventId);
    $sentCount = 0;

    $secretKey = Config::requireSecret('APP_SECRET');

    // Base URL for the website feedback form
    $baseUrl = EmailHelper::frontendUrl() . "/feedback";

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

