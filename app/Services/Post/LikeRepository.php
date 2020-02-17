<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/15/17
 * Time: 12:08 PM
 */

namespace App\Services\Post;

use App\Services\Account\User;
use App\Services\Core\EntityRepository;

class LikeRepository extends EntityRepository implements LikeContract
{
    protected $postModel;

    protected $userModel;

    public function __construct(Like $model, Post $postModel, User $userModel)
    {
        $this->model = $model;
        $this->postModel = $postModel;
        $this->userModel = $userModel;
    }

    public function createLike($attributes)
    {
        $post = $this->postModel->findOrFail($attributes['post_id']);
        $user = $this->userModel->findOrFail($attributes['user_id']);

        unset($attributes['post_id']);
        unset($attributes['user_id']);

        $like = $this->getNew($attributes);
        $like->User()->associate($user);
        $like->Post()->associate($post);
        $like->save();

        return $like;
    }

    public function getUserLikesByPostIds(array $postIds, $userId)
    {
        $likes = $this->model
            ->Where('user_id', '=', $userId)
            ->where(function ($q) use ($postIds) {
                $q->whereIn('post_id', $postIds);
            })
            ->get();

        return $likes;
    }

    public function getUserPostLike($postId, $userId)
    {
        return $this->model->where('user_id', $userId)->where('post_id', $postId)->first();
    }

    protected function constructQuery($criteria)
    {
        $query = $this->model;

        if (isset($criteria['postId'])) {
            $query = $query
                ->with(['User' => function ($q) {
                    $q->select('id', 'avatar_id', 'name', 'external_id');
                }, 'User.Avatar', 'User.Logins'])
                ->where('post_id', $criteria['postId']);
        }

        if (isset($criteria['like_user_id'])) {
            $query = $query
                ->where('user_id', $criteria['like_user_id'])
                ->whereNotIn('post_id', function ($q) use ($criteria) {
                    $q->select('id')
                        ->from(with(new Post)->getTable())
                        ->where('user_id', $criteria['like_user_id']);
                });
        } elseif (isset($criteria['be_liked_user_id'])) {
            $query = $query
                ->whereIn('post_id', function ($q) use ($criteria) {
                    $q->select('id')
                        ->from(with(new Post)->getTable())
                        ->where('user_id', $criteria['be_liked_user_id']);
                })
                ->where('user_id', '<>', $criteria['be_liked_user_id']);
        }

        return $query;
    }

    protected function includeForQuery($query)
    {
        //        $query = $query->with(['User']);

        return $query;
    }

    protected function loadRelated($entity)
    {
        // TODO: Implement loadRelated() method.
    }

    protected function constructOrderBys(&$criteria, $query)
    {
        return $query;
    }
}
