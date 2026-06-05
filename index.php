<?php

require_once __DIR__ . '/vendor/autoload.php';

use Routes\Router;

$router = new Router("api/v1");

require_once __DIR__ . '/routes/users.php';
require_once __DIR__ . '/routes/auth.php';
require_once __DIR__ . '/routes/tasks.php';
require_once __DIR__ . '/routes/teamAccess.php';

$router->dispatch();
?>