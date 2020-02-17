<?php

namespace  App\Services\Comment;

use App\Services\Core\EntityContract;

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/15/17
 * Time: 12:11 PM
 */
interface CommentContract extends EntityContract
{
    public function createComment($attributes);

    public function incrementReplyCount($commentId);

    public function getUserCommentsCountOfPost($userIds, $postId);
}
