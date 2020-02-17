<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
 */

/**
 * 同步微信头像，留到后期在做
 */

use Illuminate\Http\Request;

$router->get('/', function () {
    echo app('redis')->get('version');
});

$router->get('test_img', function () {
    $client = new GuzzleHttp\Client;
    $resp = $client->get('https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTKd7v07kPMgEicMZzYMkZibZoKP7rVgk5WfbJRlXy1ACKyfIRMVNvCb454bCMN5R6ozqsQoWLZicPAVA/0', ['verify' => false]);

    $body = $resp->getBody();

    app()->configure('oss');
    $ossConfig = config('oss');
    $ossClient = new OSS\OssClient($ossConfig['oss_id'], $ossConfig['oss_secret'], $ossConfig['oss_endpoint']);
    $ret = $ossClient->putObject('ehe1989', 'avatar.jpg', $body->getContents());

    dd($ret);
    //
    //    @ header("Content-Type:image/png");
    //    echo $body;
});

$router->group(['prefix' => 'api/v1'], function () use ($router) {
    $router->get('avatar/{id}', function ($id) {
        $identicon = new \Identicon\Identicon();
        $identicon->displayImage($id, 64);
    });

    $router->get('/', function () use ($router) {
        return [
            'apiVersion' => 'v1',
        ];
    });
    /**
     * custom auth control
     */
    // resource('place', 'PlaceController');
    $router->post('place/{id}/subscribe', 'PlaceController@subscribe');
    /**
     * no auth post search for place-post view.
     */
    $router->post('/post/no-auth/search', 'PostController@searchNoAuth');
    /**
     * issue a token
     */
    $router->post('auth/token', 'AuthController@getToken');
    /**
     * set a wechat session
     */

    $router->group(['middleware' => ['auth']], function () use ($router) {
        /**
         * comment api
         */
        resource('comment', 'CommentController');

        $router->get('notify/system/mine', 'UserController@getSystemNotifications');
        $router->get('notify/system/mine/unread', 'UserController@getSystemNotificationsNums');
        $router->put('notify/system/read', 'UserController@readSystemNotify');

        $router->post('post/search', 'PostController@search');
        $router->get('post/searchText', 'PostController@searchText');
        $router->get('post/place/{placeExtId}/stick', 'PostController@getStickTopPostsByPlaceId'); // should block check
        $router->get('post/{id:[0-9]+}', 'PostController@show');
        $router->get('post/tags', 'TagController@getTags');
        $router->post('post/nearby/{latlng}', 'PostController@searchNearBy'); // should block check
        $router->post('post/nearby/{latlng}/new', 'PostController@loadNearyByNew'); // should block check
        $router->post('post/board/index/{placeId}', 'PostController@getBoardIndex'); // should block check
        $router->post('post/{postId}/stick', 'PostController@stickTop');

        $router->get('post/{postId}/comments/sinceId', 'CommentController@getsinceCommentIdOfPost');
        /** 
         * place routes
         */
        resource('place', 'PlaceController');

        resource('recommand', 'RecommandController');

        // $router->post('post/{id}/waterfall', 'PostController@getPhotoWall');
        $router->post('post/{id}/waterfall', 'AttachmentController@getPhotoWall');

        $router->get('place/{placeId:[0-9]+}/tags', 'TagController@getPlaceTagsByExtId');
    });

    $router->post('wechat/{code}/session/sync', 'AuthController@getWechatSession');
    $router->post('wechat/{authCode}/session', 'AuthController@wechatCodeForSession');
    $router->get('test', ['middleware' => ['wechat-auth', 'auth'], 'uses' => 'TestController@index']);
    $router->post('like/search', 'LikeController@search');
    $router->group(['middleware' => ['wechat-auth', 'jwt.auth']], function () use ($router) {

        $router->post('orders/create', 'OrderController@create');
        $router->put('orders/{orderSn}/update', 'OrderController@update');
        $router->get('orders/{orderSn:[0-9]+}', 'OrderController@getOrderByOrderSn');
        $router->get('orders/search', 'OrderController@search');

        $router->get('places/history/mine', 'PlaceHistoryController@getMyViewHistory');

        $router->post('post/{postId:[0-9]+}/comments/read', 'PostController@readComments');

        $router->post('formId', 'UserController@storeFormIds');

        $router->post('upload', 'AttachmentController@uploadAttachment');

        $router->post('comments/mine', 'CommentController@getMyComments');

        // $router->post('post/{id}/waterfall', 'PostController@getPhotoWall');
        $router->get('post/mp/mine', 'PostController@searchMpLinkPost');
        $router->post('post/mine/like', 'PostController@getMyLikePosts');
        $router->post('post/{postId:[0-9]+}/like', 'PostController@like'); // should block check
        $router->post('comment/{commentId:[0-9]+}/like', 'CommentController@like'); // should block check
        // $router->post('post/search', 'PostController@search');
        $router->post('post', 'PostController@create'); // should block check
        $router->put('post/{id}', 'PostController@update'); // should block check
        $router->delete('post/{id}', 'PostController@destroy');

        $router->get('user/{id}/summary', 'UserController@summary');
        $router->get('user/{id}/likes', 'UserController@likes');
        $router->get('user/{id}/be_likes', 'UserController@beLikes');
        $router->get('user/{id}/nr', 'UserController@notifyRegister');
        $router->post('user/{id}/rnr', 'UserController@readNotify');

        $router->post('user/block/{blockUserId}', 'UserBlockController@create');
        $router->get('user/blocks', 'UserBlockController@search');
        $router->delete('user/blocks/{blockUserId}', 'UserBlockController@removeMyBlockById');

        $router->post('rcmd/sort/{id}/{replaceId}', 'RecommandController@sort');

        $router->get('comments/count/mine', 'PostController@getMyNewArticleCommentsCount');


        $router->post('placeApply', 'PlaceApplyController@create'); // should block check
        $router->post('place/{placeId}/sub/apply', 'PlaceController@addApplySubscribe'); // should block check
        $router->group(['prefix' => 'admin/place/{placeId:[0-9]+}', 'middleware' => ['admin']], function () use ($router) {
            $router->put('sub/approve/{userId}', 'PlaceSubApplyController@approveApplySubscribe');
            $router->get('applies', 'PlaceSubApplyController@show');
            $router->get('summary', 'PlaceController@summary');
            $router->put('', 'PlaceController@update');

            $router->post('block/{userId:[0-9]+}', 'PlaceBlockController@create');
            $router->delete('block/{userId:[0-9]+}', 'PlaceBlockController@destroy');
            $router->get('blocks', 'PlaceBlockController@search');

            $router->get('subscribers', 'PlaceController@searchSubscriber');

            // admin/place/{placeId}/tags/
            $router->post('tags', 'TagController@create');
            $router->put('tag/{tagId}', 'TagController@update');
            $router->delete('tag/{tagId}', 'TagController@destroy');
            $router->get('tags', 'TagController@getManagePlaceTags');
        });

        $router->get('tag/{tagId:[0-9]+}', 'TagController@show');




        /**
         * user api
         */
        resource('user', 'UserController');

        resource('place_history', 'PlaceHistoryController');
        resource('place_category', 'PlaceCategoryController');

        resource('notify', 'MsgNotifyController');
        resource('report', 'ReportController');
    });

    /*********************Admin*********************/
    $router->post('admin/token', 'AdminController@login');
    $router->get('test_admin', ['middleware' => 'auth:admin', function (Request $request) {
        $user = Auth::guard('admin')->user();
        dd($user);
    }]);
    // $router->group(['prefix' => '/admin', 'middleware' => ['auth:admin']], function () use ($router) {
    //     resource('place', 'PlaceController');
    // });
});

function resource($uri, $controller)
{
    global $app;

    $router = $app->router;

    $router->get($uri . '/all', $controller . '@all');
    $router->post($uri, $controller . '@create');
    $router->post($uri . '/search', $controller . '@search');
    $router->get($uri . '/{id}', $controller . '@show');
    $router->put($uri . '/{id}', $controller . '@update');
    $router->patch($uri . '/{id}', $controller . '@update');
    $router->delete($uri . '/{id}', $controller . '@destroy');
}
