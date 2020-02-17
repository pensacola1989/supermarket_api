<?php

namespace App\Services\Order\OrderItem;

use App\Services\Order\OrderItem\OrderItem;
use League\Fractal\TransformerAbstract;

class OrderItemTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        // 'order',
    ];


    public function __construct()
    {
    }



    public function transform(OrderItem $orderItem)
    {
        return [
            'id' => $orderItem->id,
            'orderId' => $orderItem->id,
            'name' => $orderItem->name,
            'remark' => $orderItem->remark,
            'unit' => $orderItem->unit,
            'hasStorage' => $orderItem->has_storage,
            'amount' => $orderItem->goods_amount,
            'price' => $orderItem->price,
            'comment' => $orderItem->comment,
            'createdAt' => $orderItem->created_at->format('Y-m-d H:i'),
            'updatedAt' => $orderItem->updated_at->format('Y-m-d H:i')
        ];
    }

    // public function includeOrder(OrderItem $order)
    // {
    // }
}
