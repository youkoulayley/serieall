<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class UserRepository.
 */
class UserRepository implements UserRepositoryInterface
{
    private const limitLastPublishedArticles = 2;

    /**
     * Get User By ID.
     *
     * @param $id
     *
     * @return User
     */
    public function getByID($id): User
    {
        return User::findOrFail($id);
    }

    /**
     * Get User By URL.
     *
     * @param string $userURL
     * @return User
     */
    public function getByURL(string $userURL): User
    {
        return User::where('user_url', $userURL)->firstOrFail();
    }

    /**
     * Get by Username.
     *
     * @param string $username
     * @return User
     */
    public function getByUsername(string $username): User
    {
        return User::where('username', $username)->firstOrFail();
    }

    /**
     * GetByURL returns the user based on the given URL with his published articles.
     *
     * @param $userURL
     *
     * @return Builder|Model
     */
    public function getByURLWithPublishedArticles(string $userURL)
    {
        return User::with(['articles' => function ($q) {
            $q->where('state', '=', config('articles.published'));
            $q->orderBy('published_at', 'desc')->paginate(self::limitLastPublishedArticles);
        }])->where('user_url', $userURL)->firstOrFail();
    }

    /**
     * List returns the list of all users.
     */
    public function list(): Collection
    {
        return User::orderBy('username')->get();
    }

    /**
     * GetEpisodePlanning returns the list of the diffused episode in the previous month and on the coming month.
     *
     * @param $userID
     * @param $state
     * @return Collection
     */
    public function getEpisodePlanning($userID, $state): Collection
    {
        return User::join('show_user', 'users.id', '=', 'show_user.user_id')
            ->join('shows', 'show_user.show_id', '=', 'shows.id')
            ->join('seasons', 'shows.id', '=', 'seasons.show_id')
            ->join('episodes', 'seasons.id', '=', 'episodes.season_id')
            ->where('users.id', '=', $userID)
            ->where('show_user.state', '=', $state)
            ->whereBetween('episodes.diffusion_us', [
                Carbon::now()->subMonth(),
                Carbon::now()->addMonth(),
            ])
            ->select(DB::raw('shows.name as show_name, seasons.name as season_name, episodes.name as episode_name, episodes.id, episodes.numero, episodes.diffusion_us, shows.show_url'))
            ->get();
    }

    /**
     * Save save a user in database.
     *
     * @param User|Authenticatable|Model $user
     * @return void
     * @throws Exception
     */
    public function save($user)
    {
        $ok = $user->save();
        if ($ok) {
            return;
        }

        throw new Exception('unable to save user');
    }
}
