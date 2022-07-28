<?php

namespace App\Jobs;

use App\Models\Show;
use App\Models\Temp;
use Carbon\Carbon;
use ErrorException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Class ShowUpdateFromTVDB.
 */
class ShowUpdateFromTVDB extends Job implements ShouldQueue
{
    use InteractsWithQueue;
    use SerializesModels;

    /**
     * ShowUpdateFromTVDB constructor.
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::debug('ShowUpdateFromTVDB: Start a job.');

        $secondsWeek = 604800;

        $lastUpdate = Temp::where('key', 'last_update')->first();
        $lastUpdateTimestamp = $lastUpdate->value;
        // Set time of next update to lastupdate as a base
        $nextUpdate = Carbon::now()->timestamp;

        try {
            $listUpdate = apiTvdbGetListUpdate($lastUpdateTimestamp);
        } catch (GuzzleException | ErrorException $e) {
            Log::error('ShowUpdateFromTVDB: Impossible to get list of updates.');
        }

        if (! empty($listUpdate)) {
            $listUpdate = $listUpdate->data;

            foreach ($listUpdate as $show) {
                $inputs = [
                    'thetvdb_id' => $show->id,
                ];

                // Update value of next Update if the date of update of this show is greater than $nextupdate
                $showUpdate = $show->lastUpdated;
                if ($showUpdate >= $nextUpdate) {
                    $nextUpdate = $showUpdate;
                }

                // Update the show if it exists on our BDD
                $showBdd = Show::where('thetvdb_id', $inputs['thetvdb_id'])->first();
                if (! is_null($showBdd)) {
                    createorUpdateShow($inputs);
                }
            }
        }

        $deltaLastNext = $nextUpdate - $lastUpdateTimestamp;

        if ($deltaLastNext >= $secondsWeek) {
            $nextUpdate = $lastUpdateTimestamp + $secondsWeek;
        }

        $lastUpdate->value = $nextUpdate;
        $lastUpdate->save();

        Log::debug('ShowUpdateFromTVDB: Enf of job.');
    }
}
