<?php

namespace Helpers;

class APIResponse
{
    public static function success(string $message = "Success", $data = null): array
    {
        http_response_code(200);
        return ["success" => true, "message" => $message, "data" => $data];
    }

    public static function error(string $message, int $code = 400, $data = null): array
    {
        http_response_code($code);
        return ["success" => false, "message" => $message, "data" => $data];
    }
}
