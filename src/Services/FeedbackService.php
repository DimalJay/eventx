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

  public function getFeedbacks(int $eventId)
  {
    return Feedback::where(["eventId" => $eventId]);
  }

  public function getFeedback(int $feedbackId)
  {
    $feedbacks = Feedback::where(["id" => $feedbackId]);
    return count($feedbacks) > 0 ? $feedbacks[0] : null;
  }
}