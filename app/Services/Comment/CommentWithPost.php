<?php
/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 8/4/17
 * Time: 9:12 PM
 */

namespace App\Services\Comment;

class CommentWithPost extends Comment
{
//    protected $table = ['comments'];

    protected $with = [
        'Parent',
        'Parent.ToUser',
        'Post',
        'Post.Photos',
        'Post.User',
        'Post.User.Avatar',
        'FromUser',
        'FromUser.Avatar',
        'FromUser.Logins',
        'ToUser.Avatar',
        'ToUser.Logins'
    ];
}