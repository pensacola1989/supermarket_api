<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 8/17/17
 * Time: 12:50 AM
 */

namespace App\Services\SystemNotify;


use App\Services\Core\EntityRepository;

class SystemNotifyRepository extends EntityRepository implements SystemNotifyContract
{

    public function __construct(SystemNotify $model)
    {
        $this->model = $model;
    }

    protected function constructQuery($criteria)
    {
        $query = $this->model;

        $query = $query->where('visible', 1);

        return $query;
    }

    protected function includeForQuery($query)
    {
        return $query;
    }

    protected function loadRelated($entity)
    {
        // TODO: Implement loadRelated() method.
    }

    protected function constructOrderBys(&$criteria, $query)
    {
        return $query;
    }

    public function getUserUnreadSystemNotifyNum($userLastReadTime)
    {
        $userLastReadTime = $userLastReadTime ?? 0;
        return $this->model->where('created_at', '>', $userLastReadTime)->count();
    }
}
