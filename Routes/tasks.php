<?php

use Controllers\TaskController; 
use Middlewares\AuthMiddleware;
$taskController = new TaskController();

$router->post('/task', [$taskController, 'addTask'], [AuthMiddleware::class]  ); 
$router->put('/task', [$taskController, 'updateTask'], [AuthMiddleware::class]);
$router->put('/task/status', [$taskController, 'updateTaskStatus'], [AuthMiddleware::class]);
$router->delete('/task', [$taskController, 'deleteTask'], [AuthMiddleware::class]);
$router->delete('/tasks/{id}', [$taskController, 'deleteTask'], [AuthMiddleware::class]);
$router->get('/tasks', [$taskController, 'getTasks'], [AuthMiddleware::class]);
$router->get('/task', [$taskController, 'getTask'], [AuthMiddleware::class]);
$router->get('/tasks/{id}', [$taskController, 'getTask'], [AuthMiddleware::class]);