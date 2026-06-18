<?php

require_once __DIR__ . '/vendor/autoload.php';

use Routes\Router;
use Dotenv\Dotenv;

$router = new Router("api/v1");
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

require_once __DIR__ . '/Routes/users.php';
require_once __DIR__ . '/Routes/auth.php';
require_once __DIR__ . '/Routes/feedbacks.php';
require_once __DIR__ . '/Routes/events.php';
require_once __DIR__ . '/Routes/tasks.php';
require_once __DIR__ . '/Routes/teamAccess.php';

$router->dispatch();
?>