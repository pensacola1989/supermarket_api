<?php

namespace App\Http\Transformers;

use App\Services\Comment\CommentWithPost;
use App\Services\MsgNotify\MsgNotify;
use App\Services\Post\LikeWithPost;
use League\Fractal\TransformerAbstract;
use App\Services\Comment\CommentLikeWithPost;

class NotifyTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['notifiable'];

    protected $notifableTransformer;

    public function __construct(NotifableTransformer $notifableTransformer)
    {
        $this->notifableTransformer = $notifableTransformer;
    }

    public function transform(MsgNotify $notify)
    {
        // $class = get_class($notify->notifiable);
        // dd($class);

        return [
            'createdAt' => $notify->created_at->toDateTimeString(),
            'id' => $notify->id,
            'notifyId' => $notify->notify_id,
            'notifyType' => $this->_getNotifyType($notify->notifiable),
            'userId' => $notify->user_id,
            'timeDiff' => timeDiffForHuman($notify->created_at),
        ];
    }

    private function _getNotifyType($notifiable)
    {
        // \Log::info(get_class($notifiable));
        $map = [
            CommentWithPost::class => 'comment',
            LikeWithPost::class => 'like',
            CommentLikeWithPost::class => 'commentLike'
        ];

        $class = get_class($notifiable);

        return isset($map[$class]) ? $map[$class] : null;
    }

    public function includeNotifiable(MsgNotify $notify)
    {
        if ($notify->notifiable) {
            return $this->item($notify->notifiable, $this->notifableTransformer);
        }
        return null;
    }
}
