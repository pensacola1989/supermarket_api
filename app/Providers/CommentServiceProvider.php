<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Comment\Comment;
use App\Services\Comment\CommentObserver;

class CommentServiceProvider extends ServiceProvider {
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
//        Comment::observe(new CommentObserver);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        //
    }

}
