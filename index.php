<?php

require_once __DIR__ . '/vendor/autoload.php';

if (file_exists(__DIR__ . '/.env')) {
    Dotenv\Dotenv::createImmutable(__DIR__)->load();
}

date_default_timezone_set('Asia/Colombo');

set_exception_handler(function (\Throwable $e) {
    http_response_code(500);
    error_log("[EventX] Unhandled exception: " . $e->getMessage() . " @ " . $e->getFile() . ":" . $e->getLine());
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        "success" => false,
        "message" => "An unexpected error occurred."
    ]);
});

ini_set('display_errors', '0');
ini_set('log_errors', '1');

use Routes\Router;

$router = new Router("api/v1");

require_once __DIR__ . '/Routes/users.php';
require_once __DIR__ . '/Routes/auth.php';
require_once __DIR__ . '/Routes/feedbacks.php';
require_once __DIR__ . '/Routes/events.php';
require_once __DIR__ . '/Routes/tasks.php';
require_once __DIR__ . '/Routes/teamAccess.php';
require_once __DIR__ . '/Routes/registration.php';
require_once __DIR__ . '/Routes/admin.php';
require_once __DIR__ . '/Routes/cron.php';
require_once __DIR__ . '/Routes/payments.php';
require_once __DIR__ . '/Routes/notifications.php';

$router->dispatch();