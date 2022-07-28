<?php

namespace App\Providers;

use App\Interfaces\ShowRepositoryInterface;
use App\Repositories\ShowRepository;
use Illuminate\Support\ServiceProvider;

/**
 * ShowRepositoryProvider class.
 */
class ShowRepositoryProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            ShowRepositoryInterface::class,
            ShowRepository::class,
        );
    }
}
