<?php

namespace App\Jobs;

use App\Models\Show;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class ArtistUnlink.
 */
class ArtistUnlink implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $show;

    protected $artistID;

    protected $userID;

    /**
     * Create a new job instance.
     *
     * @param $artistID
     * @param $userID
     *
     * @internal param $artistID
     * @internal param $idLog
     */
    public function __construct(Show $show, $artistID, $userID)
    {
        $this->show = $show;
        $this->artistID = $artistID;
        $this->userID = $userID;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $idLog = initJob($this->userID, 'Suppression', 'Artist', $this->artistID);

        $this->show->artists()->detach($this->artistID);

        $logMessage = 'Détachement de l\'artiste '.$this->artistID.' de la série '.$this->show['name'];
        saveLogMessage($idLog, $logMessage);

        endJob($idLog);
    }
}
