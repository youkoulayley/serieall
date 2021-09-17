<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Class ShowAddFromTVDB.
 */
class ShowAddFromTVDB extends Job implements ShouldQueue
{
    use InteractsWithQueue;
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

    public function handle()
    {
        Log::debug('ShowAddFromTVDB: Start a job with : '.json_encode($this->inputs));

        createorUpdateShow($this->inputs);

        Log::debug('ShowAddFromTVDB: End of job with : '.json_encode($this->inputs));
    }
}
