<?php

use Controllers\TaskController; 
$taskController = new TaskController();

$router->post('/task', [$taskController, 'addTask']); 
$router->put('/task', [$taskController, 'updateTask']);
$router->delete('/task', [$taskController, 'deleteTask']);
