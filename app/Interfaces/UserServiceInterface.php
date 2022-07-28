<?php

namespace App\Interfaces;

use App\Http\Requests\ChangeInfoRequest;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
    public function list();

    /**
     * getUserByURL gets a user by its URL.
     *
     * @param string $userURL
     *
     * @return Builder|Model
     *
     * @throws ModelNotFoundException
     */
    public function getUserByURL(string $userURL);

    /**
     * getNotifications gets notifications for the given user.
     *
     * @param string $userURL
     * @return array
     */
    public function getNotifications(string $userURL): array;

    /**
     * getProfile gets profile data for the given user.
     *
     * @param string $userURL
     */
    public function getProfile(string $userURL): array;

    /**
     * getRates gets rates data for the given user.
     *
     * @param string $userURL
     * @return array
     */
    public function getRates(string $userURL): array;

    /**
     * getRatesAjax gets rates data for the given user.
     *
     * @param string $userURL
     * @param string $sort
     * @return array
     */
    public function getRatesAjax(string $userURL, string $sort): array;

    /**
     * Get Comments get all comments for the given user.
     *
     * @param string $userURL
     * @param string $filter
     * @param string $order
     * @return array
     */
    public function getComments(string $userURL, string $filter, string $order): array;

    /**
     * GetCommentsAjax get all comments for the given user.
     *
     * @param string $userURL
     * @param string $action
     * @param string $filter
     * @param string $order
     * @return array
     */
    public function getCommentsAjax(string $userURL, string $action, string $filter, string $order): array;

    /**
     * Get Shows get all followed shows for the given user.
     *
     * @param string $userURL
     * @return array
     */
    public function getShows(string $userURL): array;

    /**
     * getRanking get all rankings for the given user.
     *
     * @param string $userURL
     * @return array
     */
    public function getRanking(string $userURL): array;

    /**
     * changePassword change the user password.
     *
     * @param Authenticatable|Model|User $user
     * @param string $oldPassword
     * @param string $newPassword
     * @return bool
     * @throws Exception
     */
    public function changePassword(Authenticatable $user, string $oldPassword, string $newPassword): bool;

    /**
     * changePassword change the user password.
     *
     * @param Authenticatable|Model|User $user
     * @param ChangeInfoRequest $request
     * @return void
     */
    public function changeInfo(Authenticatable $user, ChangeInfoRequest $request): void;
}
