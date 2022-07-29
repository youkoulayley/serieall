<?php

declare(strict_types=1);

namespace App\Console;

use App\Jobs\FlushLogs1Week;
use App\Jobs\ShowUpdateFromTMDB;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * Class Kernel.
 */
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Commands\Inspire::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            dispatch(new ShowUpdateFromTMDB());
        })->daily();

        $schedule->call(function () {
            dispatch(new FlushLogs1Week());
        })->daily();
    }
}
