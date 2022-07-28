<?php

declare(strict_types=1);

use App\Models\Show;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Str;

function createorUpdateShow(array $inputs)
{
    $showNotFound = 0;
    $theTvdbId = (int) $inputs['tmdb_id'];

    // Now, we are getting the informations from the Show, in english and in french
    try {
        $showFr = apiTvdbGetShow('fr', $theTvdbId)->data;
    } catch (GuzzleException | ErrorException $e) {
        Log::error('ShowAddFromTVDB: Show not found for language fr.');
        $showNotFound++;
    }
    try {
        $showEn = apiTvdbGetShow('en', $theTvdbId)->data;
    } catch (GuzzleException | ErrorException $e) {
        Log::error('ShowAddFromTVDB: Show not found for language en.');
        $showNotFound++;
    }

    if (2 == $showNotFound) {
        Log::error('ShowAddFromTVDB: Show not found for language en and fr. Quit.');

        return false;
    }

    // Define name EN as FR name when EN is not completed on TVDB (french series mostly)
    if (empty($showEn->seriesName)) {
        $showEn->seriesName = $showFr->seriesName;
    }

    // Define status of the show
    if ('Continuing' == $showEn->status) {
        $showStatus = 1;
    } else {
        $showStatus = 0;
    }

    // Concatenate channels fr and channels from thetvdb
    if (array_key_exists('chaine_fr', $inputs) || empty($inputs['chaine_fr'])) {
        $channels = $showEn->network;
    } else {
        $channels = $showEn->network.','.$inputs['chaine_fr'];
    }

    $showBdd = Show::where('tmdb_id', $inputs['tmdb_id'])->first();

    if (is_null($showBdd)) {
        // Create the show
        $showBdd = new Show([
            'thetvdb_id' => $theTvdbId,
            'name' => $showEn->seriesName,
            'name_fr' => $showFr->seriesName,
            'synopsis' => $showEn->overview,
            'synopsis_fr' => $showFr->overview,
            'diffusion_us' => $showEn->firstAired,
            'diffusion_fr' => $inputs['diffusion_fr'],
            'format' => $showEn->runtime,
            'taux_erectile' => $inputs['taux_erectile'],
            'avis_rentree' => $inputs['avis_rentree'],
            'encours' => $showStatus,
            'annee' => date_format(date_create($showEn->firstAired), 'Y'),
            'show_url' => Str::slug($showEn->seriesName),
        ]);

        $showBdd->save();
        Log::debug('Show : '.$showBdd->name.'is created.');
    } else {
        $showBdd->name = $showEn->seriesName;
        $showBdd->name_fr = $showFr->seriesName;
        $showBdd->synopsis = $showEn->overview;
        $showBdd->synopsis_fr = $showFr->overview;
        $showBdd->diffusion_us = $showEn->firstAired;
        $showBdd->format = $showEn->runtime;
        $showBdd->encours = $showStatus;
        $showBdd->annee = date_format(date_create($showEn->firstAired), 'Y');

        $showBdd->save();
        Log::debug('Show : '.$showBdd->name.'was updated.');
    }

    // Get the images for the show
    $showPoster = config('thetvdb.imageUrl').$showEn->poster;
    $showBanner = config('thetvdb.imageUrl').$showEn->banner;
    $showName = Str::slug($showEn->seriesName);

    downloadImage($showPoster, $showName, 'poster');
    downloadImage($showBanner, $showName, 'banner');
    resizeImage($showName, 'poster');
    resizeImage($showName, 'banner');

    if ($showEn->genre && ! empty($showEn->genre)) {
        Log::debug('Show : Link and create all genres for '.$showBdd->name.'.');
        linkAndCreateGenresToShow($showBdd, $showEn->genre);
    }

    if (array_key_exists('creators', $inputs) || ! empty($inputs['creators'])) {
        Log::debug('Show : Link and create all creators for '.$showBdd->name.'.');
        $inputs['creators'] = explode(',', $inputs['creators']);
        linkAndCreateArtistsToShow($showBdd, $inputs['creators'], 'creator');
    }

    if (array_key_exists('nationalities', $inputs) || ! empty($inputs['nationalities'])) {
        Log::debug('Show : Link and create all nationalities for '.$showBdd->name.'.');
        $inputs['nationalities'] = explode(',', $inputs['nationalities']);
        linkAndCreateNationalitiesToShow($showBdd, $inputs['nationalities']);
    }

    if ($channels && ! empty($channels)) {
        Log::debug('Show : Link and create all channels for '.$showBdd->name.'.');
        $channels = explode(',', $channels);
        linkAndCreateChannelsToShow($showBdd, $channels);
    }

    // Now, we are getting the informations from the actors
    try {
        $actors = apiTvdbGetActorsForShow($showBdd->tmdb_id)->data;
        linkAndCreateActorsToShow($showBdd, $actors);
    } catch (GuzzleException | ErrorException $e) {
        Log::error('Show: No actors for the show : '.$showBdd->name.'.');
    }

    try {
        linkAndCreateSeasonsToShow($showBdd);
    } catch (GuzzleException | ErrorException $e) {
        Log::error('Show: No seasons for the show : '.$showBdd->name.'.');
    }

    try {
        linkAndCreateEpisodesToShow($showBdd);
    } catch (GuzzleException | ErrorException $e) {
        Log::error('Show: No episodes for the show : '.$showBdd->name.'.'.$e);
    }

    return $showBdd;
}
