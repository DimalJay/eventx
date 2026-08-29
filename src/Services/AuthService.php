<?php

namespace Services;

use Models\User;
use Models\Admin;
use Middlewares\AuthMiddleware;

class AuthService
{
    public function adminLogin($email, $password)
    {
        $admin = Admin::where(["email" => $email])[0] ?? null;
        if ($admin && password_verify($password, $admin['password'])) {
            $jwt = AuthMiddleware::generateToken($admin['id']);
            setcookie("auth_token", $jwt, [
                "expires" => time() + (60 * 60 * 24),
                "path" => "/",
                "domain" => getenv('DOMAIN'),
                "secure" => true,
                "httponly" => true,
                "samesite" => "Lax"
            ]);

            return [
                "token" => $jwt,
                "user" => [
                    "id" => $admin['id'],
                    "email" => $admin['email'],
                    "firstName" => $admin['firstName'],
                    "lastName" => $admin['lastName']
                ]
            ];
        }
        return null;
    }

    public function login($email, $password)
    {
        $user = User::where(["email" => $email])[0] ?? null;
        if ($user && password_verify($password, $user['password'])) {
            if (empty($user['isVerified'])) {
                return [
                    "success" => false,
                    "unverified" => true,
                    "email" => $user['email']
                ];
            }

            $jwt = AuthMiddleware::generateToken($user['id']);
            setcookie("auth_token", $jwt, [
                "expires" => time() + (60 * 60 * 24),
                "path" => "/",
                "domain" => getenv('DOMAIN'),
                "secure" => true,
                "httponly" => true,
                "samesite" => "Lax"
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
            "samesite" => "Lax"
        ]);
    }

    public function googleLogin($credential)
    {
        $url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . urlencode($credential);
        $response = @file_get_contents($url);
        if (!$response) {
            return null;
        }

        $payload = json_decode($response, true);
        if (!isset($payload['email'])) {
            return null;
        }

        $email = $payload['email'];
        $firstName = $payload['given_name'] ?? 'Google';
        $lastName = $payload['family_name'] ?? 'User';
        $profilePicture = $payload['picture'] ?? null;

        $user = User::where(["email" => $email])[0] ?? null;
        if (!$user) {
            $randomPassword = bin2hex(random_bytes(16));
            $userObj = new User($email, $firstName, $lastName, $randomPassword, $profilePicture, 'google');
            $newId = $userObj->save();
            User::updateRecord(["id" => $newId], ["isVerified" => "1"]);
            $user = User::where(["email" => $email])[0] ?? null;
        }

        if ($user) {
            $jwt = AuthMiddleware::generateToken($user['id']);
            setcookie("auth_token", $jwt, [
                "expires" => time() + (60 * 60 * 24),
                "path" => "/",
                "domain" => getenv('DOMAIN'),
                "secure" => true,
                "httponly" => true,
                "samesite" => "Lax"
            ]);

            return [
                "token" => $jwt,
                "user" => [
                    "id" => $user['id'],
                    "email" => $user['email'],
                    "firstName" => $user['firstName'],
                    "lastName" => $user['lastName'],
                    "loginType" => $user['loginType'] ?? 'google',
                    "profilePicture" => $user['profilePicture'] ?? null
                ]
            ];
        }
        return null;
    }
}
