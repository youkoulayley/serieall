<?php

declare(strict_types=1);

use App\Models\Episode;
use App\Models\Season;
use App\Models\Show;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

function linkAndCreateEpisodesToShow(Show $show): bool
{
    // First, we check the availability of all episodes
    $listEpisodes = getListEpisodes($show, 1);
    if (empty($listEpisodes)) {
        return false;
    }

    // Add episodes and loop over page is necessary
    $listEpisodesNextPage = $listEpisodes->links->next;
    $listEpisodesLastPage = $listEpisodes->links->last;

    $listEpisodesData = $listEpisodes->data;

    if (is_null($listEpisodesNextPage)) {
        Log::debug('Episode : Only one page for episodes. Adding episodes...');
        createOrUpdateEpisode($show, $listEpisodesData);
    } else {
        Log::debug('Episode : Multiple pages for episodes. Adding page 1/'.$listEpisodesLastPage);
        createOrUpdateEpisode($show, $listEpisodesData);

        while ($listEpisodesNextPage <= $listEpisodesLastPage) {
            try {
                $listEpisodes = apiTvdbGetEpisodesForShow('fr', $show->tmdb_id, $listEpisodesNextPage);
                if (empty($listEpisodes)) {
                    return false;
                }

                createOrUpdateEpisode($show, $listEpisodes->data);
                $listEpisodesNextPage++;
            } catch (GuzzleException | ErrorException $e) {
                Log::error('Episode: Episodes not found for language en or fr.');

                return false;
            }
        }
    }

    return true;
}

/**
 * Get the list of episodes.
 */
function getListEpisodes(Show $show, int $page): object
{
    try {
        $listEpisodesEn = apiTvdbGetEpisodesForShow('en', $show->tmdb_id, $page);
        if (isset($listEpisodesEn->errors)) {
            $listEpisodesFr = apiTvdbGetEpisodesForShow('fr', $show->tmdb_id, $page);
            if (isset($listEpisodesFr->errors)) {
                Log::error('Episode: List of episodes not found either in french or english.');

                return (object) [];
            } else {
                $listEpisodes = $listEpisodesFr;
            }
        } else {
            $listEpisodes = $listEpisodesEn;
        }

        return $listEpisodes;
    } catch (GuzzleException | ErrorException $e) {
        Log::error('Episode: Episodes not found for language en or fr.');

        return (object) [];
    }
}

