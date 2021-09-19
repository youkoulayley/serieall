<?php

namespace App\Interfaces;

use App\Models\User;
use Illuminate\Support\Collection;

/**
 * UserServiceInterface implements all methods to handle the logic of Users.
 */
interface UserServiceInterface
{
    /**
     * index return a list of users.
     *
     * @return mixed
     */
    public function listUsers(): Collection;

    /**
     * getUserByURL gets a user by its URL.
     *
     * @param $userURL
     * @return User
     */
    public function getUserByURL($userURL): User;
}
