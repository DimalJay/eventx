<?php

namespace Middlewares;
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Models\Admin;
use Helpers\Config;
use Exception;

class AdminAuthMiddleware
{
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
            $adminId = $decoded->data->id;

            // Verify if this ID exists in the admins table
            $admin = Admin::where(["id" => $adminId])[0] ?? null;
            if (!$admin) {
                http_response_code(403);
                echo json_encode([
                    "success" => false,
                    "message" => "Forbidden. Admin access required."
                ]);
                return false;
            }

            $_SERVER['uid'] = $adminId;
            return true;
        } catch (\Firebase\JWT\ExpiredException $e) {
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "message" => "Token expired. Please login again."
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
