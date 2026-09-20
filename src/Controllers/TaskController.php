<?php

namespace Controllers;

use Contracts\TaskAuthorizationInterface;
use Contracts\TaskServiceInterface;
use Services\TaskService;
use Services\TaskAuthorizationService;
use Services\UserService;
use Services\NotificationService;
use Helpers\APIResponse;
use Models\Task;

class TaskController
{
    private TaskServiceInterface $taskService;
    private TaskAuthorizationInterface $authorization;
    private UserService $userService;
    private NotificationService $notificationService;

    public function __construct(
        ?TaskServiceInterface $taskService = null,
        ?TaskAuthorizationInterface $authorization = null,
        ?UserService $userService = null,
        ?NotificationService $notificationService = null
    ) {
        $this->taskService = $taskService ?? new TaskService();
        $this->authorization = $authorization ?? new TaskAuthorizationService();
        $this->userService = $userService ?? new UserService();
        $this->notificationService = $notificationService ?? new NotificationService();
    }

    private function currentUserId(): int
    {
        return (int) ($_SERVER["uid"] ?? 0);
    }

    private function parseJsonInput(): array
    {
        $json = file_get_contents('php://input');
        return json_decode($json, true) ?? [];
    }

    public function addTask()
    {
        $userId = $this->currentUserId();
        $data = $this->parseJsonInput();

        $eventId = $data["eventId"] ?? null;
        $title = isset($data["title"]) ? trim($data["title"]) : "";
        $assignedTo = $data["assignedTo"] ?? null;
        $assignedBy = $data["assignedBy"] ?? null;
        $dueDate = $data["dueDate"] ?? null;

        if (empty($eventId) || $title === "" || $assignedTo === null || $assignedTo === "" || $assignedBy === null || $assignedBy === "" || empty($dueDate)) {
            return APIResponse::error("Missing required fields");
        }

        if (!$this->authorization->canManageEvent($userId, (int) $eventId)) {
            return APIResponse::error("Unauthorized: You do not have access to this event", 403);
        }

        $description = isset($data["description"]) ? trim($data["description"]) : "";

        $task = new Task(
            (int) $eventId,
            $title,
            $description,
            $userId,
            (int) $assignedTo,
            (int) $assignedBy,
            $dueDate
        );

        try {
            $taskId = $this->taskService->addTask($task);
            $savedTask = $this->taskService->getTask((int) $taskId);
            if ($savedTask) {
                $this->notificationService->notifyTaskAssigned($savedTask);
            }
        } catch (\Throwable $e) {
            return APIResponse::error("Error creating task: " . $e->getMessage(), 500);
        }

        return APIResponse::success("Task created successfully", $savedTask ?? null);
    }

    public function updateTask()
    {
        $userId = $this->currentUserId();
        $data = $this->parseJsonInput();

        $id = $data["id"] ?? null;

        if ($id === null || $id === "") {
            return APIResponse::error("Task ID is required");
        }

        $existing = $this->taskService->getTask((int) $id);
        if (!$existing || !$this->authorization->canManageEvent($userId, (int) $existing["eventId"])) {
            return APIResponse::error("Unauthorized: You do not have access to this task", 403);
        }

        if (!$this->authorization->canUpdateTask($userId, $existing)) {
            return APIResponse::error("Unauthorized: You can only edit tasks assigned to you", 403);
        }

        $taskData = [];
        if (isset($data["title"]) && trim($data["title"]) !== "") {
            $taskData["title"] = trim($data["title"]);
        }
        if (isset($data["description"])) {
            $taskData["description"] = trim($data["description"]);
        }
        if (!empty($data["dueDate"])) {
            $taskData["dueDate"] = $data["dueDate"];
        }
        if (!empty($data["status"])) {
            $taskData["status"] = $data["status"];
        }
        if (isset($data["assignedTo"]) && $data["assignedTo"] !== "") {
            $taskData["assignedTo"] = (int) $data["assignedTo"];
        }
        if (isset($data["assignedBy"]) && $data["assignedBy"] !== "") {
            $taskData["assignedBy"] = (int) $data["assignedBy"];
        }

        try {
            $this->taskService->updateTask((int) $id, $taskData);
            $tsk = $this->taskService->getTask((int) $id);
            if ($tsk) {
                $this->notificationService->notifyTaskUpdated($tsk, $taskData["status"] ?? null, $userId);
            }
            return APIResponse::success("Task updated successfully", $tsk);
        } catch (\Throwable $th) {
            return APIResponse::error("Error updating task: " . $th->getMessage(), 500);
        }
    }

