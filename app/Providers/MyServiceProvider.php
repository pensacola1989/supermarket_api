<?php

namespace App\Providers;

/**
 * Created by PhpStorm.
 * User: weiwei
 * Date: 4/14/2015
 * Time: 1:57 PM
 */

use App\Services\Account\Block\UserBlockContract;
use App\Services\Account\Block\UserBlockRepository;
use App\Services\Account\LoginContract;
use App\Services\Account\LoginRepository;
use App\Services\Account\User;
use App\Services\Account\UserContract;
use App\Services\Account\UserObserver;
use App\Services\Account\UserRepository;
use App\Services\Admin\AdminContract;
use App\Services\Admin\AdminRepository;
use App\Services\Attachments\AttachContract;
use App\Services\Attachments\AttachRepository;
use App\Services\Comment\Comment;
use App\Services\Comment\CommentContract;
use App\Services\Comment\CommentObserver;
use App\Services\Comment\CommentRepository;
use App\Services\Helpers;
use App\Services\HistoryPlace\HistoryPlaceContract;
use App\Services\HistoryPlace\HistoryPlaceRepository;
use App\Services\MsgNotify\MsgNotifyContract;
use App\Services\MsgNotify\MsgNotifyRepository;
use App\Services\Place\Place;
use App\Services\Place\PlaceCategoryContract;
use App\Services\Place\PlaceCategoryRepository;
use App\Services\Place\PlaceContract;
use App\Services\Place\PlaceObserver;
use App\Services\Place\PlaceRepository;
use App\Services\Post\Like;
use App\Services\Post\LikeContract;
use App\Services\Post\LikeObserver;
use App\Services\Post\LikeRepository;
use App\Services\Post\Post;
use App\Services\Post\PostContract;
use App\Services\Post\PostObserver;
use App\Services\Post\PostRepository;
use App\Services\Recommand\RecommandContract;
use App\Services\Recommand\RecommandRepository;
use App\Services\Report\ReportContract;
use App\Services\Report\ReportRepository;
use App\Services\VerifyCode\VerifyCodeContract;
use App\Services\VerifyCode\VerifyCodeRepository;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use App\Services\Place\PlaceApplyContract;
use App\Services\Place\PlaceApplyRespository;
use App\Services\Comment\CommentLikeContract;
use App\Services\Comment\CommentLikeRepository;
use App\Services\Comment\CommentLike;
use App\Services\Comment\CommentLikeObserver;
use App\Services\Place\PlaceSubApplyContract;
use App\Services\Place\PlaceSubApplyRespository;
use App\Services\Place\PlaceBlockContract;
use App\Services\Place\PlaceBlockRepository;
use App\Services\Place\PlaceBlock;
use App\Services\Place\Block\PlaceBlockObserver;
use App\Services\Place\PlaceSubApply;
use App\Services\Place\SubApply\PlaceSubApplyObserver;
use App\Services\SystemNotify\SystemNofiyRepository;
use App\Services\SystemNotify\SystemNotifyContract;
use App\Services\SystemNotify\SystemNotifyRepository;
use App\Services\WeChatNotify\WeChatNotifyContract;
use App\Services\WeChatNotify\WeChatNotifyService;

class MyServiceProvider extends ServiceProvider
{

    public function boot()
    {
        Carbon::setLocale('zh');

        Like::observe(new LikeObserver);
        User::observe(new UserObserver);
        Comment::observe(new CommentObserver);
        Place::observe(new PlaceObserver);
        Post::observe(new PostObserver);
        CommentLike::observe(new CommentLikeObserver);
        PlaceBlock::observe(new PlaceBlockObserver);
        PlaceSubApply::observe(new PlaceSubApplyObserver);

        //        Collection::macro('loadMorph', function ($relation, $relations) {
        //            $this->pluck($relation)
        //                ->groupBy(function ($model) {
        //                    return get_class($model);
        //                })
        //                ->filter(function ($models, $className) use ($relations) {
        //                    return array_has($relations, $className);
        //                })
        //                ->each(function ($models, $className) use ($relations) {
        //                    $className::with($relations[$className])
        //                        ->eagerLoadRelations($models->all());
        //                });
        //
        //            return $this;
        //        });
    }

    public function register()
    {

        $app = $this->app;

        $app->bind(LikeContract::class, LikeRepository::class);
        $app->bind(AttachContract::class, AttachRepository::class);
        $app->bind(LoginContract::class, LoginRepository::class);
        $app->bind(PlaceContract::class, PlaceRepository::class);
        $app->bind(PlaceCategoryContract::class, PlaceCategoryRepository::class);
        $app->bind(UserContract::class, UserRepository::class);
        $app->bind(PostContract::class, PostRepository::class);
        $app->bind(CommentContract::class, CommentRepository::class);
        $app->bind(VerifyCodeContract::class, VerifyCodeRepository::class);
        $app->bind(HistoryPlaceContract::class, HistoryPlaceRepository::class);
        $app->bind(MsgNotifyContract::class, MsgNotifyRepository::class);
        $app->bind(ReportContract::class, ReportRepository::class);
        $app->bind(AdminContract::class, AdminRepository::class);
        $app->bind(RecommandContract::class, RecommandRepository::class);
        $app->bind(PlaceApplyContract::class, PlaceApplyRespository::class);
        $app->bind(CommentLikeContract::class, CommentLikeRepository::class);
        $app->bind(PlaceSubApplyContract::class, PlaceSubApplyRespository::class);
        $app->bind(PlaceBlockContract::class, PlaceBlockRepository::class);
        $app->bind(UserBlockContract::class, UserBlockRepository::class);
        $app->bind(WeChatNotifyContract::class, WeChatNotifyService::class);
        $app->bind(SystemNotifyContract::class, SystemNotifyRepository::class);
        /*
         * for My Facade
         */
        $app->bind('helper', function () {
            return new Helpers();
        });

        $app->bind('weChatNotify', function () use ($app) {
            return $app->make(WeChatNotifyContract::class);
        });
    }
}
