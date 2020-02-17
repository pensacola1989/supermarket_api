<?php
/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 8/4/17
 * Time: 9:10 PM
 */

namespace App\Services\Post;


class LikeWithPost extends Like
{
//    protected $table = 'likes';

    protected $with = [
        'User',
        'User.Avatar',
        'User.Logins',
        'Post',
        'Post.Photos',
        'Post.User',
        'Post.User.Avatar'
    ];
}