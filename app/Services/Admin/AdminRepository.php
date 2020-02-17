<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 10/31/17
 * Time: 4:19 PM
 */

namespace App\Services\Admin;

use App\Services\Core\EntityRepository;

class AdminRepository extends EntityRepository implements AdminContract
{

    public function __construct(Admin $model)
    {
        $this->model = $model;
    }

    protected function constructQuery($criteria)
    {
        // TODO: Implement constructQuery() method.
    }

    protected function includeForQuery($query)
    {
        // TODO: Implement includeForQuery() method.
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
