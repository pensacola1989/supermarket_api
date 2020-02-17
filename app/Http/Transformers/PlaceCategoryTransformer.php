<?php

namespace App\Http\Transformers;

use App\Services\Post\Like;
use League\Fractal\TransformerAbstract;
use App\Services\Place\PlaceCategories;

class PlaceCategoryTransformer extends TransformerAbstract
{
    protected $availableIncludes = [];

    public function transform(PlaceCategories $placeCategorie)
    {
        return [
            'id' => $placeCategorie->id,
            'name' => $placeCategorie->name
        ];
    }
}