function createOrUpdateEpisode(Show $show, array $listEpisodes)
{
    $episodeNotFound = 0;

    foreach ($listEpisodes as $episode) {
        // Now, we are getting the informations from the Episode, in english and in french
        try {
            $episodeFr = apiTvdbGetEpisode('fr', $episode->id)->data;
        } catch (GuzzleException | ErrorException $e) {
            Log::error('Episode: Episode not found for language fr.');
            $episodeNotFound++;
        }
        try {
            $episodeEn = apiTvdbGetEpisode('en', $episode->id)->data;
        } catch (GuzzleException | ErrorException $e) {
            Log::error('Episode: Episode not found for language en.');
            $episodeNotFound++;
        }

        if (2 == $episodeNotFound) {
            // Skipping this episode
            Log::error('Episode: Episode not found for language en and fr. Continue.');
            continue;
        }

        // Init Episode properties
        $episodeSeason = chooseBetweenTwoVars($episodeEn->airedSeason, $episodeFr->airedSeason);
        if (0 == $episodeSeason) {
            $episodeNumber = 0;
        } else {
            $episodeNumber = chooseBetweenTwoVars($episodeEn->airedEpisodeNumber, $episodeFr->airedEpisodeNumber);
        }
        $episodeTvdbId = chooseBetweenTwoVars($episodeEn->id, $episodeFr->id);
        $episodeImage = chooseBetweenTwoVars($episodeEn->filename, $episodeFr->filename);
        $episodeSeasonId = chooseBetweenTwoVars($episodeEn->airedSeasonID, $episodeFr->airedSeasonID);
        $episodeAirsAfterSeason = chooseBetweenTwoVars($episodeEn->airsAfterSeason, $episodeFr->airsAfterSeason);
        $episodeGuestStars = chooseBetweenTwoVars($episodeEn->guestStars, $episodeFr->guestStars);
        $episodeDirectors = chooseBetweenTwoVars($episodeEn->directors, $episodeFr->directors);
        $episodeWriters = chooseBetweenTwoVars($episodeEn->writers, $episodeFr->writers);

        $episodeInfo = [
            'tmdb_id' => $episodeTvdbId,
            'numero' => $episodeNumber,
            'name' => $episodeEn->episodeName,
            'name_fr' => $episodeFr->episodeName,
            'resume' => $episodeEn->overview,
            'resume_fr' => $episodeFr->overview,
            'diffusion_us' => $episodeEn->firstAired,
            'diffusion_fr' => $episodeFr->firstAired,
            'image' => $episodeImage,
            'season_id' => $episodeSeasonId,
            'guest_stars' => $episodeGuestStars,
            'directors' => $episodeDirectors,
            'writers' => $episodeWriters,
        ];

        $episodeBdd = Episode::where('tmdb_id', $episodeInfo['tmdb_id'])->first();
        if (0 == $episodeSeason && ! is_null($episodeAirsAfterSeason)) {
            $seasonBdd = Season::where('name', $episodeAirsAfterSeason)->where('show_id', $show->id)->first();
        } elseif (0 == $episodeSeason && is_null($episodeAirsAfterSeason)) {
            continue;
        } else {
            $seasonBdd = Season::where('tmdb_id', $episodeInfo['season_id'])->first();
        }

        if (is_null($episodeBdd)) {
            $episodeBdd = new Episode([
                'tmdb_id' => $episodeTvdbId,
                'numero' => $episodeNumber,
                'name' => $episodeEn->episodeName,
                'name_fr' => $episodeFr->episodeName,
                'resume' => $episodeEn->overview,
                'resume_fr' => $episodeFr->overview,
                'diffusion_us' => $episodeEn->firstAired,
                'diffusion_fr' => $episodeFr->firstAired,
                'picture' => $episodeImage,
            ]);

            $episodeBdd->season()->associate($seasonBdd);
            $episodeBdd->save();

            linkAndCreateArtistsToEpisode($episodeBdd, $episodeInfo['guest_stars'], 'guest');
            linkAndCreateArtistsToEpisode($episodeBdd, $episodeInfo['directors'], 'director');
            linkAndCreateArtistsToEpisode($episodeBdd, $episodeInfo['writers'], 'writer');

            Log::debug('Episode : '.$episodeBdd->numero.' is created and linked to season '.$seasonBdd->name.'. This is the object : '.$episodeBdd);
        } else {
            $episodeBdd->numero = $episodeNumber;
            $episodeBdd->name = $episodeEn->episodeName;
            $episodeBdd->name_fr = $episodeFr->episodeName;
            $episodeBdd->resume = $episodeEn->overview;
            $episodeBdd->resume_fr = $episodeFr->overview;
            $episodeBdd->diffusion_us = $episodeEn->firstAired;
            $episodeBdd->diffusion_fr = $episodeFr->firstAired;
            $episodeBdd->picture = $episodeImage;

            $episodeBdd->season()->associate($seasonBdd);
            $episodeBdd->save();
        }

        linkAndCreateArtistsToEpisode($episodeBdd, $episodeInfo['guest_stars'], 'guest');
        linkAndCreateArtistsToEpisode($episodeBdd, $episodeInfo['directors'], 'director');
        linkAndCreateArtistsToEpisode($episodeBdd, $episodeInfo['writers'], 'writer');

        Log::debug('Episode : '.$episodeBdd->numero.' is updated and linked to season '.$seasonBdd->name.'. This is the new object : '.$episodeBdd);
    }
}
