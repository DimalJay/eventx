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
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($username) || empty($password)) {
            return [
                'status' => 'error',
                'message' => 'Username and password are required'
            ];
        }

        $response = $this->authService->login($username, $password);
        return [
            "success" => true,
            "message" => "Login successful",
            "data" => $response
        ];
    }
}