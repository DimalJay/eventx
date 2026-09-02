<?php

namespace Controllers;

use Models\Task;
use Services\TaskService;
use Services\EventService;
use Services\UserService;
use Services\TeamAccessService;
use Services\NotificationService;
use Exception;

class TaskController
{

    private TaskService $taskService;
    private EventService $eventService;
    private UserService $userService;
    private TeamAccessService $teamAccessService;
    private NotificationService $notificationService;
    public function __construct()
    {
        $this->taskService = new TaskService();
        $this->eventService = new EventService();
        $this->userService = new UserService();
        $this->teamAccessService = new TeamAccessService();
        $this->notificationService = new NotificationService();
    }

    private function canManage(int $eventId): bool
    {
        $userId = (int) ($_SERVER["uid"] ?? 0);
        return $this->teamAccessService->hasTeamAccess($userId, $eventId);
    }

    public function addTask() // Logic to add a task
    {
        $id = $_SERVER["uid"];

        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        if (empty($data["eventId"]) || empty($data["title"]) || empty($data["assignedTo"]) || empty($data["assignedBy"]) || empty($data["dueDate"])) {
            return [
                "success" => false,
                "message" => "Missing required fields"
            ];
        }

        if (!$this->canManage((int) $data["eventId"])) {
            http_response_code(403);
            return [
                "success" => false,
                "message" => "Unauthorized: You do not have access to this event"
            ];
        }

        $task = new Task(
            $data["eventId"], 
            trim($data["title"]) ?? "",
            trim($data["description"]) ?? "", 
            $id, 
            $data["assignedTo"], 
            $data["assignedBy"], 
            $data["dueDate"]
            );
        try {
            $taskId = $this->taskService->addTask($task);

            $savedTask = $this->taskService->getTask((int) $taskId);
            $event = $this->eventService->getEvent($eventId);
            $eventTitle = $event["title"] ?? "your event";
            if ($savedTask) {
                $this->notificationService->notifyTaskAssigned($savedTask, $eventTitle);
            }
        } catch (Exception $e) {
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error creating task: " . $e->getMessage()
            ];
        }

        return [
            "success" => true,
            "message" => "Task created succesfully",
            "data" => null
        ];
    }

    public function updateTask() // Logic to edit a task
    {
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        $id = $data["id"];

        if (empty($id)) {
            return [
                "success" => false,
                "message" => "Task ID is required"
            ];
        }

        $existing = $this->taskService->getTask($id);
        if (!$existing || !$this->canManage((int) $existing["eventId"])) {
            http_response_code(403);
            return [
                "success" => false,
                "message" => "Unauthorized: You do not have access to this task"
            ];
        }
        

        $taskData = [];
        if (!empty($data["title"])) {
            $taskData["title"] = $data["title"];
        }
        if (!empty($data["description"])) {
            $taskData["description"] = $data["description"];
        }
        if (!empty($data["dueDate"])) {
            $taskData["dueDate"] = $data["dueDate"];
        }
        if (!empty($data["status"])) {
            $taskData["status"] = $data["status"];
        }

        try {
            $this->taskService->updateTask($id, $taskData);
            $tsk = $this->taskService->getTask($id);
            if ($tsk) {
                $this->notificationService->notifyTaskUpdated($tsk);
            }
            return [
                "success" => true,
                "message" => "Task updated successfully",
                "data" => $tsk
            ];
        } catch (\Throwable $th) {
            return [
                "success" => false,
                "message" => "Error updating task: " . $th->getMessage()
            ];
        }

        
    }

    public function updateTaskStatus() // Logic to update task status
    {
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        $id = $data["id"];
        $status = $data["status"];

        if (empty($id) || empty($status)) {
            return [
                "success" => false,
                "message" => "Task ID and status are required"
            ];
        }

        $existing = $this->taskService->getTask($id);
        if (!$existing || !$this->canManage((int) $existing["eventId"])) {
            http_response_code(403);
            return [
                "success" => false,
                "message" => "Unauthorized: You do not have access to this task"
            ];
        }

        try {
            $this->taskService->updateTask($id, ["status" => $status]);
            $tsk = $this->taskService->getTask($id);
            if ($tsk) {
                $this->notificationService->notifyTaskUpdated($tsk);
            }
            return [
                "success" => true,
                "message" => "Task status updated successfully",
                "data" => null
            ];
        } catch (\Throwable $th) {
            return [
                "success" => false,
                "message" => "Error updating task status: " . $th->getMessage()
            ];
        }
    }

    public function deleteTask() // Logic to delete a task
    {
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        $id = trim($data["id"]) ?? "";
        if (empty($id)) {
            return [
                "success" => false,
                "message" => "Task ID is required"
            ];
        }

        $existing = $this->taskService->getTask($id);
        if (!$existing || !$this->canManage((int) $existing["eventId"])) {
            http_response_code(403);
            return [
                "success" => false,
                "message" => "Unauthorized: You do not have access to this task"
            ];
        }

        try {
            $ret = $this->taskService->deleteTask($id);
            if ($ret > 0) {
                return [
                    "success" => true,
                    "message" => "Task deleted successfully",
                    "data" => null
                ];
            } else {
                http_response_code(404);
                throw new Exception("Task not found");
            }
        } catch (\Throwable $th) {
            return [
                "success" => false,
                "message" => "Error deleting task: " . $th->getMessage()
            ];
        }
    }

    public function getTasks() // Logic to view all tasks
    {
        $userId = $_SERVER["uid"];
        $eventId = $_GET["eventId"] ?? 0;

        $user = $this->userService->getUser($userId);
        if (!$user) {
            http_response_code(401);
            return [
                "success" => false,
                "message" => "Unauthorized: User not found"
            ];
        }

        try {
            if (!$this->canManage((int) $eventId)) {
                http_response_code(404);
                return [
                    "success" => false,
                    "message" => "Event not found"
                ];
            }
        } catch (\Throwable $th) {
            http_response_code(404);
            return [
                "success" => false,
                "message" => "Event not found"
            ];
        }

        try {
            $tasks = $this->taskService->getTasks($eventId);
            return [
                "success" => true,
                "message" => "Tasks retrieved successfully",
                "data" => $tasks
            ];
        } catch (\Throwable $th) {
            return [
                "success" => false,
                "message" => "Error retrieving tasks: " . $th->getMessage(),
                "data" => null
            ];
        }
    }

    public function getTask() // Logic to view a single task
    {
        $id = trim($_GET["id"]) ?? "";
        if (empty($id)) {
            return [
                "success" => false,
                "message" => "Task ID is required"
            ];
        }

        $task = $this->taskService->getTask($id);
        if (!$task) {
            http_response_code(404);
            return [
                "success" => false,
                "message" => "Task not found",
                "data" => null,
            ];
        }

        if (!$this->canManage((int) $task["eventId"])) {
            http_response_code(403);
            return [
                "success" => false,
                "message" => "Unauthorized: You do not have access to this task",
                "data" => null,
            ];
        }

        try {
            return [
                "success" => true,
                "message" => "Task retrieved successfully",
                "data" => $task
            ];
        } catch (\Throwable $th) {
            return [
                "success" => false,
                "message" => "Error retrieving task: " . $th->getMessage(),
                "data" => null
            ];
        }
    }
}
