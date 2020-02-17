<?php
/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/21/17
 * Time: 10:21 PM
 */

namespace App\Services\Post;


// dislike_number is deprecated
class LikeObserver
{
    public function created($model)
    {
        $likeField = app()->make(PostRepository::class)->getById($model->post_id);
        if ($model->is_like == 1) {
            $likeField->increment('like_number');
            if ($model->user_id != $likeField->User->id) {
                $model->Notify()->create([
                    'notify_id' => $model->id,
                    'user_id' => $likeField->user_id
                ]);
            }
        } else {
            $likeField->decrement('like_number');
        }
    }

    public function deleted($model)
    {
        $likeField = app()->make(PostRepository::class)->getById($model->post_id);
        if ($model->is_like == 1) {
            $likeField->decrement('like_number');
        } else {
            $likeField->increment('like_number');
        }
    }
}