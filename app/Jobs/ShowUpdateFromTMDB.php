<?php

namespace App\Jobs;

use App\Packages\TMDB\TMDBController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Class ShowUpdateFromTMDB.
 */
class ShowUpdateFromTMDB extends Job implements ShouldQueue
{
    use InteractsWithQueue;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::debug('ShowUpdateFromTMDB: Start a job.');

        $tmdb = new TMDBController(config('tmdb.apiKey'));
        $ids = $tmdb->getUpdates();

        $launched = 0;
        foreach ($ids as $id) {
            $launched++;
            dispatch(new OneShowUpdateFromTMDB($id));
        }

        Log::info("ShowUpdateFromTMDB: Launched $launched jobs.", ['ids' => $ids]);

        Log::debug('ShowUpdateFromTMDB: Enf of job.');
    }
}
