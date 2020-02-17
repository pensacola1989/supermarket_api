<?php

namespace App\Http\Transformers;

use App\Services\Place\Place;
use League\Fractal\TransformerAbstract;
use App\Services\Place\PlaceSubApply;
use App\Services\Account\User;

class PlaceSubscriberTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'user'
    ];

    public function __construct()
    { }


    public function transform(User $user)
    {
        // dd($user->subscribes[0]->pivot);
        return [
            // 'id' => $placeSubApply->id,
            // 'status' => $placeSubApply->status,
            // 'content' => $placeSubApply->content,
            'createdAt' => $user->subscribes[0]->pivot->created_at ? $user->subscribes[0]->pivot->created_at->toDateTimeString() : null,
            'timeDiff' => timeDiffForHuman($user->subscribes[0]->pivot->created_at)
        ];
    }

    public function includeUser(User $user)
    {
        return $this->item($user, new UserTranformer());
    }
}
