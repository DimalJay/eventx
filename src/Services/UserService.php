<?php

namespace Services;

use Models\User;

class UserService
{
    public function __construct()
    {
    }

    public function get_all_users()
    {
        return User::selectAll();
    }

    public function create_user(User $user)
    {
        return $user->save();
    }

    public function get_user(String $id)
    {
        $users = User::where(["id" => $id]);
        return count($users) > 0 ? $users[0] : null;
    }
}