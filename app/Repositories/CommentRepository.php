<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\CommentRepositoryInterface;
use App\Models\Comment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/**
 * Class CommentRepository.
 */
class CommentRepository implements CommentRepositoryInterface
{
    public const THUMB_SHOW_CACHE_KEY = 'THUMB_SHOW_CACHE_KEY';

    private const paginateProfileComments = 4;

    /**
     * CommentRepository constructor.
     */
    public function __construct()
    {
    }

    /**
     * getCountCommentsByUserIDAndThumb returns the summary of comments by counting the number of
     * comments by thumb for the given user.
     *
     * @param int $userID
     * @return Collection
     */
    public function getCountCommentsByUserIDAndThumb(int $userID): Collection
    {
        return Comment::where('user_id', '=', $userID)
            ->whereNotNull('thumb')
            ->select(['thumb', DB::raw('count(*) as total')])
            ->groupBy('thumb')
            ->get();
    }

    /**
     * getCommentsOnShowByUserID gets all comments a user ever made on shows.
     *
     * @param int $userID
     * @param string $pageName
     * @param array $filter
     * @param string $order
     *
     * @return LengthAwarePaginator
     */
    public function getCommentsOnShowByUserID(int $userID, string $pageName, array $filter, string $order): LengthAwarePaginator
    {
        return Comment::with('children')->whereHas('user', function ($q) use ($userID) {
            $q->where('id', '=', $userID);
        })
            ->join('shows', 'comments.commentable_id', '=', 'shows.id')
            ->whereNull('parent_id')
            ->whereNotNull('thumb')
            ->whereNotNull('commentable_id')
            ->whereCommentableType('App\Models\Show')
            ->whereIn('thumb', $filter)
            ->orderBy($order)
            ->paginate(self::paginateProfileComments, ['*'], $pageName);
    }

    /**
     * getCommentsOnSeasonByUserID gets all comments a user ever made on seasons.
     *
     * @param int $userID
     * @param string $pageName
     * @param array $filter
     * @param string $order
     *
     * @return LengthAwarePaginator
     */
    public function getCommentsOnSeasonByUserID(int $userID, string $pageName, array $filter, string $order): LengthAwarePaginator
    {
        return Comment::with('children')->whereHas('user', function ($q) use ($userID) {
            $q->where('id', '=', $userID);
        })
            ->join('seasons', 'comments.commentable_id', '=', 'seasons.id')
            ->join('shows', 'seasons.show_id', '=', 'shows.id')
            ->whereCommentableType('App\Models\Season')
            ->whereNull('parent_id')
            ->whereNotNull('thumb')
            ->whereNotNull('commentable_id')
            ->whereIn('thumb', $filter)
            ->orderBy($order)
            ->paginate(self::paginateProfileComments, ['*'], $pageName);
    }

    /**
     * getCommentsOnEpisodeByUserID gets all comments a user ever made on episodes.
     *
     * @param int $userID
     * @param string $pageName
     * @param array $filter
     * @param string $order
     *
     * @return LengthAwarePaginator
     */
    public function getCommentsOnEpisodeByUserID(int $userID, string $pageName, array $filter, string $order): LengthAwarePaginator
    {
        return Comment::with('children')->whereHas('user', function ($q) use ($userID) {
            $q->where('id', '=', $userID);
        })
            ->join('episodes', 'comments.commentable_id', '=', 'episodes.id')
            ->join('seasons', 'episodes.season_id', '=', 'seasons.id')
            ->join('shows', 'seasons.show_id', '=', 'shows.id')
            ->whereCommentableType('App\Models\Episode')
            ->whereNull('parent_id')
            ->whereNotNull('thumb')
            ->whereNotNull('commentable_id')
            ->whereIn('thumb', $filter)
            ->orderBy($order)
            ->paginate(self::paginateProfileComments, ['*'], $pageName);
    }

    // ******************
    // TODO
    // ******************

    /**
     * Get Comment by User, Type Comment, And Type ID.
     *
     * @param $user_id
     * @param $type
     * @param $type_id
     *
     * @return Comment
     *
     * @internal param $userID
     * @internal param $typeID
     */
    public function getCommentByUserIDTypeTypeID($user_id, $type, $type_id)
    {
        return Comment::where('commentable_id', '=', $type_id)
            ->with(['children' => function ($q) {
                $q->with('user');
            }])
            ->where('user_id', '=', $user_id)
            ->where('commentable_type', '=', $type)
            ->first();
    }

    /**
     * Get Last Two Comments by Type Comment and Type ID.
     *
     * @param $type
     * @param $type_id
     * @param $user_comment_id
     *
     * @return Collection|static[]
     */
    public function getLastTwoCommentsByTypeTypeID($type, $type_id, $user_comment_id)
    {
        return Comment::where('commentable_id', '=', $type_id)
            ->where('commentable_type', '=', $type)
            ->whereNotIn('id', [$user_comment_id])
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(2)
            ->get()
            ->toArray();
    }

