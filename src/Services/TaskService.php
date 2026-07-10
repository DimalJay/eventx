<?php

namespace Services;

use Models\Task;
use Models\User;

class TaskService
{

    public function addTask(Task $task)
    {
        $taskId = $task->save();

        $users = User::where(['id' => $task->getAssignedTo()]);
        $assignedUser = $users[0] ?? null;
        $name = $assignedUser ? trim($assignedUser['firstName'] . ' ' . $assignedUser['lastName']) : 'User';

        NotificationService::createNotification(
            $_SERVER['uid'],
            'New Task Assigned',
            "{$name}, you have been assigned a new task: {$task->getTitle()}",
            'task_assignment',
            ['taskId' => $taskId, 'eventId' => $task->getEventId()]
        );

        return $taskId;
    }

    public function updateTask(int $taskId, array $taskData)
    {
        if(empty($taskData)) return null;

        $tasks = Task::where(["id" => $taskId]);
        $task = $tasks[0] ?? null;

        $result = Task::updateRecord(["id" => $taskId], $taskData);

        if ($task && isset($taskData['status'])) {
            $users = User::where(['id' => $task['assignedTo']]);
            $assignedUser = $users[0] ?? null;
            $name = $assignedUser ? trim($assignedUser['firstName'] . ' ' . $assignedUser['lastName']) : 'User';

            NotificationService::createNotification(
                $_SERVER['uid'],
                'Task Status Updated',
                "{$name}, task '{$task['title']}' status changed to {$taskData['status']}",
                'task_status',
                ['taskId' => $taskId, 'status' => $taskData['status']]
            );
        }

        return $result;
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
