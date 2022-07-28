<?php

namespace App\Providers;

use App\Interfaces\UserServiceInterface;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

/**
 * UserServiceProvider class.
 */
class UserServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            UserServiceInterface::class,
            UserService::class,
        );
    }
}
