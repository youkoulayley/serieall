<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CommentCreateRequest;
use App\Http\Requests\CommentWTNCreateRequest;
use App\Http\Requests\ReactionCreateRequest;
use App\Models\Comment;
use App\Notifications\DatabaseNotification;
use App\Repositories\ArticleRepository;
use App\Repositories\CommentRepository;
use App\Repositories\EpisodeRepository;
use App\Repositories\RateRepository;
use App\Repositories\SeasonRepository;
use App\Repositories\ShowRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

/**
 * Class CommentController.
 */
class CommentController extends Controller
{
    protected $commentRepository;

    protected $showRepository;

    protected $seasonRepository;

    protected $episodeRepository;

    protected $rateRepository;

    protected $articleRepository;

    /**
     * CommentController constructor.
     */
    public function __construct(
        CommentRepository $commentRepository,
        ShowRepository $showRepository,
        SeasonRepository $seasonRepository,
        EpisodeRepository $episodeRepository,
        RateRepository $rateRepository,
        ArticleRepository $articleRepository
    ) {
        $this->commentRepository = $commentRepository;
        $this->showRepository = $showRepository;
        $this->seasonRepository = $seasonRepository;
        $this->episodeRepository = $episodeRepository;
        $this->rateRepository = $rateRepository;
        $this->articleRepository = $articleRepository;
    }

    /**
     * Print vuecomments.fiche.
     *
     * @param $show_url
     * @param null $season_name
     * @param null $episode_numero
     * @param null $episode_id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function fiche($show_url, $season_name = null, $episode_numero = null, $episode_id = null)
    {
        // Get ID User if user authenticated
        $user_id = getIDIfAuth();
        $seasonInfo = [];
        $episodeInfo = [];

        $showInfo = $this->showRepository->getInfoShowFiche($show_url);

        if (null !== $season_name) {
            $seasonInfo = $this->seasonRepository->getSeasonEpisodesBySeasonNameAndShowIDWithCommentCounts($showInfo['show']->id, $season_name);
            if (null !== $episode_numero) {
                if (null !== $episode_id) {
                    $episodeInfo = $this->episodeRepository->getEpisodeByID($episode_id);
                    $object = compileObjectInfos('Episode', $episodeInfo->id);
                    $comments = $this->commentRepository->getCommentsForFiche($user_id, $object['fq_model'], $object['id']);
                } else {
                    $episodeInfo = $this->episodeRepository->getEpisodeByEpisodeNumeroAndSeasonID($seasonInfo->id, $episode_numero);

                    // Compile Object informations
                    $object = compileObjectInfos('Episode', $episodeInfo->id);

                    // Get Comments
                    $comments = $this->commentRepository->getCommentsForFiche($user_id, $object['fq_model'], $object['id']);
                }
            } else {
                // Compile Object informations
                $object = compileObjectInfos('Season', $seasonInfo->id);

                // Get Comments
                $comments = $this->commentRepository->getCommentsForFiche($user_id, $object['fq_model'], $object['id']);
            }
        } else {
            // Compile Object informations
            $object = compileObjectInfos('Show', $showInfo['show']->id);

            // Get Comments
            $comments = $this->commentRepository->getCommentsForFiche($user_id, $object['fq_model'], $object['id']);
        }

        if (Request::ajax()) {
            return Response::json(View::make('comments.last_comments', ['comments' => $comments])->render());
        } else {
            return view('comments.fiche', compact('showInfo', 'seasonInfo', 'episodeInfo', 'object', 'comments'));
        }
    }

    /**
     * Store a new comment.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function store(CommentCreateRequest $request): JsonResponse
    {
        // Define variables by request
        $inputs = $request->all();
        $user_id = $request->user()->id;
        $object_id = $inputs['object_id'];
        $object = $inputs['object'];
        $objectFQ = 'App\Models\\'.$object;

        // Get Object
        switch ($object) {
            case 'Show':
                $object = $this->showRepository->getShowByID($object_id);

                //Invalidate cache for thumb
                Cache::forget(ShowRepository::THUMB_SHOW_CACHE_KEY.$object_id);

                break;
            case 'Season':
                $object = $this->seasonRepository->getSeasonByID($object_id);
                break;
            case 'Episode':
                $object = $this->episodeRepository->getEpisodeByID($object_id);
                break;
            case 'Article':
                $object = $this->articleRepository->getArticleByID($object_id);
                break;
            default:
                break;
        }

        // Check id comment exist
        $comment_ref = $this->commentRepository->getCommentByUserIDTypeTypeID($user_id, $objectFQ, $object_id);

        // If not, we create it
        if (null === $comment_ref) {
            // Initialize
            $comment = new Comment();

            // Define fields
            $comment->message = $inputs['avis'];
            $comment->thumb = $inputs['thumb'];

            // Attach to user and save
            $comment->user()->associate($user_id);
            $comment->save();

            // Attach to show and save
            $object->comments()->save($comment);
        } else {
            // Redefine fields
            $comment_ref->message = $inputs['avis'];
            $comment_ref->thumb = $inputs['thumb'];

            // Attach to user and save
            $comment_ref->user()->associate($user_id);
            $comment_ref->save();

            // Attach to show and save
            $object->comments()->save($comment_ref);
        }

        if (isset($inputs['episode_id'], $inputs['note'])) {
            $this->rateRepository->RateEpisode($user_id, $inputs['episode_id'], $inputs['note']);
        }

        return response()->json();
    }

    /**
     * Store a new comment.
     */
    public function storeWithoutThumbAndNote(CommentWTNCreateRequest $request): JsonResponse
    {
        // Define variables by request
        $inputs = $request->all();
        $user_id = $request->user()->id;
        $object_id = $inputs['object_id'];
        $object = $inputs['object'];
        $objectFQ = 'App\Models\\'.$object;

        // Get Object
        switch ($object) {
            case 'Article':
                $object = $this->articleRepository->getArticleByID($object_id);
                break;
            default:
                break;
        }

        // Check id comment exist
        $comment_ref = $this->commentRepository->getCommentByUserIDTypeTypeID($user_id, $objectFQ, $object_id);

        // If not, we create it
        if (null === $comment_ref) {
            // Initialize
            $comment = new Comment();

            // Define fields
            $comment->message = $inputs['avis'];

            // Attach to user and save
            $comment->user()->associate($user_id);
            $comment->save();

            // Attach to comment and save
            $object->comments()->save($comment);

            // Send Notifications for redac of the article
            foreach ($object->users as $user) {
                if ($user_id != $user->id) {
                    $user->notify(new DatabaseNotification('a commenté votre article "'.$object->name.'"', route('article.show', $object->article_url), $user_id));
                }
            }
        } else {
            // Redefine fields
            $comment_ref->message = $inputs['avis'];

            // Attach to user and save
            $comment_ref->user()->associate($user_id);
            $comment_ref->save();

            // Attach to comment and save
            $object->comments()->save($comment_ref);

            // Send notifications to reactions below this comment
            foreach ($comment_ref->children as $reaction) {
                $reaction_user = $reaction->user;
                if ($reaction_user != $user_id) {
                    $reaction_user->notify(new DatabaseNotification('a modifié son commentaire sous l\'article "'.$object->name.'"', route('article.show', $object->article_url), $user_id));
                }
            }
        }

        return response()->json();
    }

