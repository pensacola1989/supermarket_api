<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 11/3/17
 * Time: 3:04 PM
 */

namespace App\Services\Recommand;


use App\Services\Core\EntityRepository;

class RecommandRepository extends EntityRepository implements RecommandContract
{

    public function __construct(Recommand $model)
    {
        $this->model = $model;
    }

    protected function constructQuery($criteria)
    {
        $query = $this->model;

        return $query;
    }

    protected function includeForQuery($query)
    {
        $query = $query->with(['Place', 'Place.avatar', 'Place.cover', 'Place.Category']);
        return $query;
    }

    protected function loadRelated($entity)
    { }

    public function getRecomamndByOrder($order)
    {
        $hasOrder = $this->model->where('recommand_order', $order)->first();

        return $hasOrder;
    }

    protected function constructOrderBys(&$criteria, $query)
    {
        // $query = $query->orderBy('recommand_order', 'asc');

        return $query;
    }
}
