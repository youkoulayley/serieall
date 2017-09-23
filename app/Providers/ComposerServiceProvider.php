<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ComposerServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function boot()
    {
        // ADMINISTRATION
        // NavActive = AdminHome
        View::composer(
            ['admin/index','admin/log'],
            'App\Http\ViewComposers\NavActiveAdminHomeComposer'
        );

        // NavActive = AdminShows
        View::composer(
            ['admin/shows/*', 'admin/artists/*', 'admin/seasons/*', 'admin/episodes/*'],
            'App\Http\ViewComposers\NavActiveAdminShowsComposer'
        );

        // NavActive = AdminUsers
        View::composer(
            ['admin/users/*'],
            'App\Http\ViewComposers\NavActiveAdminUsersComposer'
        );

        // NavActive = AdminSystem
        View::composer(
            ['admin/system/*'],
            'App\Http\ViewComposers\NavActiveAdminSystemComposer'
        );

        // SITE
        // NavActive = home
        View::composer(
            ['home', 'errors/*'],
            'App\Http\ViewComposers\NavActiveHomeComposer'
        );

        // NavActive = login
        View::composer(
            ['auth/login', 'auth/passwords/*'],
            'App\Http\ViewComposers\NavActiveLoginComposer'
        );

        // NavActive = register
        View::composer(
            ['auth/register'],
            'App\Http\ViewComposers\NavActiveRegisterComposer'
        );

        // NavActive = profil
        View::composer(
            ['users/*'],
            'App\Http\ViewComposers\NavActiveProfilComposer'
        );

        // NavActive = shows
        View::composer(
            ['shows/*', 'seasons/*', 'episodes/*', 'layouts/errors'],
            'App\Http\ViewComposers\NavActiveShowsComposer'
        );


        // FICHES
        // FicheActive = home
        View::composer(
            ['shows/fiche'],
            'App\Http\ViewComposers\FicheActiveHomeComposer'
        );

        // FicheActive = seasons
        View::composer(
            ['seasons/fiche', 'episodes/fiche'],
            'App\Http\ViewComposers\FicheActiveSeasonsComposer'
        );

        // FicheActive = details
        View::composer(
            ['shows/details'],
            'App\Http\ViewComposers\FicheActiveDetailsComposer'
        );
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}