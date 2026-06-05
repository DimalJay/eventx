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
            setcookie("auth_token", $jwt, time() + (60 * 60 * 24), "/", "", false, true);
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
}