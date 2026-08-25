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

    public function adminLogin()
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

        $response = $this->authService->adminLogin($email, $password);
        if ($response) {
            http_response_code(200);
            return [
                "success" => true,
                "message" => "Login successful",
                "data" => $response
            ];
        }
        http_response_code(401);
        return [
            "success" => false,
            "message" => "Invalid email or password"
        ];
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
            http_response_code(200);
            return [
                "success" => true,
                "message" => "Login successful",
                "data" => $response
            ];
        }
        http_response_code(401);
        return [
            "success" => false,
            "message" => "Invalid email or password"
        ];
    }

    // logout function
    public function logout()
    {
        $this->authService->logout();
        return [
            "success" => true,
            "message" => "User Logged out"
        ];
    }

    public function googleLogin()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $credential = $data['credential'] ?? '';

        if (empty($credential)) {
            http_response_code(400);
            return [
                'success' => false,
                'message' => 'Credential token is required'
            ];
        }

        $response = $this->authService->googleLogin($credential);
        if ($response) {
            http_response_code(200);
            return [
                "success" => true,
                "message" => "Login successful",
                "data" => $response
            ];
        }
        http_response_code(400);
        return [
            "success" => false,
            "message" => "Google authentication failed"
        ];
    }
}
