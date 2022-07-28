<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Application routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

/*
    Home
*/

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', 'HomeController@index')->name('home');

Route::get('/rss-podcasts', 'ArticleController@RSSPodcast')->name('rss.podcasts');

/*
    Pages
*/
Route::view('/cgu', 'pages.cgu')->name('cgu');

Route::view('/equipe', 'pages.team')->name('team');

Route::view('/nous-rejoindre', 'pages.join')->name('join');

Route::view('/about', 'pages.about')->name('about');

Route::view('/404', 'errors.404')->name('404');

Route::view('/500', 'errors.500')->name('500');

/*
    Partie Authentification
*/
Route::post('login', 'Auth\LoginController@login')->name('login');
Route::get('logout', 'Auth\LoginController@logout')->name('logout');
Route::post('register', 'Auth\RegisterController@register')->name('register');
Route::get('inscription', 'HomeController@index')->name('inscription');

Route::get('user/activation/{token}', 'Auth\LoginController@activateUser')->name('user.activate');

/*
    Réinitialiser son mot de passe
*/
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.form');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');

/*
    Formulaire de contact
*/
Route::view('/contact', 'pages.contact')->name('contact');
Route::post('sendContact', 'ContactController@sendContact')->name('contact.send');

/*
    Users Routes
*/
Route::get('utilisateurs', [UserController::class, 'index'])
    ->name('users.index');

Route::prefix('profil')->group(function () {
    Route::get('{user}', [UserController::class, 'getProfile'])
        ->name('user.profile');
    Route::get('{user}/notes/{sort?}', [UserController::class, 'getRates'])
        ->name('user.profile.rates');

    Route::get('{user}/parametres', [UserController::class, 'getParameters'])
        ->name('user.profile.parameters')
        ->middleware('amithisuser');

    Route::middleware('auth')->group(function () {
        Route::post('password', 'UserController@changePassword')
            ->name('user.profile.changePassword');

        Route::post('info', 'UserController@changeInfo')
            ->name('user.profile.changeInfo');
    });
});

Route::get('profil/{user}/avis/{action?}/{filter?}/{tri?}', 'UserController@getComments')->name('user.profile.comments');
Route::get('profil/{user}/series', 'UserController@getShows')->name('user.profile.shows');
Route::get('profil/{user}/classements', 'UserController@getRanking')->name('user.profile.ranking');
Route::get('profil/{user}/planning', 'UserController@getPlanning')->name('user.profile.planning')->middleware('amithisuser');
Route::get('profil/{user}/notifications', 'UserController@getNotifications')->name('user.profile.notifications')->middleware('amithisuser');

/*
    Partie Notifications
 */
Route::post('notification', 'UserController@markNotification')->name('user.markNotification')->middleware('auth');
Route::post('notifications', 'UserController@markNotifications')->name('user.markNotifications')->middleware('auth');

/*
    Partie Suivi de série
 */
Route::post('followshow', 'UserController@followShow')->name('user.followshow')->middleware('auth');
Route::post('followshow/fiche', 'UserController@followShowFiche')->name('user.followshowfiche')->middleware('auth');
Route::post('unfollowshow/{show}', 'UserController@unfollowShow')->name('user.unfollowshow')->middleware('auth');

/*
    Partie Séries
*/
Route::get('series/{channel?}/{nationality?}/{genre?}/{tri?}', 'ShowController@index')->name('show.index');
Route::get('serie/{show_url}', 'ShowController@getShowFiche')->name('show.fiche');
Route::get('serie/{show_url}/details', 'ShowController@getShowDetails')->name('show.details');
Route::get('serie/{show_url}/statistiques', 'ShowController@getStatistics')->name('show.statistics');

/*
    Partie Saisons
 */
Route::get('saison/{show_url}/{season}', 'SeasonController@getSeasonFiche')->name('season.fiche');

/*
    Partie Episodes
 */
Route::get('episode/{show_url}/s{season}e{episode}/{id?}', 'EpisodeController@getEpisodeFiche')->name('episode.fiche');
Route::post('episode/rate', 'EpisodeController@rateEpisode')->name('episode.rate')->middleware('auth');

/*
    Partie Articles
*/
Route::get('articles', 'ArticleController@index')->name('article.index');
Route::get('articles/{id}', 'ArticleController@indexByCategory')->name('article.indexCategory');
Route::get('article/{article_url}', 'ArticleController@show')->name('article.show');
Route::get('serie/{show_url}/articles', 'ShowController@getShowArticles')->name('article.fiche');
Route::get('serie/{show_url}/articles/{category_id}', 'ShowController@getShowArticlesByCategory')->name('article.ficheCategory');

