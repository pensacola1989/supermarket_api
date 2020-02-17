<?php

namespace  App\Services\Comment;

use App\Services\Core\EntityContract;

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/15/17
 * Time: 12:11 PM
 */
interface CommentLikeContract extends EntityContract
{
    public function createCommentLike($attributes);

    public function getUserCommentLike($commentId, $userId);

    public function getUserLikesByCommentIds(array $commentIds, $userId);
}
