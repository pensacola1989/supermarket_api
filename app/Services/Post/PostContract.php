<?php

namespace App\Services\Post;

use App\Services\Core\EntityContract;


/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/15/17
 * Time: 12:06 PM
 */
interface PostContract extends EntityContract
{
    public function inrementCommentNumber($postId);

    public function createPost($attribute);

    public function updatePost($postId, $attribute);

    public function getStickTops($placeId);

    public function getPhotoWall($placeId);

    public function getNearbySinceId($latlng);

    public function getPosterBlockUsers($userId);

    public function decrementCommentNumber($postId);

    public function getMyNewArticleCommentsCount($adminId);
}
