<?php

namespace App\Http\Transformers;

use App\Services\Post\Like;
use League\Fractal\TransformerAbstract;

class LikeTransformer extends TransformerAbstract
{
    protected $availableIncludes = [];

    /**
     * like action should not be anonymous
     *
     * @param Like $like
     * @return void
     */
    public function transform(Like $like)
    {
        return [
            'id' => $like->id,
            'userName' => $like->User ? $like->User->name : '未知用户',
            'userId' => $like->User ? $like->User->external_id : -1,
            'avatarUrl' => $like->User ? getUserAvatar($like->User) : '',
            'createdAt' => $like->created_at->toDateTimeString(),
            'timeDiff' => timeDiffForHuman($like->created_at)
        ];
    }
}
