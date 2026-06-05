<?php

namespace Services;

use Models\Task;

class TaskService
{

    public function addTask(Task $task)
    {
        return $task->save();
    }

    public function updateTask(int $taskId, array $taskData)
    {
        Task::updateRecord(["id" => $taskId], $taskData);
    }

    public function deleteTask(int $taskId)
    {
        return Task::deleteRecord(["id" => $taskId]);
    }

    public function getTasks(int $eventId)
    {
        return Task::where(["eventId" => $eventId]);
    }

    public function getTask(int $taskId)
    {
        $tasks = Task::where(["id" => $taskId]);
        return count($tasks) > 0 ? $tasks[0] : null;
    }
}