    /**
     * Get users's comment and the last two comments for the object.
     *
     * @param $user_id
     * @param $object
     * @param $object_id
     *
     * @return array
     */
    public function getCommentsForFiche($user_id, $object, $object_id)
    {
        // Initialize
        $user_comment_id = '';
        $user_comment = '';

        if (! is_null($user_id)) {
            $user_comment = $this->getCommentByUserIDTypeTypeID($user_id, $object, $object_id, 'DESC');

            if (! is_null($user_comment)) {
                $user_comment_id = $user_comment->id;
            }
        }

        if ('comment.fiche' == Route::current()->getName()
            || 'episode.fiche' == Route::current()->getName()
            || 'season.fiche' == Route::current()->getName()) {
            $last_comment = $this->getAllCommentsByTypeTypeID($object, $object_id, 'DESC');
        } elseif ('article.show' == Route::current()->getName()) {
            $last_comment = $this->getAllCommentsByTypeTypeID($object, $object_id, 'ASC');
        } else {
            $last_comment = $this->getLastTwoCommentsByTypeTypeID($object, $object_id, $user_comment_id);
        }

        return compact('user_comment', 'last_comment');
    }

    /**
     * Return number of comments of each type for a show.
     *
     * @param $showId
     *
     * @return mixed
     */
    public function getCommentCountByTypeForShow($showId)
    {
        return Cache::rememberForever(self::THUMB_SHOW_CACHE_KEY.$showId, function () use ($showId) {
            return Comment::groupBy('thumb')
                ->select('thumb', \DB::raw('count(*) as count_thumb'))
                ->where('commentable_id', '=', $showId)
                ->where('commentable_type', '=', 'App\Models\Show')
                ->get();
        });
    }

    /**
     * @param $object
     * @param $object_id
     *
     * @return LengthAwarePaginator
     */
    public function getAllCommentsByTypeTypeID($object, $object_id, $order)
    {
        return Comment::where('commentable_id', '=', $object_id)
            ->where('commentable_type', '=', $object)
            ->with(['user', 'children' => function ($q) {
                $q->with('user');
                $q->orderBy('created_at');
            }])
            ->orderBy('created_at', $order)
            ->paginate(10);
    }

    /**
     * @param $object
     * @param $object_id
     */
    public function getAllCommentsByTypeTypeIDAdmin($object, $object_id, $order)
    {
        return Comment::where('commentable_id', '=', $object_id)
            ->where('commentable_type', '=', $object)
            ->with(['user', 'children' => function ($q) {
                $q->with('user');
                $q->orderBy('created_at');
            }])
            ->orderBy('created_at', $order)
            ->get();
    }

    /**
     * @param $id
     *
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function getCommentByID($id)
    {
        return Comment::with(['children' => function ($children) {
            $children->with('user');
            $children->orderBy('created_at');
        }, 'parent' => function ($parent) {
            $parent->with('user');
            $parent->orderBy('created_at');
        }, 'user'])->findOrFail($id);
    }

    /**
     * @param $user_id
     *
     * @return int
     */
    public function countCommentByUserIDThumbNotNull($user_id)
    {
        return Comment::where('user_id', '=', $user_id)
            ->where('thumb', '!=', null)
            ->count();
    }

    /**
     * Return number of comments for given article.
     *
     * @param $articleId
     */
    public function getCommentCountForArticle($articleId)
    {
        return Comment::with(['commentable'])
            ->where('commentable_id', '=', $articleId)
            ->whereCommentableType('App\Models\Article')
            ->count();
    }

    /**
     * Get All comments for a user with reactions.
     *
     * @param $user_id
     *
     * @return Comment[]|\Illuminate\Database\Eloquent\Builder[]|Collection|\Illuminate\Database\Query\Builder[]|\Illuminate\Support\Collection
     */
    public function getCommentsByUserID($user_id)
    {
        return Comment::with(['children', 'commentable', 'user' => function ($q) use ($user_id) {
            $q->where('id', '=', $user_id);
        }])
            ->whereNull('parent_id')
            ->whereNotNull('thumb')
            ->whereNotNull('commentable_id')
            ->get();
    }

    /**
     * @param $limit
     *
     * @return Comment[]|Collection
     */
    public function getLastComments($limit)
    {
        return Comment::limit($limit)->orderBy('created_at', 'desc')->get();
    }

    /**
     * TODO: DELETE.
     * @param $user_id
     *
     * @return Comment[]|Collection
     */
    public function getCommentByUserIDThumbNotNull($user_id)
    {
        return Comment::where('user_id', '=', $user_id)
            ->where('thumb', '!=', null)
            ->select('thumb', DB::raw('count(*) as total'))
            ->groupBy('thumb')
            ->get();
    }
}
