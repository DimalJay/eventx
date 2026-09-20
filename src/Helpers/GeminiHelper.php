<?php

namespace Helpers;

class GeminiHelper
{
    public const LABELS = ['Positive', 'Negative', 'Neutral'];

    /**
     * Classify a feedback comment via the Gemini REST API.
     * Returns one of Positive|Negative|Neutral, or null when the API key is
     * unset, the request fails, or the model returns something unrecognized.
     */
    public static function analyzeSentiment(string $text, float $timeout = 4.0): ?string
    {
        $key = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY');
        if (empty($key)) {
            error_log("GeminiHelper: skipped sentiment analysis (GEMINI_API_KEY not configured)");
            return null;
        }

        $model = $_ENV['GEMINI_MODEL'] ?? getenv('GEMINI_MODEL') ?: 'gemini-3.6-flash';
        $prompt = "Classify the sentiment of the following event feedback comment as Positive, Negative, or Neutral. "
            . "If the comment is empty, off-topic, or unclear, answer Neutral. Reply with exactly one word only.\n\n"
            . "Comment: " . $text;

        $payload = json_encode([
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ],
            'generationConfig' => [
                'temperature' => 0,
                'maxOutputTokens' => 8,
                'thinkingConfig' => ['thinkingBudget' => 0],
            ],
        ]);

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/'
            . rawurlencode($model) . ':generateContent?key=' . rawurlencode($key);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_CONNECTTIMEOUT => (int) $timeout,
            CURLOPT_TIMEOUT => (int) $timeout,
        ]);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            error_log("GeminiHelper: request failed: " . $err);
            return null;
        }

        $decoded = json_decode($response, true);
        $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!is_string($text)) {
            error_log("GeminiHelper: unexpected response shape: " . substr($response, 0, 300));
            return null;
        }

        foreach (self::LABELS as $label) {
            if (preg_match('/\b' . preg_quote($label, '/') . '\b/i', $text)) {
                return $label;
            }
        }

        error_log("GeminiHelper: unrecognized label from model: " . trim($text));
        return null;
    }
}