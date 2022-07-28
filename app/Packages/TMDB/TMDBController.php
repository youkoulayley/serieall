<?php

namespace App\Packages\TMDB;

use App\Models\Episode;
use App\Models\Season;
use App\Models\Show;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Tmdb\Client;
use Tmdb\Event\BeforeRequestEvent;
use Tmdb\Event\Listener\Request\AcceptJsonRequestListener;
use Tmdb\Event\Listener\Request\ApiTokenRequestListener;
use Tmdb\Event\Listener\Request\ContentTypeJsonRequestListener;
use Tmdb\Event\Listener\Request\UserAgentRequestListener;
use Tmdb\Event\Listener\RequestListener;
use Tmdb\Event\RequestEvent;
use Tmdb\Token\Api\ApiToken;

/**
 * Class TMDBController.
 */
class TMDBController
{
    private string $apiKey;

    public Client $client;

    public function __construct(string $apiKey)
    {
        $this->apiKey = new ApiToken($apiKey);

        $ed = new EventDispatcher();

        $this->client = new Client([
            'api_token' => $this->apiKey,
            'event_dispatcher' => [
                'adapter' => $ed,
            ],
            'http' => [
                'client' => null,
                'request_factory' => null,
                'response_factory' => null,
                'stream_factory' => null,
                'uri_factory' => null,
            ],
        ]);

        $requestListener = new RequestListener($this->client->getHttpClient(), $ed);
        $ed->addListener(RequestEvent::class, $requestListener);

        $apiTokenListener = new ApiTokenRequestListener($this->client->getToken());
        $ed->addListener(BeforeRequestEvent::class, $apiTokenListener);

        $acceptJsonListener = new AcceptJsonRequestListener();
        $ed->addListener(BeforeRequestEvent::class, $acceptJsonListener);

        $jsonContentTypeListener = new ContentTypeJsonRequestListener();
        $ed->addListener(BeforeRequestEvent::class, $jsonContentTypeListener);

        $userAgentListener = new UserAgentRequestListener();
        $ed->addListener(BeforeRequestEvent::class, $userAgentListener);
    }

    /**
     * Find Show on TMDB with the TVDB ID.
     *
     * @param int $id
     * @return int
     */
    public function findShow(int $id): int
    {
        $result = $this->client->getFindApi()->findBy($id, ['external_source' => 'tvdb_id']);

        if (count($result['tv_results']) < 1) {
            Log::Error($id.' show not found on tmdb');

            return 0;
        }

        return $result['tv_results'][0]['id'];
    }

    /**
     * Find Season on TMDB with the TVDB ID.
     *
     * @param int $id
     * @return int
     */
    public function findSeason(int $id): int
    {
        $result = $this->client->getFindApi()->findBy($id, ['external_source' => 'tvdb_id']);

        if (count($result['tv_season_results']) < 1) {
            Log::Error($id.' season not found on tmdb');

            return 0;
        }

        return $result['tv_season_results'][0]['id'];
    }

    /**
     * Find Episode on TMDB with the TVDB ID.
     *
     * @param int $id
     * @return int
     */
    public function findEpisode(int $id): int
    {
        $result = $this->client->getFindApi()->findBy($id, ['external_source' => 'tvdb_id']);

        if (count($result['tv_episode_results']) < 1) {
            Log::Error($id.' episode not found on tmdb');

            return 0;
        }

        return $result['tv_episode_results'][0]['id'];
    }

    // getShow gets a specific show.
    public function getShow(int $id): TMDBShow
    {
        $TMDBShowEN = $this->client->getTvApi()->getTvshow($id, ['language' => 'en']);
        $TMDBShowFR = $this->client->getTvApi()->getTvshow($id, ['language' => 'fr']);

        $genres = $this->buildGenres($TMDBShowFR['genres']);
        $creators = $this->buildCreators($TMDBShowEN['created_by']);
        $nationalities = $this->buildNationalities($TMDBShowEN['production_countries']);
        $channels = $this->buildChannels($TMDBShowEN['networks']);
        $actors = $this->getActors($id);

        return new TMDBShow(
            new Show([
                'tmdb_id' => $TMDBShowEN['id'],
                'show_url' => Str::slug($TMDBShowEN['name']),
                'name' => $TMDBShowEN['name'],
                'name_fr' => $TMDBShowFR['name'],
                'synopsis' => $TMDBShowEN['overview'],
                'synopsis_fr' => $TMDBShowFR['overview'],
                'format' => $TMDBShowEN['episode_run_time'][0],
                'annee' => date_format(date_create($TMDBShowEN['first_air_date']), 'Y'),
                'encours' => $TMDBShowEN['in_production'] ? 1 : 0,
                'diffusion_us' => $TMDBShowEN['first_air_date'],
            ]),
            config('tmdb.imageURL').'/w780'.$TMDBShowEN['poster_path'],
            config('tmdb.imageURL').'/w1280'.$TMDBShowEN['backdrop_path'],
            $genres,
            $creators,
            $nationalities,
            $channels,
            $actors,
            $TMDBShowEN['number_of_seasons'],
            $TMDBShowEN['number_of_episodes'],
        );
    }

