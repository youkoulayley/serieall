<?php

namespace App\Interfaces;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * UserRepositoryInterface implements all methods to interact with User.
 */
interface UserRepositoryInterface
{
    /**
     * Get User By URL.
     *
     * @param string $userURL
     * @return User
     */
    public function getByURL(string $userURL): User;

    /**
     * GetByURL returns the user based on the given URL with his published articles.
     * @param string $userURL
     *
     * @return Builder|Model
     */
    public function getByURLWithPublishedArticles(string $userURL);

    /**
     * List returns the list of all users.
     */
    public function list(): Collection;

    /**
     * Save save a user in database.
     *
     * @param User|Authenticatable $user
     * @return void
     */
    public function save($user);
}
