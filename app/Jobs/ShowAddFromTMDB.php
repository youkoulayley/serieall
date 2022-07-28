<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Packages\TMDB\TMDBController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Class ShowAddFromTMDB.
 */
class ShowAddFromTMDB extends Job implements ShouldQueue
{
    use InteractsWithQueue;
    use SerializesModels;

    protected $inputs;

    /**
     * Create a new job instance.
     *
     * @param $inputs
     */
    public function __construct($inputs)
    {
        $this->inputs = $inputs;
    }

    public function handle()
    {
        $id = (int) $this->inputs['tmdb_id'];
        Log::debug('ShowAddFromTMDB: Start a job with : '.json_encode($id));

        $tmdb = new TMDBController(config('tmdb.apiKey'));
        $show = $tmdb->getShow($id);
        $seasons = $tmdb->getSeasonsByShow($id, $show->seasons_count);

        $show->show->taux_erectile = $this->inputs['taux_erectile'];
        $show->show->avis_rentree = $this->inputs['avis_rentree'];

        $show->show->save();

        $frChannels = explode(',', $this->inputs['chaine_fr']);
        linkAndCreateChannelsToShow($show->show, array_merge($show->channels, $frChannels));
        linkAndCreateArtistsToShow($show->show, $show->creators, 'creator');
        linkAndCreateNationalitiesToShow($show->show, $show->nationalities);
        linkAndCreateGenresToShow($show->show, $show->genres);
        linkAndCreateActorsToShow($show->show, $show->actors);

        downloadImage($show->poster, $show->show->show_url, 'poster');
        downloadImage($show->banner, $show->show->show_url, 'banner');
        resizeImage($show->show->show_url, 'poster');
        resizeImage($show->show->show_url, 'banner');

        foreach ($seasons as $season) {
            $season->season->show()->associate($show->show);
            $season->season->save();

            foreach ($season->episodes as $episode) {
                $episode->episode->season()->associate($season->season);
                $episode->episode->save();

                linkAndCreateArtistsToEpisode($episode->episode, $episode->guests, 'guest');
                linkAndCreateArtistsToEpisode($episode->episode, $episode->writers, 'writer');
                linkAndCreateArtistsToEpisode($episode->episode, $episode->directors, 'director');
            }
        }

        Log::debug('ShowAddFromTMDB: End of job with : '.json_encode($id));
    }
}
