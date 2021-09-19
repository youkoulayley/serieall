<?php

namespace App\Providers;

use App\Interfaces\RateRepositoryInterface;
use App\Repositories\RateRepository;
use Illuminate\Support\ServiceProvider;

/**
 * RateRepositoryProvider class.
 */
class RateRepositoryProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            RateRepositoryInterface::class,
            RateRepository::class,
        );
    }
}