    // getActors gets all actors for a show.
    public function getActors(string $id): array
    {
        $people = $this->client->getTvApi()->getCredits($id);

        $listActors = [];
        foreach ($people['cast'] as $i => $actor) {
            if ('Acting' != $actor['known_for_department']) {
                continue;
            }

            array_push($listActors, new TMDBArtist($actor['name'], $actor['character'], config('tmdb.imageURL').'/w500'.$actor['profile_path']));
        }

        return $listActors;
    }

    // getSeasonsByShow gets all the seasons for a specific show.
    public function getSeasonsByShow(int $id, int $seasonsCount): array
    {
        $listSeasons = [];

        // Don't get specials episodes (i starts at 1)
        for ($i = 1; $i <= $seasonsCount; $i++) {
            $TMDBSeasonEN = $this->client->getTvSeasonApi()->getSeason($id, $i, ['language' => 'en']);
            $TMDBSeasonFR = $this->client->getTvSeasonApi()->getSeason($id, $i, ['language' => 'fr']);

            $episodes = $this->getEpisodes($TMDBSeasonEN['episodes'], $TMDBSeasonFR['episodes']);

            array_push(
                $listSeasons,
                new TMDBSeason(
                    new Season([
                        'tmdb_id' => $TMDBSeasonEN['id'],
                        'name' => $TMDBSeasonEN['season_number'],
                    ]),
                    $episodes,
                )
            );
        }

        return $listSeasons;
    }

    /**
     * getUpdates returns a list of TMDB ID shows to update.
     *
     * @return array
     */
    public function getUpdates(): array
    {
        $changes = [];
        $tmdbChanges = $this->client->getChangesApi()->getTvChanges();
        $changesFiltered = $this->appendChanges($tmdbChanges);

        array_push($changes, ...$changesFiltered);

        $totalPages = $tmdbChanges['total_pages'];

        for ($i = 2; $i <= $totalPages; $i++) {
            $changes = $this->client->getChangesApi()->getTvChanges(['page' => $i]);
            $changesFiltered = $this->appendChanges($tmdbChanges);

            array_push($changes, ...$changesFiltered);
        }

        return $changes;
    }

    private function appendChanges(array $tmdbChanges): array
    {
        $ids = [];

        foreach ($tmdbChanges['results'] as $change) {
            if (Show::where('tmdb_id', $change['id'])->exists()) {
                array_push($ids, $change['id']);
            }
        }

        return $ids;
    }

    // getEpisodes gets all episodes from the passed array.
    private function getEpisodes(array $episodesEN, array $episodesFR): array
    {
        $listEpisodes = [];

        foreach ($episodesEN as $i => $episode) {
            $image = '';
            if ($episode['still_path'] != '') {
                $image = config('tmdb.imageURL').'/w500'.$episode['still_path'];
            }

            array_push(
                $listEpisodes,
                new TMDBEpisode(
                    new Episode([
                        'tmdb_id' => $episode['id'],
                        'numero' => $episode['episode_number'],
                        'name' => $episode['name'],
                        'name_fr' => $episodesFR[$i]['name'],
                        'resume' => $episode['overview'],
                        'resume_fr' => $episodesFR[$i]['overview'],
                        'diffusion_us' => $episode['air_date'],
                        'diffusion_fr' => $episodesFR[$i]['air_date'],
                        'picture' => $image,
                    ]),
                    $this->buildGuests($episode['guest_stars']),
                    $this->buildCrew($episode['crew'], 'Writer'),
                    $this->buildCrew($episode['crew'], 'Director'),
                ),
            );
        }

        return $listEpisodes;
    }

    // buildGenres builds the genres.
    private function buildGenres(array $genres): array
    {
        $listGenres = [];
        foreach ($genres as $i => $genre) {
            array_push($listGenres, $genre['name']);
        }

        return $listGenres;
    }

    // buildCreators builds the creators.
    private function buildCreators(array $creators): array
    {
        $listCreators = [];
        foreach ($creators as $creator) {
            array_push($listCreators, $creator['name']);
        }

        return $listCreators;
    }

    // buildCrew builds the director and writer.
    private function buildCrew(array $artists, string $filter): array
    {
        $listArtists = [];
        foreach ($artists as $artist) {
            if (! isset($artist['job']) || ! isset($artist['name']) || $artist['job'] != $filter) {
                continue;
            }

            array_push($listArtists, $artist['name']);
        }

        return $listArtists;
    }

    // buildGuests builds the guest stars.
    private function buildGuests(array $artists): array
    {
        $listArtists = [];
        foreach ($artists as $artist) {
            if (! isset($artist['known_for_department']) || $artist['known_for_department'] != 'Acting') {
                continue;
            }

            array_push($listArtists, $artist['name']);
        }

        return $listArtists;
    }

    /**
     * buildNationalities build the nationalities.
     * TODO: Replace nationalities by ISO_3166_1 everywhere.
     */
    private function buildNationalities(array $nationalities): array
    {
        $listNationalities = [];
        foreach ($nationalities as $i => $nationality) {
            array_push(
                $listNationalities,
                $nationality['iso_3166_1']
            );
        }

        return $listNationalities;
    }

    // buildChannels builds the channels.
    private function buildChannels(array $channels): array
    {
        $listChannels = [];
        foreach ($channels as $channel) {
            array_push($listChannels, $channel['name']);
        }

        return $listChannels;
    }
}
