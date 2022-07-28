<?php

namespace App\Jobs;

use App\Repositories\SeasonRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class SeasonUpdate.
 */
class SeasonUpdate implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $inputs;

    protected $seasonRepository;

    /**
     * Create a new job instance.
     *
     * @param $inputs
     *
     * @internal param SeasonRepository $seasonRepository
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
    public function handle(SeasonRepository $seasonRepository)
    {
        $idLog = initJob($this->inputs['user_id'], 'Edition', 'Season', $this->inputs['id']);

        $season = $seasonRepository->getSeasonByID($this->inputs['id']);

        $message = 'Mise à jour de la saison '.$this->inputs['name'].'|'.$this->inputs['id'];
        saveLogMessage($idLog, $message);

        $season->tmdb_id = $this->inputs['tmdb_id'];
        $message = 'TheTVDB ID . '.$this->inputs['tmdb_id'];
        saveLogMessage($idLog, $message);

        $season->name = $this->inputs['name'];
        $message = 'Numéro . '.$this->inputs['name'];
        saveLogMessage($idLog, $message);

        $season->ba = $this->inputs['ba'];
        $message = 'Bande Annonce . '.$this->inputs['ba'];
        saveLogMessage($idLog, $message);

        $season->save();

        endJob($idLog);
    }
}
