<?php

namespace Services;

use Contracts\TaskServiceInterface;
use Models\Task;

class TaskService implements TaskServiceInterface
{

    public function addTask(Task $task)
    {
        return $task->save();
    }

    public function updateTask(int $taskId, array $taskData)
    {
        if (empty($taskData)) return null;
        return Task::updateRecord(["id" => $taskId], $taskData);
    }

    public function deleteTask(int $taskId)
    {
        return Task::deleteRecord(["id" => $taskId]);
    }

    public function getTasks(int $eventId): array
    {
        return Task::where(["eventId" => $eventId]);
    }

    public function getTasksCreatedBy(int $userId): array
    {
        return Task::where(["createdBy" => $userId]);
    }

    public function getTask(int $taskId): ?array
    {
        $tasks = Task::where(["id" => $taskId]);
        return count($tasks) > 0 ? $tasks[0] : null;
    }
}