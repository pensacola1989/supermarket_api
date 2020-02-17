<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/14/17
 * Time: 7:29 PM
 */

namespace App\Services\Comment;


class CommentLikeObserver
{
    public function saved($model)
    {
        $this->_changeCommentLikeCount($model, 1);
    }

    public function deleted($model)
    {
        $this->_changeCommentLikeCount($model, -1);
    }

    private function _changeCommentLikeCount($model, $step)
    {
        $commentId = $model->comment_id;
        $commentRepo = app()->make(CommentContract::class);
        $relatedComment = $commentRepo->getById($commentId);
        $relatedComment->increment('like_count', $step);
        if ($model->user_id != $relatedComment->FromUser->id) {
            $model->Notify()->create([
                'notify_id' => $model->id,
                'user_id' => $relatedComment->FromUser->id
            ]);
        }
    }
}