/*
    Partie Commentaire
*/
Route::get('avis/{show_url}/{season?}/{episode?}/{episode_id?}', 'CommentController@fiche')->name('comment.fiche');
Route::post('comment', 'CommentController@store')->name('comment.store')->middleware('auth');
Route::post('commentWTN', 'CommentController@storeWithoutThumbAndNote')->name('comment.storewtn')->middleware('auth');
Route::post('reaction', 'CommentController@storeReaction')->name('comment.storereaction')->middleware('auth');

/*
    Partie Planning
 */
Route::get('planning', 'PlanningController@index')->name('planning.index');

/*
    Partie Classement
 */
Route::get('classements', 'RankingController@index')->name('ranking.index');

/*
    Association part
 */
Route::get('association', 'AssociationController@index')->name('association.index');

/*
    Partie administration protégée par le middleware Admin (obligation d'être admin pour accéder aux routes)
*/
Route::group(['middleware' => 'basemanager'], function () {
    /* HOME */
    Route::get('admin', 'Admin\AdminController@index')->name('admin');

    /* SHOWS */
    Route::get('admin/shows/index/{show}', 'Admin\AdminShowController@getShow')->name('admin.shows.index.getShow');
    Route::get('admin/shows/create/manually', 'Admin\AdminShowController@createManually')->name('admin.shows.create.manually');
    Route::post('admin/shows/store/manually', 'Admin\AdminShowController@storeManually')->name('admin.shows.store.manually');
    Route::post('admin/shows/update/manually', 'Admin\AdminShowController@updateManually')->name('admin.shows.update.manually');
    Route::get('admin/shows/updatetmdb', 'Admin\AdminShowController@updateFromTMDB')->name('admin.shows.updatetmdb');
    Route::get('admin/addtmdbid', 'Admin\AdminShowController@addTMDBID')->name('admin.shows.addtmdbid');
    Route::get('admin/shows/clearDoublons', 'Admin\AdminShowController@clearDoublons')->name('admin.shows.clearDoublons');
    Route::post('admin/shows/updatetmdb/{id}', 'Admin\AdminShowController@updateOneShowFromTMDB')->name('admin.shows.updateoneshowfromtmdb');

    Route::get('admin/shows/redirectJSON', 'Admin\AdminShowController@redirectJSON')->name('admin.shows.redirectJSON');
    Route::resource('admin/shows', 'Admin\AdminShowController', [
        'names' => [
            'index' => 'admin.shows.index',
            'create' => 'admin.shows.create',
            'store' => 'admin.shows.store',
            'show' => 'admin.shows.show',
            'edit' => 'admin.shows.edit',
            'update' => 'admin.shows.update',
            'destroy' => 'admin.shows.destroy',
        ],
    ]);

    /* ARTISTS */
    Route::get('admin/shows/{show}/artists', 'Admin\AdminArtistController@show')->name('admin.artists.show');
    Route::get('admin/shows/{show}/artists/create', 'Admin\AdminArtistController@create')->name('admin.artists.create');
    Route::post('admin/artists', 'Admin\AdminArtistController@store')->name('admin.artists.store');
    Route::get('admin/shows/{show}/artists/{artist}', 'Admin\AdminArtistController@edit')->name('admin.artists.edit');
    Route::put('admin/artists', 'Admin\AdminArtistController@update')->name('admin.artists.update');
    Route::get('admin/artists/{show}/redirect', 'Admin\AdminArtistController@redirect')->name('admin.artists.redirect');
    Route::delete('admin/shows/{show}/artists/{artist}/unlinkShow', 'Admin\AdminArtistController@unlinkShow')->name('admin.artists.unlinkShow');

    /* SEASONS */
    Route::get('admin/shows/{show}/seasons', 'Admin\AdminSeasonController@show')->name('admin.seasons.show');
    Route::get('admin/shows/{show}/seasons/create', 'Admin\AdminSeasonController@create')->name('admin.seasons.create');
    Route::post('admin/seasons', 'Admin\AdminSeasonController@store')->name('admin.seasons.store');
    Route::get('admin/seasons/{season}/edit', 'Admin\AdminSeasonController@edit')->name('admin.seasons.edit');
    Route::put('admin/seasons/{season}', 'Admin\AdminSeasonController@update')->name('admin.seasons.update');
    Route::get('admin/seasons/{show}/redirect', 'Admin\AdminSeasonController@redirect')->name('admin.seasons.redirect');
    Route::delete('admin/seasons/{season}', 'Admin\AdminSeasonController@destroy')->name('admin.seasons.destroy');

    /* EPISODES */
    Route::get('admin/seasons/{season}/episodes/create', 'Admin\AdminEpisodeController@create')->name('admin.episodes.create');
    Route::post('admin/episodes', 'Admin\AdminEpisodeController@store')->name('admin.episodes.store');
    Route::get('admin/episodes/{episode}', 'Admin\AdminEpisodeController@edit')->name('admin.episodes.edit');
    Route::put('admin/episodes/update', 'Admin\AdminEpisodeController@update')->name('admin.episodes.update');
    Route::get('admin/episodes/{season}/redirect', 'Admin\AdminEpisodeController@redirect')->name('admin.episodes.redirect');
    Route::delete('admin/episodes/{episode}', 'Admin\AdminEpisodeController@destroy')->name('admin.episodes.destroy');

    Route::group(['middleware' => 'editor'], function () {
        /* ARTICLES */
        Route::get('admin/articles/index/{article}', 'Admin\AdminArticleController@getArticle')->name('admin.articles.index.getArticle');
        Route::get('admin/articles', 'Admin\AdminArticleController@index')->name('admin.articles.index');
        Route::get('admin/articles/create', 'Admin\AdminArticleController@create')->name('admin.articles.create');
        Route::get('admin/articles/{article}', 'Admin\AdminArticleController@edit')->name('admin.articles.edit');
        Route::put('admin/articles/update', 'Admin\AdminArticleController@update')->name('admin.articles.update');
        Route::post('admin/articles', 'Admin\AdminArticleController@store')->name('admin.articles.store');
        Route::delete('admin/articles/{article}', 'Admin\AdminArticleController@destroy')->name('admin.articles.destroy');
    });

    Route::group(['middleware' => 'admin'], function () {
        /* USERS */
        Route::get('admin/users/index/{user}', 'Admin\AdminUserController@getUser')->name('admin.users.index.getUser');
        Route::get('admin/users', 'Admin\AdminUserController@index')->name('admin.users.index');
        Route::get('admin/users/create', 'Admin\AdminUserController@create')->name('admin.users.create');
        Route::post('admin/users', 'Admin\AdminUserController@store')->name('admin.users.store');
        Route::get('admin/users/{user}', 'Admin\AdminUserController@edit')->name('admin.users.edit');
        Route::put('admin/users/update', 'Admin\AdminUserController@update')->name('admin.users.update');
        Route::post('admin/users/ban/{user}', 'Admin\AdminUserController@ban')->name('admin.users.ban');
        Route::post('admin/users/reinit/{user}', 'Admin\AdminUserController@reinit')->name('admin.users.reinit');
        Route::delete('admin/users/{user}', 'Admin\AdminUserController@destroy')->name('admin.users.destroy');

        /* COMMENTS */
        Route::get('admin/comments', 'Admin\AdminCommentController@index')->name('admin.comments.index');
        Route::get('admin/comments/shows', 'Admin\AdminCommentController@indexShows')->name('admin.comments.indexShows');
        Route::get('admin/comments/articles', 'Admin\AdminCommentController@indexArticles')->name('admin.comments.indexArticles');
        Route::get('admin/comments/{comment}', 'Admin\AdminCommentController@edit')->name('admin.comments.edit');
        Route::get('admin/comments/{type}/{type_id}', 'Admin\AdminCommentController@getComments')->name('admin.comments.getComments');
        Route::put('admin/comments/update', 'Admin\AdminCommentController@update')->name('admin.comments.update');
        Route::delete('admin/comments/{comment}', 'Admin\AdminCommentController@destroy')->name('admin.comments.destroy');

        /* SYSTEM */
        Route::get('admin/system', 'Admin\System\AdminSystemController@index')->name('admin.system');
        /* LOGS */
        Route::get('admin/system/logs', 'Admin\System\AdminLogsController@index')->name('admin.logs');
        Route::get('admin/system/logs/view/{id}', 'Admin\System\AdminLogsController@view')->name('admin.logs.view');
        /* CONTACTS */
        Route::get('admin/system/contacts', 'Admin\System\AdminContactsController@index')->name('admin.contacts');
        Route::get('admin/system/contacts/view/{id}', 'Admin\System\AdminContactsController@view')->name('admin.contacts.view');
        Route::post('admin/system/contacts/reply', 'Admin\System\AdminContactsController@replyContact')->name('admin.contacts.reply');
        Route::delete('admin/system/contacts/{id}', 'Admin\System\AdminContactsController@destroy')->name('admin.contacts.destroy');
        /* SLOGANS */
        Route::get('admin/system/slogans', 'Admin\System\AdminSlogansController@index')->name('admin.slogans.index');
        Route::get('admin/system/slogans/create', 'Admin\System\AdminSlogansController@create')->name('admin.slogans.create');
        Route::post('admin/system/slogans', 'Admin\System\AdminSlogansController@store')->name('admin.slogans.store');
        Route::get('admin/system/slogans/{slogan}', 'Admin\System\AdminSlogansController@edit')->name('admin.slogans.edit');
        Route::put('admin/system/slogans', 'Admin\System\AdminSlogansController@update')->name('admin.slogans.update');
        Route::get('admin/system/slogan/redirect', 'Admin\System\AdminSlogansController@redirect')->name('admin.slogans.redirect');
        Route::delete('admin/system/slogans/{slogan}', 'Admin\System\AdminSlogansController@destroy')->name('admin.slogans.destroy');
    });
});
