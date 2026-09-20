<?php

namespace Services;

use Helpers\GeminiHelper;
use Models\Feedback;

class SentimentService
{
    /**
     * Analyze a single comment, returning a validated label or 'Pending'
     * when analysis cannot be performed.
     */
    public function classifyComment(string $comment): string
    {
        $label = GeminiHelper::analyzeSentiment($comment);
        return in_array($label, GeminiHelper::LABELS, true) ? $label : 'Pending';
    }

    /**
     * Backfill job: analyze feedback comments that have not been classified
     * yet. Returns a summary of scanned/updated counts.
     */
    public function analyzePendingFeedbacks(int $limit = 50): array
    {
        $rows = Feedback::query(
            'SELECT id, comment FROM feedbacks
             WHERE sentiment = :pending
               AND comment IS NOT NULL AND comment <> ""
             ORDER BY createdAt DESC
             LIMIT ' . max(1, (int) $limit),
            ['pending' => 'Pending']
        );

        $scanned = 0;
        $updated = 0;
        foreach ($rows as $row) {
            $label = $this->classifyComment((string) $row['comment']);
            $scanned++;
            if ($label !== 'Pending') {
                Feedback::updateRecord(['id' => (int) $row['id']], ['sentiment' => $label]);
                $updated++;
            }
            usleep(200000);
        }

        return ['scanned' => $scanned, 'updated' => $updated];
    }
}