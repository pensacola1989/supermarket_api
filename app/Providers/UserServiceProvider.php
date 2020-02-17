<?php
/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/15/17
 * Time: 3:32 PM
 */
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Account\User;
use App\Services\Account\UserObserver;

class UserServiceProvider extends ServiceProvider {
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        User::observe(new UserObserver);
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
