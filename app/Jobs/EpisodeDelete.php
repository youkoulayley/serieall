<?php

namespace App\Jobs;

use App\Repositories\EpisodeRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class EpisodeDelete.
 */
class EpisodeDelete implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $episodeID;

    protected $userID;

    /**
     * Create a new job instance.
     *
     * @param $episodeID
     * @param $userID
     */
    public function __construct($episodeID, $userID)
    {
        $this->episodeID = $episodeID;
        $this->userID = $userID;
    }

    /**
     * Execute the job.
     *
     * @return void
     *
     * @throws \Exception
     * @throws \Exception
     */
    public function handle(EpisodeRepository $episodeRepository)
    {
        $episode = $episodeRepository->getEpisodeByID($this->episodeID);

        $idLog = initJob($this->userID, 'Suppression', 'Episode', $this->episodeID);

        $logMessage = '>> EPISODE '.$episode->numero;
        saveLogMessage($idLog, $logMessage);

        // On détache les artistes
        $logMessage = '>>> Détachement des artistes';
        saveLogMessage($idLog, $logMessage);
        $episode->artists()->detach();

        // On détache les avis
        $episode->comments()->delete();

        // On détache les notes
        $episode->users()->detach();

        // On détache les articles
        $episode->articles()->detach();

        // On le supprime
        $logMessage = '>>> Suppression de l\'épisode';
        saveLogMessage($idLog, $logMessage);
        $episode->delete();

        endJob($idLog);
    }
}
