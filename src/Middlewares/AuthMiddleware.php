<?php

namespace Middlewares;
require "vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class AuthMiddleware
{
    public static function generateToken($user_id)
    {
        $secretKey = "your_secret_key_is_not_short_please_change_it";
        $payload = array(
            "iss" => "http://localhost",
            "iat" => time(),
            "exp" => time() + (60 * 60 * 24),
            "data" => array(
                "id" => $user_id,
            )
        );
        return JWT::encode($payload, $secretKey, 'HS256');
    }

    public static function handle()
    {
        if (!isset($_COOKIE['auth_token'])) {
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "message" => "Access denied. Please login first."
            ]);
            return false;
        }

        $token = $_COOKIE['auth_token'];
        $secretKey = "your_secret_key_is_not_short_please_change_it";

        try {
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
            $_SERVER['uid'] = $decoded->data->id;
            return true;
        } catch (\Firebase\JWT\ExpiredException $e) {
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "message" => "Token expired. Please login again."
            ]);
            return false;
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "message" => "Invalid token. Please login again."
            ]);
            return false;
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "message" => "Authentication error. Please login again."
            ]);
            return false;
        }
        
    }
}