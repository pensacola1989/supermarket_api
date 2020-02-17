<?php

namespace App\Providers;

use App\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Services\Place\PlaceRepository;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    { }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request) {

            //            if ($request->input('api_token')) {
            //                return User::where('api_token', $request->input('api_token'))->first();
            //            }
        });

        Gate::define(
            'delete-post',
            function ($user, $post) {
                // return $user->id === $post->User->id;
                return $post->User->id === $user->id || $post->Place->admin_id === $user->id;
            }
        );

        Gate::define('delete-comment', function ($user, $comment) {
            return $comment->from_uid === $user->id || $comment->Post->Place->admin_id === $user->id;
        });

        Gate::define(
            'check-quan-index',
            function ($user, $place) {
                $isBlock = app()->make(PlaceRepository::class)->userIsBlock($user->id, $place->id);
                return !$isBlock;
            }
        );

        Gate::define(
            'manage-place',
            function ($user, $place) {
                return $place->admin_id === $user->id;
            }
        );


        Gate::define('get-history', function ($user, $inputUserId) {
            return $user->id == $inputUserId;
        });
        //        Gate::define('create-history', function ($user, $history) {
        //           return $user->id === $history->User->id;
        //        });
    }
}
