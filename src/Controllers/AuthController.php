<?php

namespace Controllers;

use Services\AuthService;

class AuthController
{
    private $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function login()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Email and password are required'
            ];
        }

        $response = $this->authService->login($email, $password);
        if ($response) {
            return [
                "success" => true,
                "message" => "Login successful",
                "data" => $response
            ];
        }
        return [
            "success" => false,
            "message" => "Invalid email or password"
        ];
    }
}