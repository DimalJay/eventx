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
}