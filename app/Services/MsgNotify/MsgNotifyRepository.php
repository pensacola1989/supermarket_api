<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 8/4/17
 * Time: 12:26 AM
 */

namespace App\Services\MsgNotify;


use App\Services\Comment\Comment;
use App\Services\Core\EntityRepository;
use App\Services\Post\Like;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class MsgNotifyRepository extends EntityRepository implements MsgNotifyContract
{

    public function __construct(MsgNotify $model)
    {
        $this->model = $model;
    }

    protected function constructQuery($criteria)
    {
        $query = $this->model;
        if (isset($criteria['user_id'])) {
            $query = $query->where('user_id', $criteria['user_id']);
        }
        if (isset($criteria['sinceId'])) {
            $query = $query->where('id', '>', $criteria['sinceId']);
        }
        if (isset($criteria['maxId'])) {
            $query = $query->where('id', '<', $criteria['maxId']);
        }
        $query = $query->whereHasMorph('notifiable', [
            'App\Services\Comment\Comment',
            'App\Services\Post\Like',
            'App\Services\Comment\CommentLike'
        ], function (Builder $query, $type) {
            $query->whereNotNull('id');
            // if ($type === 'App\Services\Comment\Comment' || $type === 'App\Services\Post\Like') {
            //     $query->whereHas('Post', function ($q) {
            //         $q->whereNull('mp_link');
            //     });
            // }
            // if ($type === 'App\Services\Comment\CommentLike') {
            //     $query->whereHas('Comment.Post', function ($q) {
            //         $q->whereNull('mp_link');
            //     });
            // }
        });
        //        if (isset($criteria['startTime'])) {
        //            $startTime = Carbon::parse($criteria['startTime'])->toDateTimeString();
        //            $query = $query->where('created_at', '>', $startTime);
        //        }
        //        if (isset($criteria['loadMorePoint'])) {
        //            $loadMorePoint = Carbon::parse($criteria['loadMorePoint'])->toDateTimeString();
        //            $query = $query->where('created_at', '<', $loadMorePoint);
        //        }

        return $query;
    }

    protected function includeForQuery($query)
    {

        $query = $query->with([
            'notifiable'
        ]);

        return $query;
    }

    protected function loadRelated($entity)
    { }

    protected function constructOrderBys(&$criteria, $query)
    {
        return $query;
    }

    public function getNotifyCount($userId, $newPoint)
    {
        return $this->model
            ->where('user_id', $userId)
            ->whereHasMorph('notifiable', [
                'App\Services\Comment\Comment',
                'App\Services\Post\Like',
                'App\Services\Comment\CommentLike'
            ], function (Builder $query, $type) {
                $query->whereNotNull('id');
                // 如果是关联公众号帖子的留言（评论），则不计数，也就是不加入前端的显示数量
                // 而是通过另一个接口，单独引导管理员去管理留言界面管理
                if ($type === 'App\Services\Comment\Comment') {
                    $query->whereHas('Post', function ($q) {
                        $q->whereNull('mp_link');
                    });
                }
            })
            ->where('created_at', '>', $newPoint)
            ->count();
    }
}
