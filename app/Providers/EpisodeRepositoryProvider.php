<?php

namespace App\Providers;

use App\Interfaces\EpisodeRepositoryInterface;
use App\Repositories\EpisodeRepository;
use Illuminate\Support\ServiceProvider;

/**
 * EpisodeRepositoryProvider class.
 */
class EpisodeRepositoryProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            EpisodeRepositoryInterface::class,
            EpisodeRepository::class,
        );
    }
}
