<?php

namespace App\Jobs;

use App\Models\Artist;
use App\Models\Channel;
use App\Models\Episode;
use App\Models\Genre;
use App\Models\Nationality;
use App\Models\Season;
use App\Models\Show;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/**
 * Class ShowAddManually.
 */
class ShowAddManually implements ShouldQueue
{
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
     */
    public function handle()
    {
        /*
         |--------------------------------------------------------------------------
         | Initialisation du Job
         |--------------------------------------------------------------------------
         */
        $idLog = initJob($this->inputs['user_id'], 'Ajout Manuel', 'Show', mt_rand());

        /*
        |--------------------------------------------------------------------------
        | Ajout des informations sur la saison
        |--------------------------------------------------------------------------
        */

        $show = new Show();
        $logMessage = '>SERIE';
        saveLogMessage($idLog, $logMessage);

        $show->name = $this->inputs['name'];
        // Nom original de la série
        $logMessage = '>>Nom original : '.$show->name;
        saveLogMessage($idLog, $logMessage);

        $show->name_fr = $this->inputs['name_fr'];
        // Nom FR de la série
        $logMessage = '>>Nom FR : '.$show->name_fr;
        saveLogMessage($idLog, $logMessage);

        $show->synopsis = $this->inputs['resume'];
        // Résumé de la série
        $logMessage = '>>Résumé original : '.$show->synopsis;
        saveLogMessage($idLog, $logMessage);

        $show->synopsis_fr = $this->inputs['resume_fr'];
        // Résumé de la série
        $logMessage = '>>Résumé FR : '.$show->synopsis_fr;
        saveLogMessage($idLog, $logMessage);

        $show->format = $this->inputs['format'];
        // Format de la série
        $logMessage = '>>Format : '.$show->format;
        saveLogMessage($idLog, $logMessage);

        $show->encours = $this->inputs['encours'];
        // La série est en cours ?
        $logMessage = '>>>En cours : '.$show->encours;
        saveLogMessage($idLog, $logMessage);

        $show->diffusion_us = $this->inputs['diffusion_us'];
        // Diffusion US de la série
        $logMessage = '>>Diffusion US : '.$show->diffusion_us;
        saveLogMessage($idLog, $logMessage);

        $dateTemp = date_create($show->diffusion_us);
        $show->annee = date_format($dateTemp, 'Y');
        // Année de diffusion de la série
        $logMessage = '>>Année : '.$show->annee;
        saveLogMessage($idLog, $logMessage);

        $show->diffusion_fr = $this->inputs['diffusion_fr'];
        // Diffusion FR de la série
        $logMessage = '>>Diffusion FR : '.$show->diffusion_fr;
        saveLogMessage($idLog, $logMessage);

        $show->particularite = $this->inputs['particularite'];
        // Particularité de la série
        $logMessage = '>>>>Particularité : '.$show->particularite;
        saveLogMessage($idLog, $logMessage);

        $show->taux_erectile = $this->inputs['taux_erectile'];
        // Taux Erectile
        $logMessage = '>>Taux érectile: '.$show->taux_erectile;
        saveLogMessage($idLog, $logMessage);

        $show->avis_rentree = $this->inputs['avis_rentree'];
        // Avis sur la série
        $logMessage = '>>Avis : '.$show->avis_rentree;
        saveLogMessage($idLog, $logMessage);

        $show->show_url = Str::slug($show->name);
        //Utilisation de la méthode Slug pour l'URL
        $logMessage = '>>URL : '.$show->show_url;
        saveLogMessage($idLog, $logMessage);

        $show->save();

        /*
        |--------------------------------------------------------------------------
        | Ajout des informations sur les genres de la série
        |--------------------------------------------------------------------------
        */

        $genres = $this->inputs['genres'];

        if (! empty($genres)) {
            $logMessage = '>>GENRES';
            saveLogMessage($idLog, $logMessage);

            $genres = explode(',', $genres);
            // Pour chaque genre
            foreach ($genres as $genre) {
                // On supprime les espaces
                $genre = trim($genre);
                // On met en forme l'URL
                $genre_url = Str::slug($genre);
                // On vérifie si le genre existe déjà en base
                $genre_ref = Genre::where('genre_url', $genre_url)->first();

                // Si il n'existe pas
                if (is_null($genre_ref)) {
                    $logMessage = '>>>Ajout du genre : '.$genre.'.';
                    saveLogMessage($idLog, $logMessage);
                    // On prépare le nouveau genre
                    $genre_ref = new Genre([
                        'name' => $genre,
                        'genre_url' => $genre_url,
                    ]);

                    // Et on le sauvegarde ne passant par l'objet Show pour créer le lien entre les deux
                    $show->genres()->save($genre_ref);
                } else {
                    $logMessage = '>>>Liaison du genre : '.$genre.'.';
                    saveLogMessage($idLog, $logMessage);
                    // Si il existe, on crée juste le lien
                    $show->genres()->attach($genre_ref->id);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Ajout des informations sur les chaines de la série
        |--------------------------------------------------------------------------
        */

        $channels = $this->inputs['channels'];

        if (! empty($channels)) {
            $logMessage = '>>CHAINES';
            saveLogMessage($idLog, $logMessage);

            $channels = explode(',', $channels);

            // Pour chaque chaines
            foreach ($channels as $channel) {
                // On supprime les espaces
                $channel = trim($channel);
                // On met en forme l'URL
                $channel_url = Str::slug($channel);
                // On vérifie si la nationalité existe déjà en base
                $channel_ref = Channel::where('channel_url', $channel_url)->first();

                // Si elle n'existe pas
                if (is_null($channel_ref)) {
                    $logMessage = '>>>Ajout de la chaine : '.$channel.'.';
                    saveLogMessage($idLog, $logMessage);
                    // On prépare la nouvelle nationalité
                    $channel_ref = new Channel([
                        'name' => $channel,
                        'channel_url' => $channel_url,
                    ]);

                    // Et on la sauvegarde en passant par l'objet Show pour créer le lien entre les deux
                    $show->channels()->save($channel_ref);
                } else {
                    $logMessage = '>>>Liaison de la chaine : '.$channel.'.';
                    saveLogMessage($idLog, $logMessage);
                    // Si elle existe, on crée juste le lien
                    $show->channels()->attach($channel_ref->id);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Ajout des informations sur les nationalités de la série
        |--------------------------------------------------------------------------
        */

        $nationalities = $this->inputs['nationalities'];

        if (! empty($nationalities)) {
            $logMessage = '>>NATIONALITES';
            saveLogMessage($idLog, $logMessage);

            $nationalities = explode(',', $nationalities);

            // Pour chaque nationalité
            foreach ($nationalities as $nationality) {
                // On supprime les espaces
                $nationality = trim($nationality);
                // On met en forme l'URL
                $nationality_url = Str::slug($nationality);
                // On verified si la nationalité existe déjà en base
                $nationality_ref = Nationality::where('nationality_url', $nationality_url)->first();

                // Si elle n'existe pas
                if (is_null($nationality_ref)) {
                    $logMessage = '>>>Ajout de la nationalité : '.$nationality.'.';
                    saveLogMessage($idLog, $logMessage);
                    // On prépare la nouvelle nationalité
                    $nationality_ref = new Nationality([
                        'name' => $nationality,
                        'nationality_url' => $nationality_url,
                    ]);

                    // Et on la sauvegarde en passant par l'objet Show pour créer le lien entre les deux
                    $show->nationalities()->save($nationality_ref);
                } else {
                    $logMessage = '>>>Liaison de la nationalité : '.$nationality.'.';
                    saveLogMessage($idLog, $logMessage);
                    // Si elle existe, on crée juste le lien
                    $show->nationalities()->attach($nationality_ref->id);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Ajout des informations sur les créateurs de la série
        |--------------------------------------------------------------------------
        */

        $creators = $this->inputs['creators'];

        if (! empty($creators)) {
            $logMessage = '>>CREATEURS';
            saveLogMessage($idLog, $logMessage);

            $creators = explode(',', $creators);

            // Pour chaque créateur
            foreach ($creators as $creator) {
                // On supprime les espaces
                $creator = trim($creator);
                // On met en forme l'URL
                $creator_url = Str::slug($creator);
                // On vérifie si le genre existe déjà en base
                $creator_ref = Artist::where('artist_url', $creator_url)->first();

                // Si il n'existe pas
                if (is_null($creator_ref)) {
                    $logMessage = '>>>Ajout du créateur : '.$creator.'.';
                    saveLogMessage($idLog, $logMessage);
                    // On prépare le nouveau créateur
                    $creator_ref = new Artist([
                        'name' => $creator,
                        'artist_url' => $creator_url,
                    ]);

                    // Et on le sauvegarde en passant par l'objet Show pour créer le lien entre les deux
                    $show->artists()->save($creator_ref, ['profession' => 'creator']);
                } else {
                    $logMessage = '>>>Liaison du créateur : '.$creator.'.';
                    saveLogMessage($idLog, $logMessage);
                    // Si il existe, on crée juste le lien
                    $show->artists()->attach($creator_ref->id, ['profession' => 'creator']);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Ajout des informations sur les acteurs de la série
        |--------------------------------------------------------------------------
        */
        if (isset($this->inputs['actors'])) {
            $actors = $this->inputs['actors'];
            $logMessage = '>>ACTEURS';
            saveLogMessage($idLog, $logMessage);

            foreach ($actors as $actor) {
                // Récupération du nom de l'acteur
                $actorName = $actor['name_actor'];

                // Récupération du rôle
                $actorRole = $actor['role_actor'];

                // On supprime les espaces
                $actor = trim($actorName);
                // On met en forme l'URL
                $actor_url = Str::slug($actor);
                // Vérification de la présence de l'acteur
                $actor_ref = Artist::where('artist_url', $actor_url)->first();

                // Si elle n'existe pas
                if (is_null($actor_ref)) {
                    $logMessage = '>>>Création de l\'acteur : '.$actorName.'.';
                    saveLogMessage($idLog, $logMessage);
                    // On prépare la nouvelle saison
                    $actor_ref = new Artist([
                        'name' => $actor,
                        'artist_url' => $actor_url,
                    ]);

                    // Et on la sauvegarde en passant par l'objet Show pour créer le lien entre les deux
                    $show->artists()->save($actor_ref, ['profession' => 'actor', 'role' => $actorRole]);
                } else {
                    $logMessage = '>>>Liaison de l\'acteur : '.$actorName.'.';
                    saveLogMessage($idLog, $logMessage);
                    // Si il existe, on crée juste le lien
                    $show->artists()->attach($actor_ref->id, ['profession' => 'actor', 'role' => $actorRole]);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Add information on seasons and episodes
        |--------------------------------------------------------------------------
        */

        if (isset($this->inputs['seasons'])) {
            $seasons = $this->inputs['seasons'];
            $logMessage = '>>SAISONS';
            saveLogMessage($idLog, $logMessage);

            foreach ($seasons as $season) {
                $season_new = new Season();

                // Récupération des informations sur la saison
                $season_new->name = $season['number'];
                // Numéro de la saison
                $logMessage = '>>>Numéro : '.$season_new->name;
                saveLogMessage($idLog, $logMessage);

                $season_new->ba = $season['ba'];
                // Bande annonce de la saison
                $logMessage = '>>>Bande Annonce : '.$season_new->ba;
                saveLogMessage($idLog, $logMessage);

                // Enregistrement de la saison
                $season_new->show()->associate($show);
                $season_new->save();

                // Ajout des épisodes
                if (isset($season['episodes'])) {
                    foreach ($season['episodes'] as $episode) {
                        $episode_new = new Episode();
                        $logMessage = '>>>EPISODES';
                        saveLogMessage($idLog, $logMessage);

                        $episode_new->numero = $episode['number'];
                        // Numéro de l'épisode
                        $logMessage = '>>>>Numéro : '.$episode_new->numero;
                        saveLogMessage($idLog, $logMessage);

                        $episode_new->name = $episode['name'];
                        // Nom original de l'épisode
                        $logMessage = '>>>>Nom Original : '.$episode_new->name;
                        saveLogMessage($idLog, $logMessage);

                        $episode_new->name_fr = $episode['name_fr'];
                        // Nom français de l'épisode
                        $logMessage = '>>>>Nom français : '.$episode_new->name_fr;
                        saveLogMessage($idLog, $logMessage);

                        $episode_new->resume = $episode['resume'];
                        // Résumé de l'épisode
                        $logMessage = '>>>>Résumé : '.$episode_new->resume;
                        saveLogMessage($idLog, $logMessage);

                        $episode_new->resume_fr = $episode['resume_fr'];
                        // Résumé français de l'épisode
                        $logMessage = '>>>>Résumé français : '.$episode_new->resume_fr;
                        saveLogMessage($idLog, $logMessage);

                        $episode_new->diffusion_us = $episode['diffusion_us'];
                        // Diffusion originale de l'épisode
                        $logMessage = '>>>>Diffusion Originale : '.$episode_new->diffusion_us;
                        saveLogMessage($idLog, $logMessage);

                        $episode_new->diffusion_fr = $episode['diffusion_fr'];
                        // Diffusion française de l'épisode
                        $logMessage = '>>>>Diffusion française : '.$episode_new->diffusion_fr;
                        saveLogMessage($idLog, $logMessage);

                        $episode_new->ba = $episode['ba'];
                        // Bande annonce de l'épisode
                        $logMessage = '>>>>Bande Annonce : '.$episode_new->name;
                        saveLogMessage($idLog, $logMessage);

                        $episode_new->season()->associate($season_new);
                        $episode_new->save();

                        /*
                        |--------------------------------------------------------------------------
                        | Ajout des informations sur les réalisateurs de l'épisode
                        |--------------------------------------------------------------------------
                        */
                        if (! empty($episode['directors'])) {
                            $directors = $episode['directors'];
                            $directors = explode(',', $directors);
                            $logMessage = '>>>>REALISATEURS';
                            saveLogMessage($idLog, $logMessage);

                            // Pour chaque créateur
                            foreach ($directors as $director) {
                                // On supprime les espaces
                                $director = trim($director);
                                // On met en forme l'URL
                                $director_url = Str::slug($director);
                                // On vérifie si le genre existe déjà en base
                                $director_ref = Artist::where('artist_url', $director_url)->first();

                                // Si il n'existe pas
                                if (is_null($director_ref)) {
                                    $logMessage = '>>>>>Ajout du réalisateur : '.$director.'.';
                                    saveLogMessage($idLog, $logMessage);
                                    // On prépare le nouveau créateur
                                    $director_ref = new Artist([
                                        'name' => $director,
                                        'artist_url' => $director_ref,
                                    ]);

                                    // Et on le sauvegarde en passant par l'objet Show pour créer le lien entre les deux
                                    $episode_new->artists()->save($director_ref, ['profession' => 'director']);
                                } else {
                                    $logMessage = '>>>>>Liaison du réalisateur : '.$director.'.';
                                    saveLogMessage($idLog, $logMessage);
                                    // Si il existe, on crée juste le lien
                                    $episode_new->artists()->attach($director_ref->id, ['profession' => 'director']);
                                }
                            }
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Ajout des informations sur les scénaristes de l'épisode
                        |--------------------------------------------------------------------------
                        */
                        if (! empty($episode['writers'])) {
                            $writers = $episode['writers'];
                            $writers = explode(',', $writers);
                            $logMessage = '>>>>SCENARISTES';
                            saveLogMessage($idLog, $logMessage);

                            // Pour chaque créateur
                            foreach ($writers as $writer) {
                                // On supprime les espaces
                                $writer = trim($writer);
                                // On met en forme l'URL
                                $writer_url = Str::slug($writer);
                                // On vérifie si le genre existe déjà en base
                                $writer_ref = Artist::where('artist_url', $writer_url)->first();

                                // Si il n'existe pas
                                if (is_null($writer_ref)) {
                                    $logMessage = '>>>>>Ajout du réalisateur : '.$writer.'.';
                                    saveLogMessage($idLog, $logMessage);
                                    // On prépare le nouveau créateur
                                    $writer_ref = new Artist([
                                        'name' => $writer,
                                        'artist_url' => $writer_ref,
                                    ]);

                                    // Et on le sauvegarde en passant par l'objet Show pour créer le lien entre les deux
                                    $episode_new->artists()->save($writer_ref, ['profession' => 'writer']);
                                } else {
                                    $logMessage = '>>>>>Liaison du réalisateur : '.$writer.'.';
                                    saveLogMessage($idLog, $logMessage);
                                    // Si il existe, on crée juste le lien
                                    $episode_new->artists()->attach($writer_ref->id, ['profession' => 'writer']);
                                }
                            }
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Ajout des informations sur les guests de l'épisode
                        |--------------------------------------------------------------------------
                        */
                        if (! empty($episode['guests'])) {
                            $guests = $episode['guests'];
                            $guests = explode(',', $guests);
                            $logMessage = '>>>>GUESTS';
                            saveLogMessage($idLog, $logMessage);

                            // Pour chaque créateur
                            foreach ($guests as $guest) {
                                // On supprime les espaces
                                $guest = trim($guest);
                                // On met en forme l'URL
                                $guest_url = Str::slug($guest);
                                // On vérifie si le genre existe déjà en base
                                $guest_ref = Artist::where('artist_url', $guest_url)->first();

                                // Si il n'existe pas
                                if (is_null($guest_ref)) {
                                    $logMessage = '>>>>>Ajout du réalisateur : '.$guest.'.';
                                    saveLogMessage($idLog, $logMessage);
                                    // On prépare le nouveau créateur
                                    $guest_ref = new Artist([
                                        'name' => $guest,
                                        'artist_url' => $guest_ref,
                                    ]);

                                    // Et on le sauvegarde en passant par l'objet Show pour créer le lien entre les deux
                                    $episode_new->artists()->save($guest_ref, ['profession' => 'writer']);
                                } else {
                                    $logMessage = '>>>>>Liaison du réalisateur : '.$guest.'.';
                                    saveLogMessage($idLog, $logMessage);
                                    // Si il existe, on crée juste le lien
                                    $episode_new->artists()->attach($guest_ref->id, ['profession' => 'writer']);
                                }
                            }
                        }
                    }
                }

                // Ajout des épisodes spéciaux
                if (isset($season['episodesSpeciaux'])) {
                    foreach ($season['episodesSpeciaux'] as $episode) {
                        $episode_new = new Episode();
                        $logMessage = '>>>EPISODES SPECIAUX';
                        saveLogMessage($idLog, $logMessage);

                        $episode_new->numero = $episode['number'];
                        // Numéro de l'épisode
                        $logMessage = '>>>>Numéro : '.$episode_new->numero;
                        saveLogMessage($idLog, $logMessage);

                        $episode_new->name = $episode['name'];
                        // Nom original de l'épisode
                        $logMessage = '>>>>Nom Original : '.$episode_new->name;
                        saveLogMessage($idLog, $logMessage);

                        $episode_new->name_fr = $episode['name_fr'];
                        // Nom français de l'épisode
                        $logMessage = '>>>>Nom français : '.$episode_new->name_fr;
                        saveLogMessage($idLog, $logMessage);

                        $episode_new->resume = $episode['resume'];
                        // Résumé de l'épisode
                        $logMessage = '>>>>Résumé : '.$episode_new->resume;
                        saveLogMessage($idLog, $logMessage);

                        $episode_new->resume_fr = $episode['resume_fr'];
                        // Résumé français de l'épisode
                        $logMessage = '>>>>Résumé français : '.$episode_new->resume_fr;
                        saveLogMessage($idLog, $logMessage);

                        $episode_new->diffusion_us = $episode['diffusion_us'];
                        // Diffusion originale de l'épisode
                        $logMessage = '>>>>Diffusion Originale : '.$episode_new->diffusion_us;
                        saveLogMessage($idLog, $logMessage);

                        $episode_new->diffusion_fr = $episode['diffusion_fr'];
                        // Diffusion française de l'épisode
                        $logMessage = '>>>>Diffusion française : '.$episode_new->diffusion_fr;
                        saveLogMessage($idLog, $logMessage);

                        $episode_new->ba = $episode['ba'];
                        // Bande annonce de l'épisode
                        $logMessage = '>>>>Bande Annonce : '.$episode_new->name;
                        saveLogMessage($idLog, $logMessage);

                        $episode_new->season()->associate($season_new);
                        $episode_new->save();

                        /*
                        |--------------------------------------------------------------------------
                        | Ajout des informations sur les réalisateurs de l'épisode
                        |--------------------------------------------------------------------------
                        */
                        if (! empty($episode['directors'])) {
                            $directors = $episode['directors'];
                            $directors = explode(',', $directors);
                            $logMessage = '>>>>REALISATEURS';
                            saveLogMessage($idLog, $logMessage);

                            // Pour chaque créateur
                            foreach ($directors as $director) {
                                // On supprime les espaces
                                $director = trim($director);
                                // On met en forme l'URL
                                $director_url = Str::slug($director);
                                // On vérifie si le genre existe déjà en base
                                $director_ref = Artist::where('artist_url', $director_url)->first();

                                // Si il n'existe pas
                                if (is_null($director_ref)) {
                                    $logMessage = '>>>>>Ajout du réalisateur : '.$director.'.';
                                    saveLogMessage($idLog, $logMessage);
                                    // On prépare le nouveau créateur
                                    $director_ref = new Artist([
                                        'name' => $director,
                                        'artist_url' => $director_ref,
                                    ]);

                                    // Et on le sauvegarde en passant par l'objet Show pour créer le lien entre les deux
                                    $episode_new->artists()->save($director_ref, ['profession' => 'director']);
                                } else {
                                    $logMessage = '>>>>>Liaison du réalisateur : '.$director.'.';
                                    saveLogMessage($idLog, $logMessage);
                                    // Si il existe, on crée juste le lien
                                    $episode_new->artists()->attach($director_ref->id, ['profession' => 'director']);
                                }
                            }
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Ajout des informations sur les scénaristes de l'épisode
                        |--------------------------------------------------------------------------
                        */
                        if (! empty($episode['writers'])) {
                            $writers = $episode['writers'];
                            $writers = explode(',', $writers);
                            $logMessage = '>>>>SCENARISTES';
                            saveLogMessage($idLog, $logMessage);

                            // Pour chaque créateur
                            foreach ($writers as $writer) {
                                // On supprime les espaces
                                $writer = trim($writer);
                                // On met en forme l'URL
                                $writer_url = Str::slug($writer);
                                // On vérifie si le genre existe déjà en base
                                $writer_ref = Artist::where('artist_url', $writer_url)->first();

                                // Si il n'existe pas
                                if (is_null($writer_ref)) {
                                    $logMessage = '>>>>>Ajout du réalisateur : '.$writer.'.';
                                    saveLogMessage($idLog, $logMessage);
                                    // On prépare le nouveau créateur
                                    $writer_ref = new Artist([
                                        'name' => $writer,
                                        'artist_url' => $writer_ref,
                                    ]);

                                    // Et on le sauvegarde en passant par l'objet Show pour créer le lien entre les deux
                                    $episode_new->artists()->save($writer_ref, ['profession' => 'writer']);
                                } else {
                                    $logMessage = '>>>>>Liaison du réalisateur : '.$writer.'.';
                                    saveLogMessage($idLog, $logMessage);
                                    // Si il existe, on crée juste le lien
                                    $episode_new->artists()->attach($writer_ref->id, ['profession' => 'writer']);
                                }
                            }
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Ajout des informations sur les guests de l'épisode
                        |--------------------------------------------------------------------------
                        */
                        if (! empty($episode['guests'])) {
                            $guests = $episode['guests'];
                            $guests = explode(',', $guests);
                            $logMessage = '>>>>GUESTS';
                            saveLogMessage($idLog, $logMessage);

                            // Pour chaque créateur
                            foreach ($guests as $guest) {
                                // On supprime les espaces
                                $guest = trim($guest);
                                // On met en forme l'URL
                                $guest_url = Str::slug($guest);
                                // On vérifie si le genre existe déjà en base
                                $guest_ref = Artist::where('artist_url', $guest_url)->first();

                                // Si il n'existe pas
                                if (is_null($guest_ref)) {
                                    $logMessage = '>>>>>Ajout du réalisateur : '.$guest.'.';
                                    saveLogMessage($idLog, $logMessage);
                                    // On prépare le nouveau créateur
                                    $guest_ref = new Artist([
                                        'name' => $guest,
                                        'artist_url' => $guest_ref,
                                    ]);

                                    // Et on le sauvegarde en passant par l'objet Show pour créer le lien entre les deux
                                    $episode_new->artists()->save($guest_ref, ['profession' => 'writer']);
                                } else {
                                    $logMessage = '>>>>>Liaison du réalisateur : '.$guest.'.';
                                    saveLogMessage($idLog, $logMessage);
                                    // Si il existe, on crée juste le lien
                                    $episode_new->artists()->attach($guest_ref->id, ['profession' => 'writer']);
                                }
                            }
                        }
                    }
                }
            }
        }

        endJob($idLog);
    }
}
