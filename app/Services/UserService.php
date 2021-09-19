<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\UserServiceInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

/**
 * UserService class.
 * @property UserRepositoryInterface $userRepository
 */
class UserService implements UserServiceInterface
{
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * listUsers return a list of users.
     *
     * @return Collection
     */
    public function listUsers(): Collection
    {
        return $this->userRepository->list();
    }

    /**
     * getUserByURL gets a user by its URL.
     *
     * @param $userURL
     * @return User
     * @throws ModelNotFoundException
     */
    public function getUserByURL($userURL): User
    {
        return $this->userRepository->getByURL($userURL);
    }
}
