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

class PlaceBlockRepository extends EntityRepository implements PlaceBlockContract
{

    public function __construct(PlaceBlock $placeBlock)
    {
        $this->model = $placeBlock;
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
        // if (isset($criteria['adminId'])) {
        //     $query = $query->whereHas('user', function ($query) use ($criteria) {
        //         $query->where('id', '<>', $criteria['adminId']);
        //     });
        // }

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
        // $query = $query->orderBy('status', 'asc');

        return $query;
    }

    public function createPlaceBlock($attribute)
    { }

    public function removeBlockUser($placeId, $userId)
    {
        $this->model
            ->where([
                ['user_id', '=', $userId],
                ['place_id', '=', $placeId]
            ])
            ->delete();
    }

    // public function approvePlaceSubApply($userId, $placeId)
    // {
    //     return $this->model
    //         ->where([
    //             ['user_id', '=', $userId],
    //             ['place_id', '=', $placeId]
    //         ])
    //         ->update(['status' => 1]);
    // }
}
