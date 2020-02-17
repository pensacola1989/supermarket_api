<?php

namespace App\Services\WechatAuth;

use Illuminate\Support\ServiceProvider;

class WechatAuthServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->configure('wechat');
        $this->app->bind('wechatAuth', function () {
            $config = config('wechat');
            return new WechatAuth($config);
        });
        $this->app->bind('wechatTplMsg', function () {
            $config = config('wechat');
            return new TemplateMessage($config);
        });

        $this->app->alias('wechatAuth', WechatAuth::class);
        $this->app->alias('wechatTplMsg', TemplateMessage::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['wechatAuth', WechatAuth::class, 'wechatTplMsg', TemplateMessage::class];
    }
}
