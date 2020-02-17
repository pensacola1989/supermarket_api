<?php

namespace App\Http\Transformers;

use App\Services\Tag\Tag;
use League\Fractal\TransformerAbstract;

class TagTransformer extends TransformerAbstract
{
    // private $_shouldIncludePlace = false;

    protected $placeTransfomer = null;

    public function __construct(PlaceTransfomer $placeTransfomer)
    {
        $this->placeTransfomer = $placeTransfomer;
    }

    // public function setShouldIncludePlace($flag)
    // {
    //     $this->_shouldIncludePlace = $flag;
    // }

    protected $availableIncludes = ['place'];

    protected $defaultIncludes = [];

    public function transform(Tag $tag)
    {
        return [
            'id' => $tag->id,
            'tagName' => $tag->tag_name,
            'tagColor' => $tag->color,
            'visible' => $tag->visible,
            'color' => $tag->color
        ];
    }

    public function includePlace(Tag $tag)
    {
        if ($tag->place) {
            return $this->item($tag->place, $this->placeTransfomer);
        }
    }
}
