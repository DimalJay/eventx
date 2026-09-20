<?php

namespace Contracts;

use Models\Task;

interface TaskServiceInterface
{
    public function addTask(Task $task);

    public function updateTask(int $taskId, array $taskData);

    public function deleteTask(int $taskId);

    public function getTasks(int $eventId): array;

    public function getTasksCreatedBy(int $userId): array;

    public function getTask(int $taskId): ?array;
}