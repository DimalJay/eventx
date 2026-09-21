<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$root = dirname(__DIR__);

if (file_exists($root . '/.env.test')) {
    Dotenv::createImmutable($root, '.env.test')->safeLoad();
}