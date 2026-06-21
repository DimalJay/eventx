<?php

namespace Controllers;

use Models\Task;
use Services\TaskService;
use Services\EventService;
use Services\UserService;
use Exception;

class TaskController
{

    private TaskService $taskService;
    private EventService $eventService;
    private UserService $userService;
    public function __construct()
    {
        $this->taskService = new TaskService();
        $this->eventService = new EventService();
        $this->userService = new UserService();
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
            $this->taskService->addTask($task);
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

        try {
            $this->taskService->updateTask($id, ["status" => $status]);
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

        $event = $this->eventService->getEventWithUserId($userId, $eventId);
        if (!$event) {
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
        try {
            $task = $this->taskService->getTask($id);
            if ($task) {
                return [
                    "success" => true,
                    "message" => "Task retrieved successfully",
                    "data" => $task
                ];
            } else {
                throw new Exception("Task not found");
            }
        } catch (\Throwable $th) {
            return [
                "success" => false,
                "message" => "Error retrieving task: " . $th->getMessage(),
                "data" => null
            ];
        }
    }
}
