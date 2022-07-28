<?php

namespace App\Jobs;

use App\Models\Artist;
use App\Repositories\EpisodeRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/**
 * Class EpisodeUpdate.
 */
class EpisodeUpdate implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
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

    /**
     * Execute the job.
     *
     * @return void
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function handle(EpisodeRepository $episodeRepository)
    {
        /*
        |--------------------------------------------------------------------------
        | Initialisation du job
        |--------------------------------------------------------------------------
        */

        $idLog = initJob($this->inputs['user_id'], 'Mise à jour', 'Episode', $this->inputs['id']);

        /*
        |--------------------------------------------------------------------------
        | Récupération des informations du formulaire
        |--------------------------------------------------------------------------
        */
        $episode = $episodeRepository->getEpisodeByID($this->inputs['id']);

        $logMessage = '>EPISODE';
        saveLogMessage($idLog, $logMessage);

        //Store tvdbid only if not empty
        //If empty tvdb id, uniq constraint violation on it and nothing resistered
        if (isset($this->inputs['tmdb_id']) && ! empty($this->inputs['tmdb_id'])) {
            $episode->tmdb_id = $this->inputs['tmdb_id'];

            // TVDB ID de l'épisode
            $logMessage = '>>TMDB ID : '.$episode->tmdb_id;
            saveLogMessage($idLog, $logMessage);
        }

        $episode->numero = $this->inputs['numero'];
        // Numéro de l'épisode
        $logMessage = '>>Numéro : '.$episode->numero;
        saveLogMessage($idLog, $logMessage);

        $episode->season_id = $this->inputs['season_id'];
        // Saison de l'épisode
        $logMessage = '>>Saison : '.$episode->season_id;
        saveLogMessage($idLog, $logMessage);

        $episode->name = $this->inputs['name'];
        // Nom FR de l'épisode
        $logMessage = '>>Nom FR : '.$episode->name;
        saveLogMessage($idLog, $logMessage);

        $episode->name_fr = $this->inputs['name_fr'];
        // Nom FR de l'épisode
        $logMessage = '>>Nom FR : '.$episode->name_fr;
        saveLogMessage($idLog, $logMessage);

        $episode->resume = $this->inputs['resume'];
        // Résumé EN de l'épisode
        $logMessage = '>>Résumé FR : '.$episode->resume;
        saveLogMessage($idLog, $logMessage);

        $episode->resume_fr = $this->inputs['resume_fr'];
        // Résumé FR de la série
        $logMessage = '>>Résumé FR : '.$episode->resume_fr;
        saveLogMessage($idLog, $logMessage);

        $episode->diffusion_fr = $this->inputs['diffusion_fr'];
        // Diffusion FR de l'épisode
        $logMessage = '>>Diffusion FR : '.$episode->diffusion_fr;
        saveLogMessage($idLog, $logMessage);

        $episode->diffusion_us = $this->inputs['diffusion_us'];
        // Diffusion US de l'épisode
        $logMessage = '>>Diffusion US : '.$episode->diffusion_us;
        saveLogMessage($idLog, $logMessage);

        $episode->ba = $this->inputs['ba'];
        // Bande Annonce de l'épisode
        $message = 'Bande Annonce : '.$this->inputs['ba'];
        saveLogMessage($idLog, $message);

        $episode->save();

        /*
        |--------------------------------------------------------------------------
        | Récupérations des informations sur les directors
        |--------------------------------------------------------------------------
        */
        $directors = $this->inputs['directors'];

        if (empty($directors)) {
            $episode->directors()->sync([]);
        } else {
            $logMessage = '>>REALISATEURS';
            saveLogMessage($idLog, $logMessage);
            $directors = explode(',', $directors);

            // Pour chaque réalisateur
            foreach ($directors as $director) {
                // On supprime les espaces
                $director = trim($director);
                // On met en forme l'URL
                $director_url = Str::slug($director);
                // On vérifie si le réalisateur existe déjà en base
                $director_ref = Artist::where('artist_url', $director_url)->first();

                // Si il n'existe pas
                if (is_null($director_ref)) {
                    $logMessage = '>>>Ajout du réalisateur : '.$director.'.';
                    saveLogMessage($idLog, $logMessage);
                    // On prépare le nouveau réalisateur
                    $director_ref = new Artist([
                        'name' => $director,
                        'artist_url' => $director_url,
                    ]);

                    // Et on le sauvegarde en passant par l'objet Episode pour créer le lien entre les deux
                    $director_ref->save();
                }
                $listDirectors[] = $director_ref->id;
            }

            $pivotData = array_fill(0, count($listDirectors), ['profession' => 'director']);
            $syncData = array_combine($listDirectors, $pivotData);

            $episode->directors()->sync($syncData);
        }

        $logMessage = '>>>Synchronisation des réalisateurs.';
        saveLogMessage($idLog, $logMessage);

        /*
        |--------------------------------------------------------------------------
        | Récupérations des informations sur les scénaristes
        |--------------------------------------------------------------------------
        */
        $writers = $this->inputs['writers'];

        if (empty($writers)) {
            $episode->writers()->sync([]);
        } else {
            $logMessage = '>>SCENARISTES';
            saveLogMessage($idLog, $logMessage);
            $writers = explode(',', $writers);

            // Pour chaque scénariste
            foreach ($writers as $writer) {
                // On supprime les espaces
                $writer = trim($writer);
                // On met en forme l'URL
                $writer_url = Str::slug($writer);
                // On vérifie si le genre existe déjà en base
                $writer_ref = Artist::where('artist_url', $writer_url)::first();

                // Si il n'existe pas
                if (is_null($writer_ref)) {
                    $logMessage = '>>>Ajout du scénariste : '.$writer.'.';
                    saveLogMessage($idLog, $logMessage);
                    // On prépare le nouveau scénariste
                    $writer_ref = new Artist([
                        'name' => $writer,
                        'artist_url' => $writer_url,
                    ]);

                    // Et on le sauvegarde en passant par l'objet Show pour créer le lien entre les deux
                    $writer_ref->save();
                }
                $listWriters[] = $writer_ref->id;
            }
            $pivotData = array_fill(0, count($listWriters), ['profession' => 'writer']);
            $syncData = array_combine($listWriters, $pivotData);

            $episode->writers()->sync($syncData);
        }

        $logMessage = '>>>Synchronisation des scénaristes.';
        saveLogMessage($idLog, $logMessage);

        /*
        |--------------------------------------------------------------------------
        | Récupérations des informations sur les guests
        |--------------------------------------------------------------------------
        */
        $guests = $this->inputs['guests'];

        if (empty($guests)) {
            $episode->guests()->sync([]);
        } else {
            $logMessage = '>>GUESTS';
            saveLogMessage($idLog, $logMessage);
            $guests = explode(',', $guests);

            // Pour chaque guest
            foreach ($guests as $guest) {
                // On supprime les espaces
                $guest = trim($guest);
                // On met en forme l'URL
                $guest_url = Str::slug($guest);
                // On vérifie si le genre existe déjà en base
                $guest_ref = Artist::where('artist_url', $guest_url)::first();

                // Si il n'existe pas
                if (is_null($guest_ref)) {
                    $logMessage = '>>>Ajout du guest : '.$guest.'.';
                    saveLogMessage($idLog, $logMessage);
                    // On prépare le nouveau guest
                    $guest_ref = new Artist([
                        'name' => $guest,
                        'artist_url' => $guest_url,
                    ]);

                    // Et on le sauvegarde en passant par l'objet Show pour créer le lien entre les deux
                    $guest_ref->save();
                }
                $listGuests[] = $guest_ref->id;
            }
            $pivotData = array_fill(0, count($listGuests), ['profession' => 'guest']);
            $syncData = array_combine($listGuests, $pivotData);

            $episode->guests()->sync($syncData);
        }

        $logMessage = '>>>Synchronisation des guests.';
        saveLogMessage($idLog, $logMessage);

        endJob($idLog);
    }
}
