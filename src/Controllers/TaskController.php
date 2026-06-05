<?php

namespace Controllers;

use Models\Task;
use Services\TaskService;
use Exception;

class TaskController
{

    private $taskService;
    public function __construct()
    {
        $this->taskService = new TaskService();
    }

    public function addTask() // Logic to add a task
    {
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        $eventId = $data["eventId"] ?? "";
        $title = $data["title"] ?? "";
        $description = $data["description"] ?? "";
        $createdBy = $data["createdBy"] ?? "";
        $assignedTo = $data["assignedTo"] ?? "";
        $assignedBy = $data["assignedBy"] ?? "";
        $dueDate = $data["dueDate"] ?? "";

        if (empty($eventId) || empty($title) || empty($createdBy) || empty($assignedTo) || empty($assignedBy) || empty($dueDate)) {
            return [
                "success" => false,
                "message" => "Missing required fields"
            ];
        }

        $task = new Task($eventId, $title, $description, $createdBy, $assignedTo, $assignedBy, $dueDate);
        try {
            $this->taskService->addTask($task);
        } catch (Exception $e) {
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

        $id = $data["id"] ?? "";
        $title = $data["title"] ?? "";
        $description = $data["description"] ?? "";
        $dueDate = $data["dueDate"] ?? "";
        $status = $data["status"] ?? "";

        if (empty($id)) {
            return [
                "success" => false,
                "message" => "Task ID is required"
            ];
        }

        $taskData = [];
        if (!empty($title)) {
            $taskData["title"] = $title;
        }
        if (!empty($description)) {
            $taskData["description"] = $description;
        }
        if (!empty($dueDate)) {
            $taskData["dueDate"] = $dueDate;
        }
        if (!empty($status)) {
            $taskData["status"] = $status;
        }

        try {
            $this->taskService->updateTask($id, $taskData);
        } catch (\Throwable $th) {
            return [
                "success" => false,
                "message" => "Error updating task: " . $th->getMessage()
            ];
        }

        return [
            "success" => true,
            "message" => "Task updated successfully",
            "data" => null
        ];
    }

    public function deleteTask() // Logic to delete a task
    {
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        $id = $data["id"] ?? "";
        if (empty($id)) {
            return [
                "success" => false,
                "message" => "Task ID is required"
            ];
        }
        try {
            $ret = $this->taskService->deleteTask($id);
            echo count($ret);
            if ($ret) {
                return [
                    "success" => true,
                    "message" => "Task deleted successfully",
                    "data" => $ret
                ];
            } else {
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
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        $id = $data["id"] ?? "";
        
        
    }

    public function getTask() // Logic to view a single task
    {
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);
    }
}
