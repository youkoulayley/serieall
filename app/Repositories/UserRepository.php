<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class UserRepository.
 */
class UserRepository implements UserRepositoryInterface
{
    protected User $user;

    /**
     * UserRepository constructor.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get by Username.
     *
     * @param $username
     */
    public function getByUsername($username): User
    {
        return $this->user::where('username', $username)->firstOrFail();
    }

    /**
     * Get By URL.
     *
     * @param $user_url
     *
     * @return Builder|Model
     *
     * @throws ModelNotFoundException
     */
    public function getByURL($user_url)
    {
        return $this->user::with(['articles' => function ($q) {
            $q->where('state', '=', 1);
            $q->orderBy('published_at', 'desc')->paginate(2);
        }])->where('user_url', $user_url)->firstOrFail();
    }

    /**
     * List.
     */
    public function list(): Collection
    {
        return $this->user::orderBy('username')->get();
    }

    /**
     * Get User By ID.
     *
     * @param $id
     *
     * @return \Illuminate\Database\Eloquent\Collection|Model
     *
     * @throws ModelNotFoundException
     */
    public function getByID($id): User
    {
        return $this->user->findOrFail($id);
    }

    /**
     * Get planning for user.
     *
     * @param $user_id
     * @param $state
     *
     * @return mixed
     */
    public function getEpisodePlanning($user_id, $state)
    {
        return $this->user->join('show_user', 'users.id', '=', 'show_user.user_id')
            ->join('shows', 'show_user.show_id', '=', 'shows.id')
            ->join('seasons', 'shows.id', '=', 'seasons.show_id')
            ->join('episodes', 'seasons.id', '=', 'episodes.season_id')
            ->where('users.id', '=', $user_id)
            ->where('show_user.state', '=', $state)
            ->whereBetween('episodes.diffusion_us', [
                Carbon::now()->subMonth(1),
                Carbon::now()->addMonth(1), ])
            ->select(DB::raw('shows.name as show_name, seasons.name as season_name, episodes.name as episode_name, episodes.id, episodes.numero, episodes.diffusion_us, shows.show_url'))
            ->get();
    }
}
