<?php

declare(strict_types=1);

namespace App\Providers;

use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Class AppServiceProvider.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();

        Schema::defaultStringLength(191); //Solved by increasing StringLength

        $folderImages = config('directories.images');
        $folderShows = config('directories.shows');
        $folderActors = config('directories.actors');

        $noteGood = config('param.good');
        $noteNeutral = config('param.neutral');
        $noteBad = config('param.bad');

        View::share('folderImages', $folderImages);
        View::share('folderShows', $folderShows);
        View::share('folderActors', $folderActors);
        View::share('noteGood', $noteGood);
        View::share('noteNeutral', $noteNeutral);
        View::share('noteBad', $noteBad);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->isLocal()) {
            $this->app->register(IdeHelperServiceProvider::class);
        }
    }
}