    public function updateTaskStatus()
    {
        $userId = $this->currentUserId();
        $data = $this->parseJsonInput();

        $id = $data["id"] ?? null;
        $status = isset($data["status"]) ? trim($data["status"]) : "";

        if ($id === null || $id === "" || $status === "") {
            return APIResponse::error("Task ID and status are required");
        }

        $existing = $this->taskService->getTask((int) $id);
        if (!$existing || !$this->authorization->canManageEvent($userId, (int) $existing["eventId"])) {
            return APIResponse::error("Unauthorized: You do not have access to this task", 403);
        }

        if (!$this->authorization->canUpdateTask($userId, $existing)) {
            return APIResponse::error("Unauthorized: You can only update the status of tasks assigned to you", 403);
        }

        try {
            $this->taskService->updateTask((int) $id, ["status" => $status]);
            $tsk = $this->taskService->getTask((int) $id);
            if ($tsk) {
                $this->notificationService->notifyTaskUpdated($tsk, $status, $userId);
            }
            return APIResponse::success("Task status updated successfully");
        } catch (\Throwable $th) {
            return APIResponse::error("Error updating task status: " . $th->getMessage(), 500);
        }
    }

    public function deleteTask($params = [])
    {
        $data = $this->parseJsonInput();

        $id = $params["id"] ?? $data["id"] ?? $_GET["id"] ?? "";
        $id = trim((string) $id);
        if ($id === "") {
            return APIResponse::error("Task ID is required");
        }

        $existing = $this->taskService->getTask((int) $id);
        if (!$existing || !$this->authorization->canManageEvent($this->currentUserId(), (int) $existing["eventId"])) {
            return APIResponse::error("Unauthorized: You do not have access to this task", 403);
        }

        try {
            $ret = $this->taskService->deleteTask((int) $id);
            if ($ret > 0) {
                return APIResponse::success("Task deleted successfully");
            }
            return APIResponse::error("Task not found", 404);
        } catch (\Throwable $th) {
            return APIResponse::error("Error deleting task: " . $th->getMessage(), 500);
        }
    }

    public function getTasks()
    {
        $userId = $this->currentUserId();
        $eventId = isset($_GET["eventId"]) ? trim($_GET["eventId"]) : "";

        $user = $this->userService->getUser($userId);
        if (!$user) {
            return APIResponse::error("Unauthorized: User not found", 401);
        }

        if (empty($eventId)) {
            try {
                $createdTasks = $this->taskService->getTasksCreatedBy($userId);
                return APIResponse::success("Tasks retrieved successfully", $createdTasks);
            } catch (\Throwable $th) {
                return APIResponse::error("Error retrieving tasks: " . $th->getMessage(), 500);
            }
        }

        try {
            if (!$this->authorization->canManageEvent($userId, (int) $eventId)) {
                return APIResponse::error("Unauthorized: You do not have access to this event", 403);
            }
        } catch (\Throwable $th) {
            return APIResponse::error("Event not found", 404);
        }

        try {
            $tasks = $this->taskService->getTasks((int) $eventId);
            return APIResponse::success("Tasks retrieved successfully", $tasks);
        } catch (\Throwable $th) {
            return APIResponse::error("Error retrieving tasks: " . $th->getMessage(), 500);
        }
    }

    public function getTask($params = [])
    {
        $id = $params["id"] ?? $_GET["id"] ?? "";
        $id = trim((string) $id);
        if ($id === "") {
            return APIResponse::error("Task ID is required");
        }

        $task = $this->taskService->getTask((int) $id);
        if (!$task) {
            return APIResponse::error("Task not found", 404);
        }

        if (!$this->authorization->canManageEvent($this->currentUserId(), (int) $task["eventId"])) {
            return APIResponse::error("Unauthorized: You do not have access to this task", 403);
        }

        return APIResponse::success("Task retrieved successfully", $task);
    }
}