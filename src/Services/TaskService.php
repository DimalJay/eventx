<?php

namespace Services;

use Models\Task;
use Models\Event;
use Exception;

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
        $ret = Event::where(["id" => $eventId]);
        if (count($ret) < 1) {
            throw new Exception("Event does not exist");
        }
        return Task::where(["eventId" => $eventId]);
    }

    public function getTask(int $taskId)
    {
        $tasks = Task::where(["id" => $taskId]);
        return count($tasks) > 0 ? $tasks[0] : null;
    }
}
