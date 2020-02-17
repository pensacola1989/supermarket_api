<?php

namespace App\Http\Transformers;

use App\Services\Place\Place;
use League\Fractal\TransformerAbstract;
use App\Services\Place\PlaceSubApply;
use App\Services\Place\PlaceBlock;

class PlaceBlockTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'user'
    ];

    public function __construct()
    { }


    public function transform(PlaceBlock $placeBlock)
    {
        return [
            'id' => $placeBlock->id,
            'created_at' => $placeBlock->created_at ? $placeBlock->created_at->toDateTimeString() : null,
            'timeDiff' => timeDiffForHuman($placeBlock->created_at)
        ];
    }

    public function includeUser(PlaceBlock $placeBlock)
    {
        return $this->item($placeBlock->user, new UserTranformer());
    }
}
