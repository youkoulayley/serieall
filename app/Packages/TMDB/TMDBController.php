<?php

namespace App\Packages\TMDB;

use App\Models\Channel;
use App\Models\Episode;
use App\Models\Genre;
use App\Models\Nationality;
use App\Models\Season;
use App\Models\Show;
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

    // getShow gets a specific show.
    public function getShow(string $id): TMDBShow
    {
        $TMDBShowEN = $this->client->getTvApi()->getTvshow($id, ['language' => 'en']);
        $TMDBShowFR = $this->client->getTvApi()->getTvshow($id, ['language' => 'fr']);
        var_dump($TMDBShowEN);
        var_dump($TMDBShowFR);

        $genres = $this->buildGenres($TMDBShowFR['genres']);
        $creators = $this->buildCreators($TMDBShowEN['created_by']);
        $nationalities = $this->buildNationalities($TMDBShowEN['production_countries']);
        $channels = $this->buildChannels($TMDBShowEN['networks']);

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
            $genres,
            $creators,
            $nationalities,
            $channels,
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

            array_push($listActors, new TMDBArtist(
                $actor['name'],
                Str::slug($actor['name']),
                'actor',
                $actor['character'],
            ));
        }

        return $listActors;
    }

    // getSeasonsByShow gets all the seasons for a specific show.
    public function getSeasonsByShow(string $id, int $seasonsCount): array
    {
        $listSeasons = [];

        // Don't get specials episodes (i starts at 1)
        for ($i = 1; $i <= $seasonsCount; ++$i) {
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

    // getEpisodes gets all episodes from the passed array.
    private function getEpisodes(array $episodesEN, array $episodesFR): array
    {
        $listEpisodes = [];

        foreach ($episodesEN as $i => $episode) {
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
                        'picture' => config('tmdb.imageURL').'/w500'.$episode['still_path'],
                    ]),
                    $this->buildArtists($episode['crew'], 'director', 'Directing'),
                    $this->buildArtists($episode['crew'], 'writer', 'Writing'),
                    $this->buildArtists($episode['guest_stars'], 'writer', 'Acting'),
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
            array_push(
                $listGenres,
                new Genre([
                    'name' => $genre['name'],
                    'genre_url' => Str::slug($genre['name']),
                ])
            );
        }

        return $listGenres;
    }

    // buildCreators builds the creators.
    private function buildCreators(array $creators): array
    {
        $listCreators = [];
        foreach ($creators as $i => $creator) {
            array_push(
                $listCreators,
                new TMDBArtist(
                    $creator['name'],
                    Str::slug($creator['name']),
                    'creator',
                    '',
                )
            );
        }

        return $listCreators;
    }

    // buildArtists builds the artists.
    private function buildArtists(array $artists, string $profession, string $filter): array
    {
        $listArtists = [];
        foreach ($artists as $i => $artist) {
            if ($artist['known_for_department'] != $filter) {
                continue;
            }

            array_push(
                $listArtists,
                new TMDBArtist(
                    $artist['name'],
                    Str::slug($artist['name']),
                    $profession,
                    '',
                )
            );
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
                new Nationality([
                    'name' => $nationality['iso_3166_1'],
                    'nationality_url' => Str::slug($nationality['iso_3166_1']),
                ])
            );
        }

        return $listNationalities;
    }

    // buildChannels builds the channels.
    private function buildChannels(array $channels): array
    {
        $listChannels = [];
        foreach ($channels as $i => $channel) {
            array_push(
                $listChannels,
                new Channel([
                    'name' => $channel['name'],
                    'channel_url' => Str::slug($channel['name']),
                ])
            );
        }

        return $listChannels;
    }
}
