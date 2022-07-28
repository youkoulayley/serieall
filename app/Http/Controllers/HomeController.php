<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Slogan;
use App\Repositories\ArticleRepository;
use App\Repositories\CommentRepository;
use App\Repositories\EpisodeRepository;
use App\Repositories\RateRepository;
use App\Repositories\ShowRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

/**
 * Class HomeController.
 */
class HomeController extends Controller
{
    protected $rateRepository;

    protected $commentRepository;

    protected $articleRepository;

    protected $episodeRepository;

    protected $showRepository;

    /**
     * Create a new controller instance.
     *
     * @internal param ArticleRepository $articleRepository
     */
    public function __construct(RateRepository $rateRepository, CommentRepository $commentRepository, ArticleRepository $articleRepository, EpisodeRepository $episodeRepository, ShowRepository $showRepository)
    {
        $this->rateRepository = $rateRepository;
        $this->commentRepository = $commentRepository;
        $this->articleRepository = $articleRepository;
        $this->episodeRepository = $episodeRepository;
        $this->showRepository = $showRepository;
    }

    /**
     * Return Homepage.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function index()
    {
        if (Request::ajax()) {
            //Filtre depuis la homepage sur le fil d'actu
            //TODO : créer une méthode spécifique d'API

            $filter_home = Request::input('filter_home');

            switch ($filter_home) {
                case 'all':
                    $fil_actu = $this->getFilActuWithAll();
                    break;
                case 'rates':
                    $fil_actu = $this->getFilActuWithRates();
                    break;
                default:
                    $fil_actu = $this->getFilActuWithComments();
                    break;
            }

            return Response::json(View::make('pages.home_fil_actu', ['fil_actu' => $fil_actu, 'filter_home' => $filter_home])->render());
        } else {
            $slogan = Slogan::inRandomOrder()->first();

            // Fil d'actu
            $filter_home = 'all';
            $fil_actu = $this->getFilActuWithAll()->take(30);

            // Get show of the moment
            $shows_moment = $this->rateRepository->getShowsMoment();

            // Get Articles
            $articles = $this->articleRepository->getLast6Articles();

            // Get last added shows
            $last_added_shows = $this->showRepository->getLastAddedShows();

            // Get Planning on 14days
            $dates['yesterday'] = Carbon::now()->subDay()->format('Y-m-d');
            $dates['today'] = Carbon::now()->format('Y-m-d');
            $dates['tomorrow'] = Carbon::now()->addDay()->format('Y-m-d');
            $planning['yesterday'] = $this->episodeRepository->getPlanningHome('diffusion_us', $dates['yesterday']);
            $planning['today'] = $this->episodeRepository->getPlanningHome('diffusion_us', $dates['today']);
            $planning['tomorrow'] = $this->episodeRepository->getPlanningHome('diffusion_us', $dates['tomorrow']);

            return view('pages.home', compact('fil_actu', 'shows_moment', 'articles', 'planning', 'dates', 'last_added_shows', 'filter_home', 'slogan'));
        }
    }

    /************** Privates ************/

    /**
     * Retourne le contenu du fil d'actu comprenant les notes et les commentaires.
     *
     * @return RateRepository[]|\Illuminate\Database\Eloquent\Collection
     */
    private function getFilActuWithAll()
    {
        // Get last Rates and comments
        $lastRates = $this->rateRepository->getLastRates(15);
        $lastRates->map(function ($rate) {
            $rate->type = 'rate';
        });
        $lastComments = $this->commentRepository->getLastComments(15);
        $lastComments->map(function ($comment) {
            $comment->type = 'comment';
        });

        $lastArticles = $this->articleRepository->getLast6Articles();
        $lastArticles->map(function ($article) {
            $article->type = 'article';
            $article->created_at = $article->published_at;
        });

        // Merge collections of comments and rates
        return $lastRates->concat($lastComments)->concat($lastArticles)->sortByDesc('created_at');
    }

    /**
     * Retourne le contenu du fil d'actu comprenant les notes.
     *
     * @return RateRepository[]|\Illuminate\Database\Eloquent\Collection
     */
    private function getFilActuWithRates()
    {
        // Get last Rates
        $lastRates = $this->rateRepository->getLastRates(30);
        $lastRates->map(function ($rate) {
            $rate->type = 'rate';
        });

        // Merge collections of comments and rates
        return $lastRates->sortByDesc('created_at');
    }

    /**
     * Retourne le contenu du fil d'actu comprenant les commentaires.
     *
     * @return RateRepository[]|\Illuminate\Database\Eloquent\Collection
     */
    private function getFilActuWithComments()
    {
        // Get last comments
        $lastComments = $this->commentRepository->getLastComments(30);
        $lastComments->map(function ($comment) {
            $comment->type = 'comment';
        });

        // Merge collections of comments and rates
        return $lastComments->sortByDesc('created_at');
    }
}
