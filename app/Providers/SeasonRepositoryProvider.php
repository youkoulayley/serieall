<?php

namespace App\Providers;

use App\Interfaces\SeasonRepositoryInterface;
use App\Repositories\SeasonRepository;
use Illuminate\Support\ServiceProvider;

/**
 * SeasonRepositoryProvider class.
 */
class SeasonRepositoryProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            SeasonRepositoryInterface::class,
            SeasonRepository::class,
        );
    }
}
