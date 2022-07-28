<?php

namespace App\Jobs;

use App\Models\Episode;
use App\Models\Season;
use App\Models\Show;
use App\Packages\TMDB\TMDBController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Class ShowUpdateFromTMDB.
 */
class OneShowUpdateFromTMDB extends Job implements ShouldQueue
{
    use InteractsWithQueue;
    use SerializesModels;

    protected $id;

    /**
     * ShowUpdateFromTVDB constructor.
     *
     * @param $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    public function handle()
    {
        Log::debug('OneShowUpdateFromTMDB: Start a job with : '.json_encode($this->id));

        $tmdb = new TMDBController(config('tmdb.apiKey'));
        $show = $tmdb->getShow($this->id);
        $seasons = $tmdb->getSeasonsByShow($this->id, $show->seasons_count);

        $showGot = Show::where('tmdb_id', $this->id)->first();

        if (is_null($showGot)) {
            Log::Error('OneShowUpdateFromTMDB: show '.$this->id." doesn't exists");

            return;
        }

        // Update show
        $showGot->name = $show->show->name;
        $showGot->name_fr = $show->show->name_fr;
        $showGot->synopsis = $show->show->synopsis;
        $showGot->synopsis_fr = $show->show->synopsis_fr;
        $showGot->diffusion_us = $show->show->diffusion_us;
        $showGot->format = $show->show->format;
        $showGot->encours = $show->show->encours;
        $showGot->annee = $show->show->annee;

        $showGot->save();
        Log::Info('OneShowUpdateFromTMDB: show '.$this->id.' has been updated');

        // Get images for the show
        downloadImage($show->poster, $showGot->show_url, 'poster');
        downloadImage($show->banner, $showGot->show_url, 'banner');
        resizeImage($showGot->show_url, 'poster');
        resizeImage($showGot->show_url, 'banner');

        Log::Info('OneShowUpdateFromTMDB: show images '.$this->id.' has been updated');

        // Updates metadata
        linkAndCreateChannelsToShow($showGot, $show->channels);
        linkAndCreateArtistsToShow($showGot, $show->creators, 'creator');
        linkAndCreateNationalitiesToShow($showGot, $show->nationalities);
        linkAndCreateGenresToShow($showGot, $show->genres);
        linkAndCreateActorsToShow($showGot, $show->actors);

        $this->processSeasons($showGot, $seasons);

        Log::debug('OneShowUpdateFromTMDB: Enf of job with : '.json_encode($this->id));
    }

    private function processSeasons($show, $seasons)
    {
        foreach ($seasons as $season) {
            $seasonGot = Season::where('tmdb_id', $season->season->tmdb_id)->first();

            if (is_null($seasonGot)) {
                // Check if the season exist in the database without a tmdb_id but with a tvdb_id.
                $seasonGot = Season::where('name', $season->season->name)
                    ->whereNotNull('thetvdb_id')
                    ->whereNull('tmdb_id')
                    ->where('show_id', $show->id)
                    ->first();

                if (is_null($seasonGot)) {
                    // Create season we don't have it
                    Log::Info('OneShowUpdateFromTMDB: Season '.$season->season->name." doesn't exists");
                    $seasonGot = new Season([
                        'tmdb_id' => $season->season->tmdb_id,
                    ]);
                } else {
                    Log::Info('OneShowUpdateFromTMDB: Season '.$season->season->name.' exists but without tmdb_id');
                    $seasonGot->tmdb_id = $season->season->tmdb_id;
                }
            }

            // Season exists, update name and link to show
            $seasonGot->name = $season->season->name;

            $seasonGot->show()->associate($show);
            $seasonGot->save();

            Log::Info('OneShowUpdateFromTMDB: Season '.$season->season->name.' updated');

            $this->processEpisodes($seasonGot, $season->episodes);
        }
    }

    private function processEpisodes(Season $season, array $episodes)
    {
        foreach ($episodes as $episode) {
            $episodeGot = Episode::where('tmdb_id', $episode->episode->tmdb_id)->first();

            if (is_null($episodeGot)) {
                // Check if the season exist in the database without a tmdb_id but with a tvdb_id.
                $episodeGot = Episode::where('numero', $episode->episode->numero)
                    ->whereNotNull('thetvdb_id')
                    ->whereNull('tmdb_id')
                    ->where('season_id', $season->id)
                    ->first();

                if (is_null($episodeGot)) {
                    // Create season we don't have it
                    Log::Info('OneShowUpdateFromTMDB: Episode '.$season->name.'/'.$episode->episode->numero." doesn't exists");
                    $episodeGot = new Episode([
                        'tmdb_id' => $episode->episode->tmdb_id,
                    ]);
                } else {
                    Log::Info('OneShowUpdateFromTMDB: Episode '.$season->name.'/'.$episode->episode->numero.' exists but without tmdb_id');
                    $episodeGot->tmdb_id = $episode->episode->tmdb_id;
                }
            }

            // Season exists, update name and link to show
            $episodeGot->numero = $episode->episode->numero;
            $episodeGot->name = $episode->episode->name;
            $episodeGot->name_fr = $episode->episode->name_fr;
            $episodeGot->resume = $episode->episode->resume;
            $episodeGot->resume_fr = $episode->episode->resume_fr;
            $episodeGot->diffusion_us = $episode->episode->diffusion_us;
            $episodeGot->diffusion_fr = $episode->episode->diffusion_fr;
            $episodeGot->picture = $episode->episode->picture;

            $episodeGot->season()->associate($season);
            $episodeGot->save();

            Log::Info('OneShowUpdateFromTMDB: Episode '.$season->name.'/'.$episode->episode->numero.' updated');

            linkAndCreateArtistsToEpisode($episodeGot, $episode->guests, 'guest');
            linkAndCreateArtistsToEpisode($episodeGot, $episode->writers, 'writer');
            linkAndCreateArtistsToEpisode($episodeGot, $episode->directors, 'director');
        }
    }
}
