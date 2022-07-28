<?php

namespace App\Jobs;

use App\Models\Episode;
use App\Models\Season;
use App\Models\Show;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class ShowUpdateFromTVDB.
 */
class ClearDoublons extends Job implements ShouldQueue
{
    use InteractsWithQueue;
    use SerializesModels;

    /**
     * ShowUpdateFromTVDB constructor.
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
//        return true;
        /*
        |--------------------------------------------------------------------------
        | Initialisation du job
        |--------------------------------------------------------------------------
        */
        $idLog = initJob(null, 'Clear Doublons', 'Show', 0);
        $idLogListUpdateManually = initJob(null, 'List of doublons', 'Show', 0);

        $allShows = Show::all();

        saveLogMessage($idLog, 'Retrieving All Shows Ok');

        foreach ($allShows as $currentShow) {
            saveLogMessage($idLog, '> Parse current show  : '.$currentShow->name);

            $this->processSeasonForShow($currentShow, $idLog, $idLogListUpdateManually);
        }

        /*
        |--------------------------------------------------------------------------
        | End Log
        |--------------------------------------------------------------------------
        */
        endJob($idLog);
        endJob($idLogListUpdateManually);
    }

    /**
     * Remove season doublons for given show.
     *
     * @param $currentShow
     */
    private function processSeasonForShow($currentShow, $idLog, $idLogListUpdateManually)
    {
        //Retrieve all seasons from the serie with no tvdb id
        $allSeasonsForShow = Season::where('show_id', '=', $currentShow->id)
            ->get();

        if (is_null($allSeasonsForShow)) {
            saveLogMessage($idLog, '> Retrieving All Seasons for show KO - '.$currentShow->name);
        } else {
            saveLogMessage($idLog, '> Retrieving All Seasons for show Ok - '.$currentShow->name);

            foreach ($allSeasonsForShow as $currentSeason) {
                saveLogMessage($idLog, '>> Parse current season  : '.$currentSeason->name);

                if (is_null($currentSeason->thetvdb_id)) {
                    //Is there a season with tvdb id in database ?
                    $tvdbSeason = Season::where('show_id', '=', $currentShow->id)
                        ->whereNotNull('thetvdb_id')
                        ->where('name', '=', $currentSeason->name)
                        ->first();

                    if (! is_null($tvdbSeason)) {
                        //There is a tvdb season -> need to update season & episodes

                        //Verification du nombre d'épisodes dans la saison.
                        //Si pas le meme nombre, ça peut poser des soucis de numerotation => preferer une maj manuelle
                        $countEpisodesInCurrentSeason = Episode::where('season_id', '=', $currentSeason->id)
                                                        ->count();
                        $countEpisodesInTvdbSeason = Episode::where('season_id', '=', $tvdbSeason->id)
                            ->count();

                        if ($countEpisodesInCurrentSeason == $countEpisodesInTvdbSeason) {
                            $this->processEpisodesForSeason($currentShow, $currentSeason, $tvdbSeason, $idLog, $idLogListUpdateManually);

                            saveLogMessage($idLog, '>> Season with tvdb id in database  : '.$tvdbSeason);

                            if ($tvdbSeason->nbnotes > 0) {
                                //Episodes dans la saison noté => ne pas toucher automatiquement, préféré une modification manuelle.
                                saveLogMessage($idLog, '>> Season with rates - process manual update');
                                saveLogMessage($idLogListUpdateManually, ''.$currentShow->name.' - '.$currentSeason->name);
                            } else {
                                saveLogMessage($idLog, '>> No rates - automatic correction');

                                $this->updateSeasonData($currentSeason, $tvdbSeason);

                                //Clear doublons and save new one
                                try {
                                    $tvdbSeason->delete();
                                    $currentSeason->save();

                                    saveLogMessage($idLog, '>> Delete tvdb season / save current OK');
                                } catch (\Exception $e) {
                                    saveLogMessage($idLog, '>> Delete tvdb season / save current KO : '.$e);
                                }
                            }
                        } else {
                            saveLogMessage($idLog, '>> Not the same number of episodes in season - process manually');
                            saveLogMessage($idLogListUpdateManually, ''.$currentShow->name.' - '.$currentSeason->name);
                        }
                    } else {
                        //No tvdb season -> update episode, id needed
                        $this->processEpisodesForSeason($currentShow, $currentSeason, null, $idLog, $idLogListUpdateManually);

                        saveLogMessage($idLog, '>> No Season with tvdb id found in database');
                    }
                } else {
                    saveLogMessage($idLog, '>> Already tvdb Season');

                    $this->processEpisodesForSeason($currentShow, $currentSeason, null, $idLog, $idLogListUpdateManually);
                }
            }
        }
    }

    private function processEpisodesForSeason($currentShow, $currentSeason, $tvdbSeason, $idLog, $idLogListUpdateManually)
    {
        //Retrieve all episodes from the season
        $allEpisodesForSeason = Episode::where('season_id', '=', $currentSeason->id)
            ->get();

        if (is_null($allEpisodesForSeason)) {
            saveLogMessage($idLog, '>> Retrieving All Episodes for season KO - '.$currentSeason->name);
        } else {
            saveLogMessage($idLog, '>> Retrieving All Episodes for season Ok - '.$currentSeason->name);

            foreach ($allEpisodesForSeason as $currentEpisode) {
                saveLogMessage($idLog, '>>> Parse current episode  : '.$currentEpisode->numero);

                if (is_null($currentEpisode->thetvdb_id)) {
                    //Is there a episode with tvdb id in database ?
                    $tvdbEpisode = null;
                    if (is_null($tvdbSeason)) {
                        $tvdbEpisode = Episode::where('season_id', '=', $currentSeason->id)
                            ->where('numero', '=', $currentEpisode->numero)
                            ->whereNotNull('thetvdb_id')
                            ->first();
                    } else {
                        $tvdbEpisode = Episode::where('season_id', '=', $tvdbSeason->id)
                            ->where('numero', '=', $currentEpisode->numero)
                            ->whereNotNull('thetvdb_id')
                            ->first();
                    }

                    if (! is_null($tvdbEpisode)) {
                        saveLogMessage($idLog, '>>> Episode with tvdb id in database  : '.$tvdbEpisode);

                        if ($tvdbEpisode->nbnotes > 0) {
                            //Episodes dans la saison noté => ne pas toucher automatiquement, préféré une modification manuelle.
                            saveLogMessage($idLog, '>>> Episode with rates - process manual update');
                            saveLogMessage($idLogListUpdateManually, ''.$currentShow->name.' - '.$currentSeason->name.' - '.$currentEpisode->numero);
                        } else {
                            saveLogMessage($idLog, '>> No rates - automatic correction');

                            $this->updateEpisodeData($currentEpisode, $tvdbEpisode);

                            //Clear doublons and save new one
                            try {
                                $tvdbEpisode->delete();
                                $currentEpisode->save();

                                saveLogMessage($idLog, '>> Delete tvdb episode / save current OK');
                            } catch (\Exception $e) {
                                saveLogMessage($idLog, '>> Delete tvdb episode / save current KO : '.$e);
                            }
                        }
                    } else {
                        saveLogMessage($idLog, '>>> No Episode with tvdb id found in database');
                    }
                } else {
                    saveLogMessage($idLog, '>>> Already tvdb Episode');
                }
            }
        }
    }

    /**
     * Copy data from $episodeToPickDataFrom to $episodeToUpdate.
     */
    private function updateEpisodeData($episodeToUpdate, $episodeToPickDataFrom)
    {
        $episodeToUpdate->thetvdb_id = $episodeToPickDataFrom->thetvdb_id;
        if (! is_null($episodeToPickDataFrom->resume_fr) && is_null($episodeToUpdate->resume_fr)) {
            $episodeToUpdate->resume_fr = $episodeToPickDataFrom->resume_fr;
        }
        if (! is_null($episodeToPickDataFrom->resume_en) && is_null($episodeToUpdate->resume_en)) {
            $episodeToUpdate->resume_en = $episodeToPickDataFrom->resume_en;
        }
        if (! is_null($episodeToPickDataFrom->diffusion_us) && is_null($episodeToUpdate->diffusion_us)) {
            $episodeToUpdate->diffusion_us = $episodeToPickDataFrom->diffusion_us;
        }
        if (! is_null($episodeToPickDataFrom->diffusion_fr) && is_null($episodeToUpdate->diffusion_fr)) {
            $episodeToUpdate->diffusion_fr = $episodeToPickDataFrom->diffusion_fr;
        }
    }

    /**
     * Copy data from $seasonToKeepDataFrom to $seasonToUpdate.
     *
     * @param $seasonToUpdate
     * @param $seasonToKeepDataFrom
     */
    private function updateSeasonData($seasonToUpdate, $seasonToKeepDataFrom)
    {
        $seasonToUpdate->thetvdb_id = $seasonToKeepDataFrom->thetvdb_id;
    }
}
