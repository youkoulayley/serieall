<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Class ComposerServiceProvider.
 */
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
            ['admin/index', 'admin/log'],
            'App\Http\ViewComposers\NavActiveAdminHomeComposer'
        );

        // NavActive = AdminShows
        View::composer(
            ['admin/shows/*', 'admin/artists/*', 'admin/seasons/*', 'admin/episodes/*'],
            'App\Http\ViewComposers\NavActiveAdminShowsComposer'
        );

        // NavActive = AdminArticles
        View::composer(
            ['admin/articles/*'],
            'App\Http\ViewComposers\NavActiveAdminArticlesComposer'
        );

        // NavActive = AdminUsers
        View::composer(
            ['admin/users/*'],
            'App\Http\ViewComposers\NavActiveAdminUsersComposer'
        );

        // NavActive = AdminComments
        View::composer(
            ['admin/comments/*'],
            'App\Http\ViewComposers\NavActiveAdminCommentsComposer'
        );

        // NavActive = AdminSystem
        View::composer(
            ['admin/system/*'],
            'App\Http\ViewComposers\NavActiveAdminSystemComposer'
        );

        // SITE
        // NavActive = home
        View::composer(
            ['home', 'errors/*', 'pages/*'],
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
            ['shows/*', 'seasons/*', 'episodes/*', 'comments/fiche', 'layouts/errors'],
            'App\Http\ViewComposers\NavActiveShowsComposer'
        );

        // NavActive = articles
        View::composer(
            ['articles/*'],
            'App\Http\ViewComposers\NavActiveArticlesComposer'
        );

        // NavActive = planning
        View::composer(
            ['planning/*'],
            'App\Http\ViewComposers\NavActivePlanningComposer'
        );

        // NavActive = classements
        View::composer(
            ['ranking/*'],
            'App\Http\ViewComposers\NavActiveClassementComposer'
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

        // FicheActive = comments
        View::composer(
            ['comments/fiche'],
            'App\Http\ViewComposers\FicheActiveCommentsComposer'
        );

        // FicheActive = articles
        View::composer(
            ['shows/articles', 'shows/articlesCategory'],
            'App\Http\ViewComposers\FicheActiveArticlesComposer'
        );

        // FicheActive = statistics
        View::composer(
            ['shows/statistics'],
            'App\Http\ViewComposers\FicheActiveStatisticsComposer'
        );

        // UnreadNotifications
        View::composer(
            ['*'],
            'App\Http\ViewComposers\UnreadNotificationsComposer',
            'App\Http\ViewComposers\SloganComposer'
        );

        // UnreadNotifications
        View::composer(
            ['layouts/app'],
            'App\Http\ViewComposers\FollowedShowsComposer'
        );
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
    }
}
