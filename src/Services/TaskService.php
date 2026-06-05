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

    public function getTasks()
    {
        // Logic to view all tasks
    }

    public function getTask($taskId)
    {
        // Logic to view a single task
    }
}
