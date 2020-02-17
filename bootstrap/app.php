<?php

require_once __DIR__ . '/../vendor/autoload.php';

try {
    // (new Dotenv\Dotenv(__DIR__ . '/../'))->load();
    (new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
        dirname(__DIR__)
    ))->bootstrap();
} catch (Dotenv\Exception\InvalidPathException $e) {
    //
}

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
 */

$app = new Laravel\Lumen\Application(
    realpath(__DIR__ . '/../')
);

$app->withFacades();

$app->withEloquent();

if (!class_exists('MyHelper')) {
    class_alias(App\Services\Facade\Helper::class, 'MyHelper');
}
if (!class_exists('Geotools')) {
    class_alias(Toin0u\Geotools\Facade\Geotools::class, 'GeoTools');
}
if (!class_exists('Curl')) {
    class_alias(Ixudra\Curl\Facades\Curl::class, 'Curl');
}
if (!class_exists('WeChatNotify')) {
    class_alias(App\Services\Facade\WeChatNotifyFacade::class, 'WeChatNotify');
}
$app->configure('app');
$app->configure('fractal');
/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
 */

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
 */

// $app->middleware([
//    App\Http\Middleware\ExampleMiddleware::class
// ]);

$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
    'wechat-auth' => App\Http\Middleware\WechatAuthMiddleware::class,
    'admin' => App\Http\Middleware\AdminMiddleware::class,
    'block-check' => App\Http\Middleware\PlaceBlockCheckMiddleware::class
]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
 */
$app->register(Ixudra\Curl\CurlServiceProvider::class);
$app->register(App\Services\WechatAuth\WechatAuthServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
$app->register(Tymon\JWTAuth\Providers\LumenServiceProvider::class);
$app->register(Spatie\Fractal\FractalServiceProvider::class);
$app->register(Anik\Form\FormRequestServiceProvider::class);
// $app->register(App\Providers\AppServiceProvider::class);
// $app->register(App\Providers\AuthServiceProvider::class);
// $app->register(App\Providers\EventServiceProvider::class);
$app->register(Toin0u\Geotools\GeotoolsServiceProvider::class);
$app->register(Illuminate\Redis\RedisServiceProvider::class);
$app->register(App\Providers\MyServiceProvider::class);
$app->register(App\Providers\CommentServiceProvider::class);
/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
 */

// $app->group(['namespace' => 'App\Http\Controllers'], function ($app) {
//     require __DIR__ . '/../routes/web.php';
// });
$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__ . '/../routes/web.php';
});

//$app->configure('wechat');

return $app;
