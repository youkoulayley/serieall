<?php

namespace App\Interfaces;

use App\Models\User;
use Illuminate\Support\Collection;

/**
 * UserRepositoryInterface implements all methods to interact with User.
 */
interface UserRepositoryInterface
{
    /**
     * @param $id
     */
    public function getByID($id): User;

    /**
     * @param $user_url
     *
     * @return mixed
     */
    public function getByURL($user_url);

    /**
     * @param $username
     * @return mixed
     */
    public function getByUsername($username);

    public function list(): Collection;

    /**
     * @param $user_id
     * @param $state
     *
     * @return mixed
     */
    public function getEpisodePlanning($user_id, $state);
}
