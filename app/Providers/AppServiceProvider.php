<?php

namespace App\Providers;

use App\Services\WeChatNotify\WeChatNotifyContract;
use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $app = $this->app;
        $app->bind('weChatNotify', function () use ($app) {
            return $app->make(WeChatNotifyContract::class);
        });
    }

    public function boot()
    {
        Carbon::setLocale('zh');
    }
}
