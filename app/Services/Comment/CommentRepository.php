<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/15/17
 * Time: 12:12 PM
 */

namespace App\Services\Comment;


use App\Services\Account\User;
use App\Services\Core\EntityContract;
use App\Services\Core\EntityRepository;
use App\Services\Post\Post;
use Carbon\Carbon;

class CommentRepository extends EntityRepository implements CommentContract
{
    protected $userContext;

    protected $postContext;

    protected $commentLikeRepository;

    public function __construct(Comment $model, User $userContext, Post $postContext, CommentLikeContract $commentLikeContract)
    {
        $this->model = $model;
        $this->userContext = $userContext;
        $this->postContext = $postContext;
        $this->commentLikeRepository = $commentLikeContract;
    }

    protected function constructQuery($criteria)
    {
        $query = $this->model;

        if (isset($criteria['postId'])) {
            $post = $this->postContext->where('external_id', $criteria['postId'])->first();
            if ($post) {
                $query = $query->where('post_id', $post->id);
            }
        }

        if (isset($criteria['sinceId'])) {
            $query = $query->where('id', '>', $criteria['sinceId']);
        }

        if (isset($criteria['maxId'])) {
            $query = $query->where('id', '<', $criteria['maxId']);
        }
        //        if (isset($criteria['startTime'])) {
        //            $startTime = Carbon::parse($criteria['startTime'])->toDateTimeString();
        //            $query = $query->where('created_at', '>', $startTime);
        //        }
        //
        //        if (isset($criteria['loadOldPoint'])) {
        //            $loadOldPoint = Carbon::parse($criteria['loadOldPoint'])->toDateTimeString();
        //            $query = $query->where('created_at', '<', $loadOldPoint);
        //        }

        if (isset($criteria['fromUserId'])) {
            $query = $query->where('from_uid', $criteria['fromUserId']);
        }

        if (isset($criteria['toUserId'])) { }

        // 如果是对某个评论对“回复”查询
        if (isset($criteria['parentId'])) {
            $query = $query->where('reply_id', $criteria['parentId']);
        } elseif (!isset($criteria['shouldFlatternComments'])) {
            // 对某个帖子的查询，查第一级
            $query = $query->whereNull('reply_id');
        }

        // 评论在帖子显示的时候只显示精选评论，如果是个人相关的，xxx的评论，则全部显示
        // 在CommentConotroller->getMyComments() 这个接口限定了用户只能看自己的包含非精选的评论
        if (!isset($criteria['userCanCheckAll'])) {
            $query = $query->where('visible', 1);
        }



        return $query;
    }

    protected function includeForQuery($query)
    {
        $query = $query->with([
            'Parent',
            'Parent.ToUser',
            'Post',
            'Post.Photos',
            'Post.User',
            'Post.User.Logins',
            'Post.User.Avatar',
            'FromUser',
            'FromUser.Avatar',
            'FromUser.Logins',
            'ToUser',
            'subComments' => function ($q) {
                return $q->limit(5)->orderBy('id', 'desc');
            }
        ]);

        return $query;
    }

    public function createComment($attributes)
    {
        $fromUser = $this->userContext->findOrFail($attributes['from_uid']);
        $toUser = $this->userContext->findOrFail($attributes['to_uid']);
        $post = $this->postContext->findOrFail($attributes['post_id']);

        $comment = $this->getNew($attributes);

        $comment->FromUser()->associate($fromUser);
        $comment->ToUser()->associate($toUser);
        $comment->Post()->associate($post);

        if (isset($attributes['reply_id'])) {
            $parentComment = $this->requireById($attributes['reply_id']);
            $comment->Parent()->associate($parentComment);
        }
        $comment->save();
        $newComment = $this->requireById($comment->id);

        return $newComment;
    }

    protected function loadRelated($entity)
    {
        $entity->load('FromUser', 'FromUser.Avatar', 'FromUser.Logins', 'ToUser', 'ToUser.Logins');
    }

    protected function constructOrderBys(&$criteria, $query)
    {
        // if ($criteria['sortBy'] === 'hot') {
        //     $query = $query->orderBy('like_count', 'desc');
        // }
        if (!isset($criteria['userCanCheckAll'])) {
            $query = $query->orderBy('is_top', 'desc');
        }
        if (isset($criteria['sortBy']) && $criteria['sortBy'] === 'like_count') {
            $query = $query
                ->orderBy('like_count', 'desc')
                ->orderBy('reply_count', 'desc')
                ->orderBy('id', 'desc');
        }
        if (isset($criteria['sortBy']) && $criteria['sortBy'] === 'id') {
            $query = $query->orderBy('id', 'desc');
        }
        // $query = $query->orderBy('id', 'desc');

        return $query;
    }

    public function incrementReplyCount($commentId)
    {
        $comment = $this->requireById($commentId);
        $comment->increment('reply_count');
    }

    public function getUserCommentsCountOfPost($userIds, $postId)
    {
        $userIdWithCounts = $this->model
            ->select(\DB::raw('COUNT(1) as user_comments_count'), 'from_uid')
            ->whereIn('from_uid', $userIds)
            ->where('post_id', $postId)
            ->groupBy('from_uid')
            ->get();

        $ret = [];

        \Log::info($userIdWithCounts);
        $userIdWithCounts->each(function ($userIdWithCount) use (&$ret) {
            $ret[$userIdWithCount->from_uid] = $userIdWithCount->user_comments_count;
        });

        \Log::info($ret);

        return $ret;
    }
}
