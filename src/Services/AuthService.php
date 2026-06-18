<?php

namespace Services;

use Models\User;
use Middlewares\AuthMiddleware;

class AuthService
{
    public function login($email, $password)
    {
        $user = User::where(["email" => $email])[0] ?? null;
        if ($user && password_verify($password, $user['password'])) {
            $jwt = AuthMiddleware::generateToken($user['id']);
            setcookie("auth_token", $jwt, [
                "expires" => time() + (60 * 60 * 24),
                "path" => "/",
                "domain" => "eventx-mega.vercel.app",
                "secure" => true,
                "httponly" => false,
                "samesite" => "None"
            ]);
            return [
                "token" => $jwt,
                "user" => [
                    "id" => $user['id'],
                    "email" => $user['email'],
                    "firstName" => $user['firstName'],
                    "lastName" => $user['lastName']
                ]
            ];
        }
        return null;
    }

    public function logout() {
        setcookie("auth_token", "", [
            "expires" => time() - 3600,
            "path" => "/",
            "secure" => true,
            "httponly" => true,
            "samesite" => "None"
        ]);
    }
}