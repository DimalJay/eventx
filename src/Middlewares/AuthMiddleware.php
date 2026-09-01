<?php

namespace Middlewares;
require "vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Helpers\Config;
use Exception;

class AuthMiddleware
{
    public static function generateToken($user_id)
    {
        $secretKey = Config::requireSecret('JWT_SECRET');
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
        $secretKey = Config::requireSecret('JWT_SECRET');

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