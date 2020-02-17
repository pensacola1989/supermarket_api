<?php

namespace App\Http\Transformers;

use App\Services\HistoryPlace\HistoryPlace;
use League\Fractal\TransformerAbstract;

class HistoryTransformer extends TransformerAbstract
{
    protected $availableIncludes = [];

    protected $defaultIncludes = [
        'place',
    ];

    private $userSubscribeIds = null;

    private $placeTransfomer;

    public function __construct(PlaceTransfomer $placeTransfomer)
    {
        $this->placeTransfomer = $placeTransfomer;
    }

    public function setUserSubscribePlaceIds($ids)
    {
        $this->userSubscribeIds = $ids;
    }

    public function transform(HistoryPlace $history)
    {
        return [
            'newsCount' => $history->count ?? 0,
            'id' => $history->id,
            'placeId' => $history->place_id,
            'place' => $history->Places,
            'userId' => $history->user_id,
            'hasSub' => $history->has_sub,
            'timeDiff' => timeDiffForHuman($history->updated_at),
            'createdAt' => $history->created_at ? $history->created_at->toDateTimeString() : null,
            'updatedAt' => $history->updated_at ? $history->updated_at->toDateTimeString() : null,
        ];
    }

    public function includePlace(HistoryPlace $history)
    {
        $place = $history->Places;

        return $this->item($place, $this->placeTransfomer);
    }
}
