<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/14/17
 * Time: 7:29 PM
 */

namespace App\Services\Comment;

use App;
use App\Services\Post\PostRepository;

class CommentObserver
{
    public function saved($model)
    {
        \Log::info('..............');
        // 自己给自己留言不要通知，待处理
        $postRepo = app()->make(App\Services\Post\PostContract::class);
        $userRepo = app()->make(App\Services\Account\UserContract::class);
        $wechatNotifyService = app()->make(App\Services\WeChatNotify\WeChatNotifyContract::class);
        $postRepo->inrementCommentNumber($model->post_id);

        // check if this is a reply comment
        if ($model->reply_id) {
            $commentRepo = app()->make(App\Services\Comment\CommentContract::class);
            $commentRepo->incrementReplyCount($model->reply_id);
        }
        // create notify data
        $model->Notify()->create([
            'notify_id' => $model->id,
            'user_id' => $model->to_uid
        ]);

        if ($model->Post->isMpLink) {
            // should update article_unread
            $model->Post()->update([
                'article_unread' => 1
            ]);
        }

        $openId = $userRepo->getOpenId($model->to_uid);

        if ($model->reply_id) {
            $wechatNotifyService->deliveryMsg($openId, config('wechat.scenes.reply'), $model->post_id, [
                "thing1" => ["value" => $model->Parent->content],
                "thing2" => ["value" => $model->content],
                "thing3" => ["value" => $model->FromUser->name],
                "time4" => ["value" => date('Y年m月d日 h:i', time() + 8 * 60 * 60)],
                "thing5" => ["value" => "点击查看详情"]
            ], ['parentId' => $model->reply_id]);
        } else {
            $wechatNotifyService->deliveryMsg($openId, config('wechat.scenes.comment'), $model->post_id, [
                "name1" => ["value" => $model->FromUser->name],
                "thing2" => ["value" => $model->content],
                "time3" => ["value" => date('Y年m月d日 h:i', time() + 8 * 60 * 60)],
                "thing4" => ["value" => "点击查看详情"]
            ], ['s' => 's']);
        }
        // if ($model->reply_id) {
        //     $wechatNotifyService->deliveryMsg($model->to_uid, config('wechat.scenes.reply'), $model->post_id, [
        //         date('Y年m月d日 h:i', time() + 8 * 60 * 60),
        //         $model->content,
        //         $model->FromUser->name,
        //         date('Y年m月d日 h:i', time() + 8 * 60 * 60)
        //     ], ['parentId' => $model->reply_id]);
        // } else {
        //     $wechatNotifyService->deliveryMsg($model->to_uid, config('wechat.scenes.comment'), $model->post_id, [
        //         date('Y年m月d日 h:i', time() + 8 * 60 * 60),
        //         $model->content,
        //         $model->FromUser->name
        //     ], ['s' => 's']);
        // }
    }


    public function deleted($model)
    {
        $postRepo = app()->make(App\Services\Post\PostContract::class);
        $postRepo->decrementCommentNumber($model->post_id);
    }
}
