<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 8/17/17
 * Time: 12:50 AM
 */

namespace App\Services\Order\OrderItem;

use App\Exceptions\SystemErrors;
use App\Exceptions\UserErrors;
use App\Services\Account\User;
use App\Services\Core\EntityRepository;
use App\Services\Order\OrderItem\OrderItem;
use App\Services\Post\Post;
use Illuminate\Support\Facades\DB;

class OrderItemService extends EntityRepository
{

    public function __construct(OrderItem $model)
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
        return $query;
    }

    protected function loadRelated($entity)
    {
        // TODO: Implement loadRelated() method.
    }

    public function createOrderItem($inputs)
    {
    }

    protected function constructOrderBys(&$criteria, $query)
    {
        return $query;
    }
}