    public function storeReaction(ReactionCreateRequest $request)
    {
        // Define variables by request
        $inputs = $request->all();
        $user_id = $request->user()->id;
        $object_parent_id = $inputs['object_parent_id'];
        $notified_users = [];

        $comment_ref = new Comment();
        $comment_ref->message = $inputs['reaction'];
        $comment_ref->user()->associate($user_id);
        $comment_ref->parent()->associate($object_parent_id);
        $comment_ref->save();

        // Get Object
        $comment = $comment_ref->parent;
        $comment_object = $comment->commentable_type;
        $comment_object_id = $comment->commentable_id;
        switch ($comment_object) {
            case 'App\Models\Show':
                $object = $this->showRepository->getShowByID($comment_object_id);
                $route_object = route('comment.fiche', [$object->show_url]);
                break;
            case 'App\Models\Season':
                $object = $this->seasonRepository->getSeasonByID($comment_object_id);
                $route_object = route('comment.fiche', [$object->show->show_url, $object->name]);
                break;
            case 'App\Models\Episode':
                $object = $this->episodeRepository->getEpisodeByID($comment_object_id);
                $route_object = route('comment.fiche', [$object->show->show_url, $object->season->name, $object->numero, $object->id]);
                break;
            case 'App\Models\Article':
                $object = $this->articleRepository->getArticleByID($comment_object_id);
                $route_object = route('article.show', [$object->article_url]);
                break;
            default:
                break;
        }

        // Send notifications to parent
        $comment_user = ($comment->user);
        if ($comment_user->id != $user_id) {
            // User that will receive the notification
            $get_notif = $comment_user;

            switch ($comment_object) {
                case 'App\Models\Show':
                    $text_notif = 'a répondu à votre commentaire sous la série "'.$object->name.'"';
                    break;
                case 'App\Models\Season':
                    $text_notif = 'a répondu à votre commentaire sous la saison '.$object->name.' de la série "'.$object->show->name.'"';
                    break;
                case 'App\Models\Episode':
                    $text_notif = 'a répondu à votre commentaire sous l\'épisode '.afficheEpisodeName($object, true, false).' de la série "'.$object->show->name.'"';
                    break;
                case 'App\Models\Article':
                    $text_notif = 'a répondu à votre commentaire sous l\'article "'.$object->name.'"';
                    break;
                default:
                    break;
            }

            if (! in_array($get_notif->id, $notified_users)) {
                $get_notif->notify(new DatabaseNotification($text_notif, $route_object, $user_id));
                $notified_users[] = $get_notif->id;
            }
        }

        // Send notifications to other reactions.
        foreach ($comment->children as $other_reaction) {
            if ($other_reaction->user->id != $user_id) {
                // User that will receive the notification
                $get_notif = $other_reaction->user;
                switch ($comment_object) {
                    case 'App\Models\Show':
                        $text_notif = 'a répondu au commentaire de '.$comment->user->username.' sous la série "'.$object->name.'"';
                        break;
                    case 'App\Models\Season':
                        $text_notif = 'a répondu au commentaire de '.$comment->user->username.' sous la saison '.$object->name.' de la série "';
                        break;
                    case 'App\Models\Episode':
                        $text_notif = 'a répondu au commentaire de '.$comment->user->username.' sous l\'épisode '.afficheEpisodeName($object, true, false).' de la série "';
                        break;
                    case 'App\Models\Article':
                        $text_notif = 'a répondu au commentaire de '.$comment->user->username.' sous l\'article "'.$object->name.'"';
                        break;
                    default:
                        break;
                }

                if (! in_array($get_notif->id, $notified_users)) {
                    $get_notif->notify(new DatabaseNotification($text_notif, $route_object, $user_id));
                    $notified_users[] = $get_notif->id;
                }
            }
        }

        return response()->json();
    }
}
