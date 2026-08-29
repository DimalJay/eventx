<?php

namespace Controllers;

use Models\User;
use Services\AuthService;
use Helpers\EmailHelper;

class AuthController
{
    private $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    private function frontendHost(): string
    {
        return ($_ENV['FRONTEND_HOST'] ?? getenv('FRONTEND_HOST')) ?: 'http://localhost:3000';
    }

    private function generateSecureToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    // POST /auth/verify-email
    public function verifyEmail()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $token = $data['token'] ?? '';

        if (empty($token)) {
            http_response_code(400);
            return [
                'success' => false,
                'message' => 'Verification token is required',
                'data' => null,
            ];
        }

        $user = User::where(["verificationToken" => $token])[0] ?? null;
        if (!$user) {
            http_response_code(400);
            return [
                'success' => false,
                'message' => 'Invalid verification token',
                'data' => null,
            ];
        }

        $expires = $user['verificationTokenExpires'];
        if (empty($expires) || strtotime($expires) < time()) {
            http_response_code(400);
            return [
                'success' => false,
                'message' => 'Verification link has expired. Please request a new one.',
                'data' => null,
            ];
        }

        User::updateRecord(
            ["id" => $user['id']],
            [
                "isVerified" => "1",
                "verificationToken" => null,
                "verificationTokenExpires" => null,
            ]
        );

        return [
            'success' => true,
            'message' => 'Email verified successfully. You can now log in.',
            'data' => null,
        ];
    }

    // POST /auth/resend-verification
    public function resendVerification()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'] ?? '';

        $user = User::where(["email" => $email])[0] ?? null;
        if ($user && empty($user['isVerified'])) {
            $token = $this->generateSecureToken();
            $expires = date('Y-m-d H:i:s', time() + (24 * 60 * 60));
            User::updateRecord(
                ["id" => $user['id']],
                [
                    "verificationToken" => $token,
                    "verificationTokenExpires" => $expires,
                ]
            );

            $verifyLink = $this->frontendHost() . "/verify?token=" . urlencode($token) . "&email=" . urlencode($email);
            EmailHelper::sendWithTemplate(
                $email,
                "Verify your EventX account",
                "verification",
                [
                    "name" => $user['firstName'] . ' ' . $user['lastName'],
                    "verifyLink" => $verifyLink,
                ]
            );
        }

        return [
            'success' => true,
            'message' => 'If an account exists for that email, a verification link has been sent.',
            'data' => null,
        ];
    }

    // POST /auth/forgot-password
    public function forgotPassword()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'] ?? '';

        if (empty($email)) {
            http_response_code(400);
            return [
                'success' => false,
                'message' => 'Email is required',
                'data' => null,
            ];
        }

        $user = User::where(["email" => $email])[0] ?? null;
        if ($user) {
            $token = $this->generateSecureToken();
            $expires = date('Y-m-d H:i:s', time() + (60 * 60));
            User::updateRecord(
                ["id" => $user['id']],
                [
                    "resetToken" => $token,
                    "resetTokenExpires" => $expires,
                ]
            );

            $resetLink = $this->frontendHost() . "/reset-password?token=" . urlencode($token) . "&email=" . urlencode($email);
            EmailHelper::sendWithTemplate(
                $email,
                "Reset your EventX password",
                "password_reset",
                [
                    "name" => $user['firstName'] . ' ' . $user['lastName'],
                    "resetLink" => $resetLink,
                    "expiresIn" => "1 hour",
                ]
            );
        }

        return [
            'success' => true,
            'message' => 'If an account exists for that email, a password reset link has been sent.',
            'data' => null,
        ];
    }

    // POST /auth/reset-password
    public function resetPassword()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $token = $data['token'] ?? '';
        $email = $data['email'] ?? '';
        $newPassword = $data['newPassword'] ?? '';

        if (empty($token) || empty($email) || empty($newPassword)) {
            http_response_code(400);
            return [
                'success' => false,
                'message' => 'Token, email and new password are required',
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

        $user = User::where(["resetToken" => $token, "email" => $email])[0] ?? null;
        if (!$user) {
            http_response_code(400);
            return [
                'success' => false,
                'message' => 'Invalid password reset link',
                'data' => null,
            ];
        }

        $expires = $user['resetTokenExpires'];
        if (empty($expires) || strtotime($expires) < time()) {
            http_response_code(400);
            return [
                'success' => false,
                'message' => 'Password reset link has expired. Please request a new one.',
                'data' => null,
            ];
        }

        User::updateRecord(
            ["id" => $user['id']],
            [
                "password" => password_hash($newPassword, PASSWORD_DEFAULT),
                "resetToken" => null,
                "resetTokenExpires" => null,
            ]
        );

        return [
            'success' => true,
            'message' => 'Password reset successfully. You can now log in.',
            'data' => null,
        ];
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
        if ($response && isset($response['unverified'])) {
            http_response_code(403);
            return [
                "success" => false,
                "message" => "Please verify your email address before logging in. Check your inbox for the verification link.",
                "unverified" => true,
                "email" => $response['email']
            ];
        }
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
