<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 8/17/17
 * Time: 12:50 AM
 */

namespace App\Services\Order;

use App\Exceptions\SystemErrors;
use App\Exceptions\UserErrors;
use App\Services\Account\User;
use App\Services\Core\EntityRepository;
use App\Services\Order\OrderItem\OrderItem;
use App\Services\Order\OrderItem\OrderItemService;
use App\Services\Post\Post;
use Illuminate\Support\Facades\DB;

class OrderService extends EntityRepository
{

    private $orderItemService = null;

    public function __construct(Order $model, OrderItemService $orderItemService)
    {
        $this->model = $model;
        $this->orderItemService = $orderItemService;
    }

    protected function constructQuery($criteria)
    {
        $query = $this->model;

        return $query;
    }

    protected function includeForQuery($query)
    {
        $query = $query->with(['items']);

        return $query;
    }

    protected function loadRelated($entity)
    {
        $entity->load(['items']);
    }

    public function createOrder($inputs)
    {
        $order = $this->getNew($inputs);
        $order->has_storage = 1;
        return DB::transaction(function () use ($order, $inputs) {
            $order->order_status = 1;
            $order->save();
            $order->items()->createMany($inputs['orderItems']);

            $order->load('items');

            return $order;
        });
    }

    public function updateOrder($sn, $model)
    {
        $order = $this->getOrderByOrderSn($sn);

        return DB::transaction(function () use ($order, $model) {
            $order->update($model);

            $this->removeItems($order, $model);
            collect($model['orderItems'])->each(function ($orderItem) use ($order) {
                $order->items()->updateOrCreate(['id' => $orderItem['id']], $orderItem);
            });

            $order->load('items');

            return $order;
        });
    }

    public function getOrderByOrderSn($orderSn)
    {
        $order =  $this->model->where('order_sn', $orderSn)->first();
        if (!$order) {
            throw UserErrors::EntityNotFound('order', $orderSn);
        }

        return $order;
    }

    private function removeItems($order, $model)
    {
        $ids = collect($model['orderItems'])
            ->pluck('id')
            ->filter(function ($m) {
                return $m !== null;
            })->values();

        $order->items()->whereNotIn('id', $ids)->delete();
    }


    protected function constructOrderBys(&$criteria, $query)
    {
        $query = $query->orderBy('id', 'desc');

        return $query;
    }
}
