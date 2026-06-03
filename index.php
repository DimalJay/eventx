<?php

require_once __DIR__ . '/vendor/autoload.php';

use Routes\Router;

$router = new Router("api/v1");

require_once __DIR__ . '/routes/users.php';

$router->dispatch();
?>