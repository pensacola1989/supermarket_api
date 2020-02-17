<?php

namespace App\Services\Post;

use App\Services\Core\EntityContract;

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/15/17
 * Time: 12:07 PM
 */
interface LikeContract extends EntityContract
{
    public function createLike($attributes);

    public function getUserPostLike($userId, $postId);

    public function getUserLikesByPostIds(array $postIds, $userId);

}