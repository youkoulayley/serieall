<?php

namespace App\Jobs;

use App\Models\Artist;
use App\Models\Show;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/**
 * Class ArtistStore.
 */
class ArtistStore implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $actorName;

    protected $actorRole;

    protected $show;

    protected $listLogID;

    /**
     * Create a new job instance.
     *
     * @param $actorName
     * @param $actorRole
     * @param $listLogID
     */
    public function __construct($actorName, $actorRole, Show $show, $listLogID)
    {
        $this->actorName = $actorName;
        $this->actorRole = $actorRole;
        $this->show = $show;
        $this->listLogID = $listLogID;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // On supprime les espaces
        $actor_url = trim($this->actorName);
        // On met en forme l'URL
        $actor_url = Str::slug($actor_url);

        // Vérification de la présence de l'acteur
        $actor_ref = Artist::where('artist_url', $actor_url)::first();

        // Si elle n'existe pas
        if (is_null($actor_ref)) {
            // On prépare le nouvel acteur
            $actor_ref = new Artist([
                'name' => $this->actorName,
                'artist_url' => $actor_url,
            ]);

            $logMessage = '> Création de l\'acteur '.$this->actorName;
            saveLogMessage($this->listLogID, $logMessage);

            // Et on la sauvegarde en passant par l'objet Show pour créer le lien entre les deux
            $this->show->artists()->save($actor_ref, ['profession' => 'actor', 'role' => $this->actorRole]);
        } else {
            // Si il existe, on vérifie qu'il n'a pas déjà un lien avec la série
            $actor_liaison = $actor_ref->shows()
                ->where('shows.tmdb_id', $this->show['tmdb_id'])
                ->where('artistables.profession', 'actor')
                ->get()
                ->toArray();

            if (empty($actor_liaison)) {
                // On lie l'acteur à la série
                $logMessage = '> Liaison de l\'acteur '.$this->actorName;
                saveLogMessage($this->listLogID, $logMessage);

                $this->show->artists()->attach($actor_ref->id, ['profession' => 'actor', 'role' => $this->actorRole]);
            } else {
                // On met à jour le rôle et la photo
                $logMessage = '> Mise à jour du rôle de '.$this->actorName;
                saveLogMessage($this->listLogID, $logMessage);
                $actor_ref->shows()->updateExistingPivot($this->show->id, ['role' => $this->actorRole]);
            }
        }
    }
}
