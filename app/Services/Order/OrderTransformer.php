<?php

namespace App\Services\Order;

use App\Http\Transformers\AttachmentTransformer;
use App\Http\Transformers\PlaceTransfomer;
use App\Http\Transformers\UserTranformer;
use App\Services\Order\Order;
use App\Services\Order\OrderItem\OrderItemTransformer;
use League\Fractal\TransformerAbstract;

class OrderTransformer extends TransformerAbstract
{
    // protected $defaultIncludes = [
    //     'items',
    //     'custom',
    //     'store'
    // ];
    protected $availableIncludes = [
        'items',
        'custom',
        'store',
        'payScreeShot'
    ];

    private $userTransformer;

    private $orderItemTransformer;

    private $placeTransformer;

    private $attachmentTransformer;


    public function __construct(
        UserTranformer $userTranformer,
        OrderItemTransformer $orderItemTransformer,
        PlaceTransfomer $placeTransfomer,
        AttachmentTransformer $attachmentTransformer
    ) {
        $this->userTransformer =  $userTranformer;
        $this->orderItemTransformer = $orderItemTransformer;
        $this->placeTransformer = $placeTransfomer;
        $this->attachmentTransformer = $attachmentTransformer;
    }



    public function transform(Order $order)
    {
        return [
            'id' => $order->id,
            'customId' => $order->custom_id,
            'orderSn' => $order->order_sn,
            'mobile' => $order->mobile,
            'orderAmount' => $order->order_amount,
            'orderStatus' => $order->order_status,
            'payScreenShotId' => $order->pay_screenshot_id,
            'storeId' => $order->store_id,
            'hasPayed' => (bool) $order->pay_screenshot_id,
            'remark' => $order->remark,
            'createdAt' => $order->created_at->format('Y-m-d H:i'),
            'updateAt' => $order->updated_at->format('Y-m-d H:i'),
            'address' => json_decode($order->address)
        ];
    }

    public function includeItems(Order $order)
    {
        return $this->collection($order->items, $this->orderItemTransformer);
    }

    public function includeCustom(Order $order)
    {
        return $this->item($order->custom, $this->userTransformer);
    }

    public function includeStore(Order $order)
    {
        return $this->item($order->store, $this->placeTransformer);
    }

    public function includePayScreeShot(Order $order)
    {
        if (!$order->payScreeShot) {
            return null;
        }
        return $this->item($order->payScreeShot, $this->attachmentTransformer);
    }
}
