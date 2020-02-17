<?php

namespace App\Http\Transformers;

use App\Services\Comment\CommentWithPost;
use App\Services\Post\LikeWithPost;
use League\Fractal\TransformerAbstract;
use App\Services\Comment\CommentLikeWithPost;

class NotifableTransformer extends TransformerAbstract
{
    protected $postTransformer;

    protected $commentTransformer;

    protected $userTransformer;

    public function __construct(
        PostTransformer $postTransformer,
        CommentTransformer $commentTransformer,
        UserTranformer $userTransformer
    ) {
        $this->postTransformer = $postTransformer;
        $this->commentTransformer = $commentTransformer;
        $this->userTransformer = $userTransformer;
    }

    protected $availableIncludes = [];

    public function transform($notifable)
    {
        $notifableResponse = [
            'content' => $notifable->content,
            'createdAt' => $notifable->created_at,
            'fromUid' => $notifable->from_uid,
            'id' => $notifable->id,
            'replyId' => $notifable->reply_id,
        ];
        if ($notifable instanceof CommentWithPost) {
            $parent = fractal($notifable->Parent, $this->commentTransformer);
            $notifableResponse['replyCount'] = $notifable->reply_count;
            $notifableResponse['likeCount'] = $notifable->like_count;
            $notifableResponse['parent'] = $parent;
            $notifableResponse['toUser'] = fractal($notifable->ToUser, $this->userTransformer);
            $notifableResponse['post'] = fractal($notifable->Post, $this->postTransformer);
            // 如果帖子没有被删除
            if ($notifable->Post) {
                $shouldProcessAnonymous = $notifable->Post && $notifable->Post->User->id === $notifable->FromUser->id;
                $userShouldAnonymous = $shouldProcessAnonymous && $notifable->Post->is_anonymous;
                $notifableResponse['fromUser'] = fractal($notifable->FromUser, (new UserTranformer())->setAnonymous($userShouldAnonymous));
            } else {
                $notifableResponse['fromUser'] = fractal($notifable->FromUser, $this->userTransformer);
            }
        }
        if ($notifable instanceof LikeWithPost) {
            // 不需要作匿名判断
            $notifableResponse['post'] = fractal($notifable->Post, $this->postTransformer);
            $notifableResponse['user'] = fractal($notifable->User, $this->userTransformer);
        }
        if ($notifable instanceof CommentLikeWithPost) {
            $notifableResponse['comment'] = fractal($notifable->Comment, $this->commentTransformer);
            if (isset($notifable->Comment)) {
                $notifableResponse['post'] = fractal($notifable->Comment->Post, $this->postTransformer);
            }
            if ($notifable->Post) {
                $shouldProcessAnonymous = $notifable->Post && $notifable->Post->User->id === $notifable->FromUser->id;
                $userShouldAnonymous = $shouldProcessAnonymous && $notifable->Post->is_anonymous;
                $notifableResponse['user'] = fractal($notifable->FromUser, (new UserTranformer())->setAnonymous($userShouldAnonymous));
            } else {
                $notifableResponse['user'] = fractal($notifable->User, $this->userTransformer);
            }
        }
        return $notifableResponse;
    }
}
