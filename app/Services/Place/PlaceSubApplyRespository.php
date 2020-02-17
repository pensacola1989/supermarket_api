<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/15/17
 * Time: 12:09 PM
 */

namespace App\Services\Place;

use App\Services\Attachments\Attachment;
use App\Services\Core\EntityRepository;
use MyHelper;
use Illuminate\Support\Facades\DB;

class PlaceSubApplyRespository extends EntityRepository implements PlaceSubApplyContract
{

    public function __construct(PlaceSubApply $placeSubApply)
    {
        $this->model = $placeSubApply;
    }



    protected function constructQuery($criteria)
    {
        $query = $this->model;

        if (isset($criteria['placeId'])) {
            $query = $query->where('place_id', $criteria['placeId']);
        }
        if (isset($criteria['searchName'])) {
            $query = $query->whereHas('user', function ($query) use ($criteria) {
                $query->where('name', 'like', '%' . $criteria['searchName'] . '%');
            });
        }

        return $query;
    }

    protected function includeForQuery($query)
    {
        $query = $query->with(['user']);

        return $query;
    }

    protected function loadRelated($entity)
    {
        $entity->load(['avatar', 'cover', 'Category']);
        // TODO: Implement loadRelated() method.
    }



    protected function constructOrderBys(&$criteria, $query)
    {
        $query = $query->orderBy('status', 'asc');

        return $query;
    }

    public function approvePlaceSubApply($userId, $placeId)
    {
        // DB::transaction(function () use ($placeId, $userId) {
        return $this->model
            ->where([
                ['user_id', '=', $userId],
                ['place_id', '=', $placeId]
            ])
            ->first()
            ->update(['status' => 1]);
        // add subscription data

        // });
    }
}
