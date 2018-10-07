<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\EpisodeRepository;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use App\Repositories\ArticleRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\CommentRepository;
use Illuminate\Support\Facades\View;
use Calendar;


/**
 * Class ArticleController
 * @package App\Http\Controllers
 */
class PlanningController extends Controller
{
    protected $episodeRepository;

    public function __construct(EpisodeRepository $episodeRepository)
    {
        $this->episodeRepository = $episodeRepository;
    }


    public function index() {
        $events = [];

        $diffusion_us = $this->episodeRepository->getEpisodesDiffusion("diffusion_us");
        foreach($diffusion_us as $event) {
            $events[] = Calendar::event(
                $event->season->show->name . ' - ' . afficheEpisodeName($event, 1, 0),
                true,
                $event->diffusion_us,
                $event->diffusion_us,
                $event->id,
                [
                    'url' => route("episode.fiche", [$event->season->show->show_url, $event->season->name, $event->numero, $event->id]),
                    'backgroundColor' => '#1074b2',
                    'borderColor' => '#1074b2'
                ]
            );
        }

        $diffusion_fr = $this->episodeRepository->getEpisodesDiffusion("diffusion_fr");
        foreach($diffusion_fr as $event) {
            $events[] = Calendar::event(
                $event->season->show->name . ' - ' . afficheEpisodeName($event, 1, 0),
                true,
                $event->diffusion_fr,
                $event->diffusion_fr,
                $event->id,
                [
                    'url' => route("episode.fiche", [$event->season->show->show_url, $event->season->name, $event->numero, $event->id]),
                    'backgroundColor' => '#b3b3b3',
                    'borderColor' => '#b3b3b3'
                ]
            );
        }

        $calendar = Calendar::addEvents($events)
            ->setOptions([
                'firstDay' => 1,
                'lang' => 'fr',
                'locale' => 'fr',
                'aspectRatio' => 2.5,
                'showNonCurrentDates' => false,
                'fixedWeekCount' => false,
        ]);
        return view('planning.index', compact('calendar'));
    }
}