<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Charts\RateSummary;
use App\Repositories\ArticleRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\CommentRepository;
use App\Repositories\EpisodeRepository;
use App\Repositories\SeasonRepository;
use App\Repositories\ShowRepository;
use App\Traits\FormatShowHeaderTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

/**
 * Class ShowController.
 */
class ShowController extends Controller
{
    use FormatShowHeaderTrait;

    protected $showRepository;

    protected $seasonRepository;

    protected $episodeRepository;

    protected $commentRepository;

    protected $articleRepository;

    protected $categoryRepository;

    /**
     * ShowController constructor.
     */
    public function __construct(
        ShowRepository $showRepository,
        SeasonRepository $seasonRepository,
        EpisodeRepository $episodeRepository,
        CommentRepository $commentRepository,
        ArticleRepository $articleRepository,
        CategoryRepository $categoryRepository
    ) {
        $this->showRepository = $showRepository;
        $this->seasonRepository = $seasonRepository;
        $this->episodeRepository = $episodeRepository;
        $this->commentRepository = $commentRepository;
        $this->articleRepository = $articleRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Print vue shows.index.
     *
     * @param string|int $channel
     * @param string|int $nationality
     * @param string|int $genre
     * @param string     $tri
     * @param string     $order
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index($channel = '0', $genre = '0', $nationality = '0', $tri = 1)
    {
        switch ($tri) {
            case 1:
                $tri = 'name';
                $order = 'asc';
                break;
            case 2:
                $tri = 'name';
                $order = 'desc';
                break;
            case 3:
                $tri = 'moyenne';
                $order = 'asc';
                break;
            case 4:
                $tri = 'moyenne';
                $order = 'desc';
                break;
            case 5:
                $tri = 'diffusion_us';
                $order = 'asc';
                break;
            case 6:
                $tri = 'diffusion_us';
                $order = 'desc';
                break;
            default:
                $tri = 'name';
                $order = 'asc';
                break;
        }

        if ('0' === $channel) {
            $channel = '';
        }
        if ('0' === $nationality) {
            $nationality = '';
        }
        if ('0' === $genre) {
            $genre = '';
        }

        if (Request::ajax()) {
            $shows = $this->showRepository->getAllShows($channel, $genre, $nationality, $tri, $order);

            return Response::json(View::make('shows.index_cards', ['shows' => $shows])->render());
        } else {
            $shows = $this->showRepository->getAllShows($channel, $genre, $nationality, $tri, $order);
        }

        return view('shows.index', compact('shows'));
    }

    /**
     * Envoi vers la page shows/index
     * Page principale d'une série.
     *
     * @param $show_url
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getShowFiche($show_url)
    {
        // Get ID User if user authenticated
        $user_id = getIDIfAuth();

        // Get Show
        $show = $this->showRepository->getShowByURL($show_url);

        if (! is_null($show)) {
            $showInfo = $this->formatForShowHeader($show);
            $showInfo['seasons'] = $this->seasonRepository->getSeasonsCountEpisodesForShowByID($show->id);

            $ratesShow = $this->showRepository->getRateByShowID($showInfo['show']->id);

            $state_show = '';
            if (Auth::check()) {
                if (Auth::user()->shows->contains($showInfo['show']->id)) {
                    $state_show = Auth::user()->join('show_user', 'users.id', '=', 'show_user.user_id')
                        ->join('shows', 'show_user.show_id', '=', 'shows.id')
                        ->where('users.id', '=', Auth::user()->id)
                        ->where('shows.id', '=', $showInfo['show']->id)
                        ->pluck('state')
                        ->first();
                }
            }

            //Graphe d'évolution des notes de la saison
            $chart = new RateSummary();
            $chart
                ->height(300)
                ->title('Evolution des notes de la série')
                ->labels($showInfo['seasons']->pluck('name'))
                ->dataset('Moyenne', 'line', $showInfo['seasons']->pluck('moyenne'));

            $chart->options([
                'yAxis' => [
                    'min' => 0,
                    'max' => 20,
                ],
            ]);

            // Compile Object informations
            $object = compileObjectInfos('Show', $showInfo['show']->id);

            // Get Comments
            $comments = $this->commentRepository->getCommentsForFiche($user_id, $object['fq_model'], $object['id']);

            $type_article = 'Show';
            $articles_linked = $this->articleRepository->getPublishedArticleByShowID(0, $showInfo['show']->id);

            return view('shows/fiche', ['chart' => $chart], compact('showInfo', 'type_article', 'articles_linked', 'comments', 'object', 'state_show', 'ratesShow'));
        } else {
            //Show not found -> 404
            abort(404);
        }
    }

    /**
     * Envoi vers la page shows/details.
     *
     * @param $show_url
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getShowDetails($show_url)
    {
        $show = $this->showRepository->getShowDetailsByURL($show_url);
        if (! is_null($show)) {
            $showInfo = $this->formatForShowHeader($show);

            return view('shows/details', compact('showInfo'));
        } else {
            abort(404);
        }
    }

    public function getShowArticles($show_url)
    {
        $show = $this->showRepository->getShowByURL($show_url);
        if (! is_null($show)) {
            $showInfo = $this->formatForShowHeader($show);

            $categories = $this->categoryRepository->getAllCategories();
            $articles = $this->articleRepository->getPublishedArticleByShow($showInfo['show']);

            //Retrieve comment count for each article
            foreach ($articles as $article) {
                $article['comments_count'] = $this->commentRepository->getCommentCountForArticle($article->id);
            }

            $articles_count = count($articles);

            return view('shows/articles', compact('showInfo', 'articles', 'articles_count', 'categories'));
        } else {
            abort(404);
        }
    }

    /**
     * Print the articles/indexCategory vue.
     *
     * @param $show_url
     * @param $idCategory
     *
     * @return View
     */
    public function getShowArticlesByCategory($show_url, $idCategory)
    {
        $show = $this->showRepository->getShowByURL($show_url);
        if (! is_null($show)) {
            $showInfo = $this->formatForShowHeader($show);

            $categories = $this->categoryRepository->getAllCategories();
            $category = $this->categoryRepository->getCategoryByID($idCategory);
            $articles = $this->articleRepository->getPublishedArticlesByCategoriesAndShowWithAutorsCommentsAndCategory($showInfo['show'], $idCategory);

            //Retrieve comment count for each article
            foreach ($articles as $article) {
                $article['comments_count'] = $this->commentRepository->getCommentCountForArticle($article->id);
            }

            $articles_count = count($articles);

            return view('shows.articlesCategory', compact('showInfo', 'categories', 'category', 'articles', 'articles_count', 'idCategory'));
        } else {
            abort(404);
        }
    }

    /**
     * Get Statistics. Return shows.statistics.
     *
     * @param $show_url
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getStatistics($show_url)
    {
        $showInfo = $this->showRepository->getInfoShowFiche($show_url);
        $topEpisodes = $this->episodeRepository->getRankingEpisodesByShow($showInfo['show']['id'], 'DESC');

        return view('shows.statistics', compact('showInfo', 'topEpisodes'));
    }
}
