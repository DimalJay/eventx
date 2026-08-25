<?php
namespace Controllers;

use Services\UserService;
use Models\User;
class UserController
{

    private UserService $userService;
    public function __construct()
    {
        $this->userService = new UserService();
    }
    public function listUsers()
    {
        $users = $this->userService->getAllUsers();
        return [
            "success" => true,
            "message" => "List of users",
            "data" => $users
        ];
    }
 
    public function getUserDetails()
    {
        $id = $_SERVER["uid"];
        $user = $this->userService->getUser($id);

        return [
            "success" => true,
            "message" => "User details for ID: " . $id,
            "data" => $user,
        ];
    }

    public function createUser()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $fName = $data['firstName'] ?? '';
        $lName = $data['lastName'] ?? '';


        // Basic validation
        if(empty($email) || empty($password) || empty($fName) || empty($lName)) {
            http_response_code(400);
            return [
                "success" => false,
                "message" => "All fields are required",
                "data" => null,
            ];
        }

        // Check if email already exists
        $ret = User::where(["email" => $email]);
        if(count($ret) > 0) {
            http_response_code(400);
            return [
                "success" => false,
                "message" => "Email already exists",
                "data" => null,
            ];
        } 

        $user = new User($email, $fName, $lName, $password, null);

        $response = $this->userService->createUser($user);
        return [
            "success" => true,
            "message" => "User created successfully",
            "data" => $response,
        ];
    }

    public function updateUser()
    {
        $id = $_SERVER["uid"];
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        if (empty($data)) {
            http_response_code(400);
            return [
                "success" => false,
                "message" => "No data provided",
                "data" => null,
            ];
        }

        $userData = [];

        if (!empty($data["firstName"])) {
            $userData["firstName"] = trim($data["firstName"]);
        }
        if (!empty($data["lastName"])) {
            $userData["lastName"] = trim($data["lastName"]);
        }
        if (!empty($data["phoneNumber"])) {
            $userData["phoneNumber"] = trim($data["phoneNumber"]);
        }
        if (!empty($data["profilePicture"])) {
            $userData["profilePicture"] = trim($data["profilePicture"]);
        }

        if (empty($userData)) {
            http_response_code(400);
            return [
                "success" => false,
                "message" => "No valid fields to update",
                "data" => null,
            ];
        }

        try {
            $this->userService->updateUser($id, $userData);
        } catch (\Throwable $th) {
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error updating user: " . $th->getMessage(),
                "data" => null,
            ];
        }

        $updatedUser = $this->userService->getUser($id);
        return [
            "success" => true,
            "message" => "User updated successfully",
            "data" => $updatedUser,
        ];
    }

    public function getUserRegistrations()
    {
        $userId = $_GET["userId"] ?? "";
        if (empty($userId)) {
            return [
                "success" => false,
                "message" => "User ID is required"
            ];
        }

        $registrations = \Models\Registration::where(["userId" => $userId]);

        foreach ($registrations as &$reg) {
            $events = \Models\Event::where(["id" => $reg["eventId"]]);
            if (count($events) > 0) {
                $event = $events[0];
                $reg["eventTitle"] = $event["title"];
                $reg["startDate"] = $event["startDate"];
                $reg["eventType"] = $event["eventType"];
            }
        }

        return [
            "success" => true,
            "message" => "List of registrations for user ID: " . $userId,
            "data" => $registrations
        ];
    }

    public function updateUserStatus()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $userId = $data['userId'] ?? null;
        $status = $data['status'] ?? null;

        if (empty($userId) || empty($status)) {
            http_response_code(400);
            return [
                "success" => false,
                "message" => "User ID and Status are required"
            ];
        }

        if (!in_array($status, ['active', 'suspended'])) {
            http_response_code(400);
            return [
                "success" => false,
                "message" => "Invalid status value"
            ];
        }

        try {
            $this->userService->updateUser($userId, ["accountStatus" => $status]);
            return [
                "success" => true,
                "message" => "User status updated successfully"
            ];
        } catch (\Throwable $th) {
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error updating status: " . $th->getMessage()
            ];
        }
    }
}

