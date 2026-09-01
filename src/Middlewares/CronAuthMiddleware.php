<?php

namespace Middlewares;

use Helpers\Config;

class CronAuthMiddleware
{
    public static function handle()
    {
        $secret = Config::get('CRON_SECRET');
        if ($secret === null || $secret === '') {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Server configuration error: CRON_SECRET not set."
            ]);
            return false;
        }

        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $provided = $headers['X-Cron-Secret'] ?? '';
        if (is_array($provided)) {
            $provided = implode('', $provided);
        }

        if (!hash_equals($secret, (string) $provided)) {
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "message" => "Unauthorized."
            ]);
            return false;
        }

        return true;
    }
}