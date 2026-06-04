<?php

namespace Middlewares;
require "vendor/autoload.php";
use Firebase\JWT\JWT;


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
}