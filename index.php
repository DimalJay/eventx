<?php

require_once __DIR__ . '/vendor/autoload.php';

use Routes\Router;

$router = new Router("api/v1");

require_once __DIR__ . '/Routes/users.php';
require_once __DIR__ . '/Routes/auth.php';
require_once __DIR__ . '/Routes/feedbacks.php';
require_once __DIR__ . '/Routes/events.php';
require_once __DIR__ . '/Routes/tasks.php';
require_once __DIR__ . '/Routes/teamAccess.php';

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$file = __DIR__ . $path;

if (file_exists($file) && !is_dir($file)) {
    return false; 
}

$_GET['request'] = ltrim($path, '/');
require 'index.php';

$router->dispatch();
