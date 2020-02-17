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

class CommentLikeRepository extends EntityRepository implements CommentLikeContract
{
    protected $userContext;

    protected $postContext;

    public function __construct(CommentLike $model, User $userContext, Post $postContext)
    {
        $this->model = $model;
        $this->userContext = $userContext;
        $this->postContext = $postContext;
    }

    protected function constructQuery($criteria)
    {
        $query = $this->model;

        // if (isset($criteria['postId'])) {
        //     $post = $this->postContext->where('external_id', $criteria['postId'])->first();
        //     if ($post) {
        //         $query = $query->where('post_id', $post->id);
        //     }
        // }

        // if (isset($criteria['sinceId'])) {
        //     $query = $query->where('id', '>', $criteria['sinceId']);
        // }

        // if (isset($criteria['maxId'])) {
        //     $query = $query->where('id', '<', $criteria['maxId']);
        // }

        // if (isset($criteria['fromUserId'])) {
        //     $query = $query->where('from_uid', $criteria['fromUserId']);
        // }

        // if (isset($criteria['toUserId'])) { }
        return $query;
    }

    public function createCommentLike($attributes)
    {
        $commentLike = $this->getNew($attributes);
        return $commentLike->save();
    }

    public function getUserCommentLike($commentId, $userId)
    {
        return $this->model->where('comment_id', $commentId)->where('user_id', $userId)->first();
    }

    public function getUserLikesByCommentIds(array $commentIds, $userId)
    {
        $likes = $this->model
            ->Where('user_id', '=', $userId)
            ->where(function ($q) use ($commentIds) {
                $q->whereIn('comment_id', $commentIds);
            })
            ->get();

        return $likes;
    }

    protected function includeForQuery($query)
    {
        // $query = $query->with([
        //     'Parent',
        //     'Parent.ToUser',
        //     'Post',
        //     'Post.Photos',
        //     'Post.User',
        //     'Post.User.Logins',
        //     'Post.User.Avatar',
        //     'FromUser',
        //     'FromUser.Avatar',
        //     'FromUser.Logins',
        //     'ToUser'
        // ]);

        return $query;
    }

    // public function createComment($attributes)
    // {
    //     $fromUser = $this->userContext->findOrFail($attributes['from_uid']);
    //     $toUser = $this->userContext->findOrFail($attributes['to_uid']);
    //     $post = $this->postContext->findOrFail($attributes['post_id']);

    //     $comment = $this->getNew($attributes);

    //     $comment->FromUser()->associate($fromUser);
    //     $comment->ToUser()->associate($toUser);
    //     $comment->Post()->associate($post);

    //     if (isset($attributes['reply_id'])) {
    //         $parentComment = $this->requireById($attributes['reply_id']);
    //         $comment->Parent()->associate($parentComment);
    //     }
    //     $comment->save();
    //     $newComment = $this->requireById($comment->id);

    //     return $newComment;
    // }

    protected function loadRelated($entity)
    {
        // $entity->load('FromUser', 'FromUser.Avatar', 'FromUser.Logins', 'ToUser', 'ToUser.Logins');
    }

    protected function constructOrderBys(&$criteria, $query)
    {
        return $query;
    }
}
