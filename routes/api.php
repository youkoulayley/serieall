<?php

declare(strict_types=1);

use Dingo\Api\Routing\Router;

/*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register API routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | is assigned the "api" middleware group. Enjoy building your API!
    |
    */

$api = app(Router::class);

$api->version('v1', [], function (Router $api) {
    $api->get('shows/search', '\App\Http\Controllers\Api\V1\ShowSearchController@index');
    $api->get('shows/list', '\App\Http\Controllers\Api\V1\ShowsListController@index');
    $api->get('shows/abandoned/list', '\App\Http\Controllers\Api\V1\ShowsAbandonnedController@index');
    $api->get('articles/list', '\App\Http\Controllers\Api\V1\ArticlesListController@index');
    $api->get('seasons/list', '\App\Http\Controllers\Api\V1\SeasonsListController@index');
    $api->get('episodes/seasons/{id}', '\App\Http\Controllers\Api\V1\EpisodesBySeasonIDController@index');
    $api->get('seasons/show/{id}', '\App\Http\Controllers\Api\V1\SeasonsByShowIDController@index');
    $api->get('seasons/show_name/{name}', '\App\Http\Controllers\Api\V1\SeasonsByShowNameController@index');
    $api->get('nationalities/list', '\App\Http\Controllers\Api\V1\NationalitiesController@index');
    $api->get('artists/list', '\App\Http\Controllers\Api\V1\ArtistsController@index');
    $api->get('genres/list', '\App\Http\Controllers\Api\V1\GenresController@index');
    $api->get('channels/list', '\App\Http\Controllers\Api\V1\ChannelsController@index');
    $api->get('categories/list', '\App\Http\Controllers\Api\V1\CategoriesListController@index');
    $api->get('users/list', '\App\Http\Controllers\Api\V1\UsersListController@index');
});
