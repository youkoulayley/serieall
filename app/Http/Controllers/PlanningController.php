<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\EpisodeRepository;
use Calendar;

/**
 * Class ArticleController.
 */
class PlanningController extends Controller
{
    protected $episodeRepository;

    public function __construct(EpisodeRepository $episodeRepository)
    {
        $this->episodeRepository = $episodeRepository;
    }

    public function index()
    {
        $events = [];

        $diffusion_us = $this->episodeRepository->getEpisodesDiffusion('diffusion_us');
        foreach ($diffusion_us as $event) {
            $events[] = Calendar::event(
                $event->season->show->name.' - '.afficheEpisodeName($event, 1, 0),
                true,
                $event->diffusion_us,
                $event->diffusion_us,
                $event->id,
                [
                    'url' => route('episode.fiche', [$event->season->show->show_url, $event->season->name, $event->numero, $event->id]),
                    'backgroundColor' => '#1074b2',
                    'borderColor' => '#1074b2',
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
        ])->setCallbacks(['eventRender' => 'function(eventObj, $el) {$el.popup({title: eventObj.title,content: eventObj.description,trigger: "hover",placement: "top",container: "body"});}']);

        return view('planning.index', compact('calendar'));
    }
}
