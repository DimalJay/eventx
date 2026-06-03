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
        $json_data = file_get_contents("php://input");
        $data = json_decode($json_data, true);
        $user = new User(
            $data['email'],
            $data['first_name'],
            $data['last_name'],
            $data['password'],
            $data['profile_picture'] ?? null,
        );
        $response = $this->userService->create_user($user);
        return [
            "success" => true,
            "message" => "User created successfully",
            "data" => $response,
        ];
    }
}

