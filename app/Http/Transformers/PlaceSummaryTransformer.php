<?php

namespace App\Http\Transformers;

use App\Services\Place\Place;
use League\Fractal\TransformerAbstract;

class PlaceSummaryTransformer extends TransformerAbstract
{
    public function __construct()
    { }


    public function transform(Place $place)
    {
        return [
            'applierCount' => $place->userApply()->where('status', 0)->count(),
            'blocks' => $place->blocks()->count(),
            'subscribes' => $place->subscribes()->count(),
            'isPrivate' => $place->is_private
        ];
    }
}
