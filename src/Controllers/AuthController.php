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

    public function changePassword()
    {
        $id = $_SERVER["uid"];
        $data = json_decode(file_get_contents("php://input"), true);
        $currentPassword = $data['currentPassword'] ?? '';
        $newPassword = $data['newPassword'] ?? '';

        if (empty($currentPassword) || empty($newPassword)) {
            http_response_code(400);
            return [
                'success' => false,
                'message' => 'Current and new password are required',
                'data' => null,
            ];
        }

        if (strlen($newPassword) < 8) {
            http_response_code(400);
            return [
                'success' => false,
                'message' => 'New password must be at least 8 characters',
                'data' => null,
            ];
        }

        $user = \Models\User::where(["id" => $id])[0] ?? null;
        if (!$user) {
            http_response_code(404);
            return [
                'success' => false,
                'message' => 'User not found',
                'data' => null,
            ];
        }

        if (!password_verify($currentPassword, $user['password'])) {
            http_response_code(400);
            return [
                'success' => false,
                'message' => 'Current password is incorrect',
                'data' => null,
            ];
        }

        \Models\User::updateRecord(
            ["id" => $id],
            ["password" => password_hash($newPassword, PASSWORD_DEFAULT)]
        );

        return [
            'success' => true,
            'message' => 'Password updated successfully',
            'data' => null,
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
