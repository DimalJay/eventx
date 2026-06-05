<?php

namespace Services;

use Models\Feedback;

class FeedbackService
{
  public function __construct()
  {

  }
  public function submitFeedback(Feedback $feedback)
  {
    return $feedback->save();
  }
  public function getAllFeedbacks(int $eventId)
  {
    return Feedback::where(["eventId" => $eventId]);
  }
  public function getFeedbackById(int $feedbackId)
  {
    $feedbacks = Feedback::where(["id" => $feedbackId]);
    return count($feedbacks) > 0 ? $feedbacks[0] : null;
  }
}