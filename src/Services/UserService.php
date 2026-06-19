<?php

namespace Services;

use Models\User;

class UserService
{
    public function __construct()
    {
    }

    public function getAllUsers()
    {
        $users = User::selectAll();
        return array_map(function ($user) {
            unset($user['password']);
            return $user;
        }, $users);
    }

    public function createUser(User $user)
    {
        return $user->save();
    }

    public function getUser(String $id)
    {
        $users = User::where(["id" => $id]);
        return count($users) > 0 ? $users[0] : null;
    }
}