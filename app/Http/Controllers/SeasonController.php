<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Charts\RateSummary;
use App\Repositories\ArticleRepository;
use App\Repositories\CommentRepository;
use App\Repositories\SeasonRepository;
use App\Repositories\ShowRepository;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

/**
 * Class SeasonController.
 */
class SeasonController extends Controller
{
    protected $showRepository;

    protected $seasonRepository;

    protected $commentRepository;

    protected $articleRepository;

    /**
     * ShowController constructor.
     */
    public function __construct(
        ShowRepository $showRepository,
        SeasonRepository $seasonRepository,
        CommentRepository $commentRepository,
        ArticleRepository $articleRepository
    ) {
        $this->showRepository = $showRepository;
        $this->seasonRepository = $seasonRepository;
        $this->commentRepository = $commentRepository;
        $this->articleRepository = $articleRepository;
    }

    /**
     * Envoi vers la page shows/seasons.
     *
     * @param $show_url
     * @param $season
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getSeasonFiche($show_url, $season)
    {
        // Get ID User if user authenticated
        $user_id = getIDIfAuth();

        $showInfo = $this->showRepository->getInfoShowFiche($show_url);
        $seasonInfo = $this->seasonRepository->getSeasonEpisodesBySeasonNameAndShowIDWithCommentCounts($showInfo['show']->id, $season);

        // Compile Object informations
        $object = compileObjectInfos('Season', $seasonInfo->id);

        $ratesSeason = $this->seasonRepository->getRateBySeasonID($seasonInfo->id);

        $chart = new RateSummary();
        $chart
            ->height(300)
            ->title('Evolution des notes de la saison')
            ->labels($seasonInfo->episodes->pluck('numero'))
            ->dataset('Moyenne', 'line', $seasonInfo->episodes->pluck('moyenne'));
        $chart->options([
            'yAxis' => [
                'min' => 0,
                'max' => 20,
            ],
        ]);

        // Get Comments
        $comments = $this->commentRepository->getCommentsForFiche($user_id, $object['fq_model'], $object['id']);

        $type_article = 'Season';
        $articles_linked = $this->articleRepository->getPublishedArticleBySeasonID(0, $seasonInfo->id);

        if (Request::ajax()) {
            return Response::json(View::make('comments.last_comments', ['comments' => $comments])->render());
        } else {
            return view('seasons.fiche', ['chart' => $chart], compact('showInfo', 'type_article', 'articles_linked', 'seasonInfo', 'ratesSeason', 'comments', 'object'));
        }
    }
}
