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
        $users = $this->userService->get_all_users();
        return [
            "success" => true,
            "message" => "List of users",
            "data" => $users
        ];
    }

    public function getUserDetails()
    {
        $id = $_GET['id'] ?? null;
        return [
            "success" => true,
            "message" => "User details for ID: " . $id,
            "data" => null,
        ];
    }

    public function createUser()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $fName = $data['first_name'] ?? '';
        $lName = $data['last_name'] ?? '';


        // Basic validation
        if(empty($email) || empty($password) || empty($fName) || empty($lName)) {
            return [
                "success" => false,
                "message" => "All fields are required",
                "data" => null,
            ];
        }

        // Check if email already exists
        $ret = User::where(["email" => $email]);
        if(count($ret) > 0) {
            return [
                "success" => false,
                "message" => "Email already exists",
                "data" => null,
            ];
        } 

        $user = new User($email, $fName, $lName, $password, null);

        $response = $this->userService->create_user($user);
        return [
            "success" => true,
            "message" => "User created successfully",
            "data" => $response,
        ];
    }
}

