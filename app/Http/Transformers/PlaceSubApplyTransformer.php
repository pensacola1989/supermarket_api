<?php

namespace App\Http\Transformers;

use App\Services\Place\Place;
use League\Fractal\TransformerAbstract;
use App\Services\Place\PlaceSubApply;

class PlaceSubApplyTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'user'
    ];

    public function __construct()
    { }


    public function transform(PlaceSubApply $placeSubApply)
    {
        return [
            'id' => $placeSubApply->id,
            'status' => $placeSubApply->status,
            'content' => $placeSubApply->content,
            'created_at' => $placeSubApply->created_at ? $placeSubApply->created_at->toDateTimeString() : null,
            'timeDiff' => timeDiffForHuman($placeSubApply->created_at)
        ];
    }

    public function includeUser(PlaceSubApply $placeSubApply)
    {
        return $this->item($placeSubApply->user, new UserTranformer());
    }
}
