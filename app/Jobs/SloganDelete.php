<?php

namespace App\Jobs;

use App\Repositories\SloganRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class SloganDelete.
 */
class SloganDelete implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $sloganID;

    protected $userID;

    /**
     * Create a new job instance.
     *
     * @param $sloganID
     * @param $userID
     */
    public function __construct($sloganID, $userID)
    {
        $this->sloganID = $sloganID;
        $this->userID = $userID;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SloganRepository $sloganRepository)
    {
        $slogan = $sloganRepository->getSloganByID($this->sloganID);

        $idLog = initJob($this->userID, 'Suppression', 'Slogan', $this->sloganID);

        $logMessage = '>> Slogan '.$slogan->message;
        saveLogMessage($idLog, $logMessage);

        // On le supprime
        $logMessage = '>>> Suppression du slogan';
        saveLogMessage($idLog, $logMessage);
        $slogan->delete();

        endJob($idLog);
    }
}
