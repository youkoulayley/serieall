<?php

namespace App\Providers;

use App\Interfaces\CommentRepositoryInterface;
use App\Repositories\CommentRepository;
use Illuminate\Support\ServiceProvider;

/**
 * CommentRepositoryProvider class.
 */
class CommentRepositoryProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            CommentRepositoryInterface::class,
            CommentRepository::class,
        );
    }
}
