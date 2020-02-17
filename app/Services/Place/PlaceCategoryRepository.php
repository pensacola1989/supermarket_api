<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/15/17
 * Time: 12:11 PM
 */

namespace App\Services\Place;


use App\Services\Core\EntityContract;
use App\Services\Core\EntityRepository;

class PlaceCategoryRepository extends EntityRepository implements PlaceCategoryContract
{

    public function __construct(PlaceCategories $model)
    {
        $this->model = $model;
    }

    protected function constructQuery($criteria)
    {
        $query = $this->model;

        return $query;
    }

    public function createPlaceCategory(array $placeCategory)
    {
        return $this->createModel($placeCategory);
    }

    protected function includeForQuery($query)
    {
        return $query;
        //        return $query->with('Places');
    }

    protected function loadRelated($entity)
    {
        // TODO: Implement loadRelated() method.
    }

    protected function constructOrderBys(&$criteria, $query)
    {
        return $query;
    }
}
