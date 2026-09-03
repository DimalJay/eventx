<?php

namespace Controllers;

require_once dirname(__DIR__, 2) . '/database/Database.php';

use Models\Task;
use Models\TeamAccess;
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
        $id = (int) ($_SERVER["uid"] ?? 0);

        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        $eventId = $data["eventId"] ?? null;
        $title = isset($data["title"]) ? trim($data["title"]) : "";
        $assignedTo = $data["assignedTo"] ?? null;
        $assignedBy = $data["assignedBy"] ?? null;
        $dueDate = $data["dueDate"] ?? null;

        if (empty($eventId) || $title === "" || $assignedTo === null || $assignedTo === "" || $assignedBy === null || $assignedBy === "" || empty($dueDate)) {
            http_response_code(400);
            return [
                "success" => false,
                "message" => "Missing required fields"
            ];
        }

        if (!$this->canManage((int) $eventId)) {
            http_response_code(403);
            return [
                "success" => false,
                "message" => "Unauthorized: You do not have access to this event"
            ];
        }

        $description = isset($data["description"]) ? trim($data["description"]) : "";

        $task = new Task(
            (int) $eventId, 
            $title,
            $description, 
            $id, 
            (int) $assignedTo, 
            (int) $assignedBy, 
            $dueDate
        );
        try {
            $taskId = $this->taskService->addTask($task);

            $savedTask = $this->taskService->getTask((int) $taskId);
            $event = $this->eventService->getEvent((string) $eventId);
            $eventTitle = $event["title"] ?? "your event";
            if ($savedTask) {
                // Resolve recipient user ID for notification:
                // If assignedTo is 0, the recipient is the organizer
                // If assignedTo matches a team_access ID, resolve to that member's userId
                $recipientUserId = (int) $assignedTo;
                if ($recipientUserId === 0 && !empty($event['organizerId'])) {
                    $recipientUserId = (int) $event['organizerId'];
                } else {
                    $member = TeamAccess::where(["id" => $recipientUserId]);
                    if (count($member) > 0 && !empty($member[0]['userId'])) {
                        $recipientUserId = (int) $member[0]['userId'];
                    }
                }

                $this->notificationService->notifyTaskAssigned(
                    array_merge($savedTask, ['assignedTo' => $recipientUserId]),
                    $eventTitle
                );
            }
        } catch (\Throwable $e) {
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error creating task: " . $e->getMessage()
            ];
        }

        return [
            "success" => true,
            "message" => "Task created successfully",
            "data" => $savedTask ?? null
        ];
    }

    public function updateTask() // Logic to edit a task
    {
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        $id = $data["id"] ?? null;

        if ($id === null || $id === "") {
            http_response_code(400);
            return [
                "success" => false,
                "message" => "Task ID is required"
            ];
        }

        $existing = $this->taskService->getTask((int) $id);
        if (!$existing || !$this->canManage((int) $existing["eventId"])) {
            http_response_code(403);
            return [
                "success" => false,
                "message" => "Unauthorized: You do not have access to this task"
            ];
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
                $this->notificationService->notifyTaskUpdated($tsk);
            }
            return [
                "success" => true,
                "message" => "Task updated successfully",
                "data" => $tsk
            ];
        } catch (\Throwable $th) {
            http_response_code(500);
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

        $id = $data["id"] ?? null;
        $status = isset($data["status"]) ? trim($data["status"]) : "";

        if ($id === null || $id === "" || $status === "") {
            http_response_code(400);
            return [
                "success" => false,
                "message" => "Task ID and status are required"
            ];
        }

        $existing = $this->taskService->getTask((int) $id);
        if (!$existing || !$this->canManage((int) $existing["eventId"])) {
            http_response_code(403);
            return [
                "success" => false,
                "message" => "Unauthorized: You do not have access to this task"
            ];
        }

        try {
            $this->taskService->updateTask((int) $id, ["status" => $status]);
            $tsk = $this->taskService->getTask((int) $id);
            if ($tsk) {
                $this->notificationService->notifyTaskUpdated($tsk);
            }
            return [
                "success" => true,
                "message" => "Task status updated successfully",
                "data" => null
            ];
        } catch (\Throwable $th) {
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error updating task status: " . $th->getMessage()
            ];
        }
    }

    public function deleteTask($params = []) // Logic to delete a task
    {
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true) ?? [];

        $id = $params["id"] ?? $data["id"] ?? $_GET["id"] ?? "";
        $id = trim($id);
        if ($id === "") {
            http_response_code(400);
            return [
                "success" => false,
                "message" => "Task ID is required"
            ];
        }

        $existing = $this->taskService->getTask((int) $id);
        if (!$existing || !$this->canManage((int) $existing["eventId"])) {
            http_response_code(403);
            return [
                "success" => false,
                "message" => "Unauthorized: You do not have access to this task"
            ];
        }

        try {
            $ret = $this->taskService->deleteTask((int) $id);
            if ($ret > 0) {
                return [
                    "success" => true,
                    "message" => "Task deleted successfully",
                    "data" => null
                ];
            } else {
                http_response_code(404);
                return [
                    "success" => false,
                    "message" => "Task not found"
                ];
            }
        } catch (\Throwable $th) {
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error deleting task: " . $th->getMessage()
            ];
        }
    }

    public function getTasks() // Logic to view all tasks
    {
        $userId = (int) ($_SERVER["uid"] ?? 0);
        $eventId = isset($_GET["eventId"]) ? trim($_GET["eventId"]) : "";

        $user = $this->userService->getUser($userId);
        if (!$user) {
            http_response_code(401);
            return [
                "success" => false,
                "message" => "Unauthorized: User not found"
            ];
        }

        // If no eventId specified, return all tasks created by this user
        if (empty($eventId)) {
            try {
                $createdTasks = Task::where(["createdBy" => $userId]);
                return [
                    "success" => true,
                    "message" => "Tasks retrieved successfully",
                    "data" => $createdTasks
                ];
            } catch (\Throwable $th) {
                http_response_code(500);
                return [
                    "success" => false,
                    "message" => "Error retrieving tasks: " . $th->getMessage(),
                    "data" => null
                ];
            }
        }

        try {
            if (!$this->canManage((int) $eventId)) {
                http_response_code(403);
                return [
                    "success" => false,
                    "message" => "Unauthorized: You do not have access to this event"
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
            $tasks = $this->taskService->getTasks((int) $eventId);
            return [
                "success" => true,
                "message" => "Tasks retrieved successfully",
                "data" => $tasks
            ];
        } catch (\Throwable $th) {
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error retrieving tasks: " . $th->getMessage(),
                "data" => null
            ];
        }
    }

    public function getTask($params = []) // Logic to view a single task
    {
        $id = $params["id"] ?? $_GET["id"] ?? "";
        $id = trim($id);
        if ($id === "") {
            http_response_code(400);
            return [
                "success" => false,
                "message" => "Task ID is required"
            ];
        }

        $task = $this->taskService->getTask((int) $id);
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
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error retrieving task: " . $th->getMessage(),
                "data" => null
            ];
        }
    }
}
