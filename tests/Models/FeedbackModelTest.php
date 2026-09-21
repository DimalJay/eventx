<?php

namespace Tests\Models;

use DateTime;
use Models\Feedback;
use Tests\TestCase;

class FeedbackModelTest extends TestCase
{
    public function testConstructorStoresFields(): void
    {
        $feedback = new Feedback(1, 2, 5, 4, 3, 'Great event', 'Positive');

        $this->assertSame(1, $this->prop($feedback, 'eventId'));
        $this->assertSame(2, $this->prop($feedback, 'participantId'));
        $this->assertSame(5, $this->prop($feedback, 'organizationRating'));
        $this->assertSame(4, $this->prop($feedback, 'contentRating'));
        $this->assertSame(3, $this->prop($feedback, 'experienceRating'));
        $this->assertSame('Great event', $this->prop($feedback, 'comment'));
        $this->assertSame('Positive', $this->prop($feedback, 'sentiment'));
    }

    public function testSentimentDefaultsToPending(): void
    {
        $feedback = new Feedback(1, 2, 5, 5, 5);
        $this->assertSame('Pending', $this->prop($feedback, 'sentiment'));
        $this->assertNull($this->prop($feedback, 'comment'));
    }

    public function testConstructorInitialisesTimestamps(): void
    {
        $feedback = new Feedback(1, 2, 5, 5, 5);

        $this->assertInstanceOf(DateTime::class, $this->prop($feedback, 'createdAt'));
        $this->assertInstanceOf(DateTime::class, $this->prop($feedback, 'updatedAt'));
    }

    public function testEmptyReturnsInstance(): void
    {
        $this->assertInstanceOf(Feedback::class, Feedback::empty());
    }
}