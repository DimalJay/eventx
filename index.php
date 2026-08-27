<?php

require_once __DIR__ . '/vendor/autoload.php';

if (file_exists(__DIR__ . '/.env')) {
    Dotenv\Dotenv::createImmutable(__DIR__)->load();
}

date_default_timezone_set('Asia/Colombo');

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

$router->dispatch();