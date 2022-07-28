<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\EpisodeRepository;
use App\Repositories\RateRepository;
use App\Repositories\SeasonRepository;
use App\Repositories\ShowRepository;

/**
 * Class ArticleController.
 */
class RankingController extends Controller
{
    protected $showRepository;

    protected $seasonRepository;

    protected $episodeRepository;

    protected $rateRepository;

    /**
     * ClassementController constructor.
     */
    public function __construct(
        ShowRepository $showRepository,
        SeasonRepository $seasonRepository,
        EpisodeRepository $episodeRepository,
        RateRepository $rateRepository
    ) {
        $this->showRepository = $showRepository;
        $this->seasonRepository = $seasonRepository;
        $this->episodeRepository = $episodeRepository;
        $this->rateRepository = $rateRepository;
    }

    /**
     * Return the view classements.index.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        // Top Show
        $top_shows = $this->showRepository->getRankingShows('DESC');
        $flop_shows = $this->showRepository->getRankingShows('ASC');
        $top_seasons = $this->seasonRepository->getRankingSeasons('DESC');
        $flop_seasons = $this->seasonRepository->getRankingSeasons('ASC');
        $top_episodes = $this->episodeRepository->getRankingEpisodes('DESC');
        $flop_episodes = $this->episodeRepository->getRankingEpisodes('ASC');

        // Redac
        $redac_top_shows = $this->rateRepository->getRankingShowRedac('DESC');
        $redac_flop_shows = $this->rateRepository->getRankingShowRedac('ASC');
        $redac_top_seasons = $this->rateRepository->getRankingSeasonRedac('DESC');
        $redac_flop_seasons = $this->rateRepository->getRankingSeasonRedac('ASC');
        $redac_top_episodes = $this->rateRepository->getRankingEpisodeRedac('DESC');
        $redac_flop_episodes = $this->rateRepository->getRankingEpisodeRedac('ASC');

        // Country
        $country_top_us = $this->showRepository->getRankingShowsByNationalities('Américaine');
        $country_top_fr = $this->showRepository->getRankingShowsByNationalities('Française');
        $country_top_en = $this->showRepository->getRankingShowsByNationalities('Anglaise');

        // Genres
        $genre_top_drama = $this->showRepository->getRankingShowsByGenres('Drame');
        $genre_top_comedy = $this->showRepository->getRankingShowsByGenres('Comedie');
        $genre_top_sf = $this->showRepository->getRankingShowsByGenres('Science-Fiction');
        $genre_top_cop = $this->showRepository->getRankingShowsByGenres('Crime');

        // Channels
        $channel_top_show = $this->rateRepository->getRankingShowChannel();

        return view('ranking.index', compact('top_shows', 'flop_shows', 'top_seasons', 'flop_seasons', 'top_episodes', 'flop_episodes', 'redac_top_shows', 'redac_flop_shows', 'redac_top_seasons', 'redac_flop_seasons', 'redac_top_episodes', 'redac_flop_episodes', 'country_top_us', 'country_top_fr', 'country_top_en', 'genre_top_drama', 'genre_top_comedy', 'genre_top_sf', 'genre_top_cop', 'channel_top_show'));
    }
}
