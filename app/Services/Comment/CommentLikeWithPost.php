<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 8/4/17
 * Time: 9:10 PM
 */

namespace App\Services\Comment;

use App\Services\Comment\CommentLike;

class CommentLikeWithPost extends CommentLike
{
    //    protected $table = 'likes';

    protected $with = [
        'User',
        'User.Avatar',
        'User.Logins',
        'Comment'
    ];
}
