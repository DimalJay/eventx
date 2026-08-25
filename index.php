<?php

require_once __DIR__ . '/vendor/autoload.php';

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

$router->dispatch();