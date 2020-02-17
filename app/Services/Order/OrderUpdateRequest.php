<?php

namespace App\Services\Order;

use App\Services\Core\BaseRequest;

class OrderUpdateRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'customId' => 'required|alpha_num|exists:users,id',
            'storeId' => 'required|alpha_num|exists:places,id',
            'orderSn' => 'required|alpha_num',
            'orderAmount' => 'required|alpha_num',
            'orderStatus' => 'required|alpha_num',
            'orderItems' => 'required|array'
        ];
    }

    public function authorize()
    {
        return true;
    }

    public function toEntity()
    {
        return [
            'id' => $this->id,
            'custom_id' => $this->customId,
            'store_id' => $this->storeId,
            'order_sn' => $this->orderSn,
            'order_amount' => $this->orderAmount,
            'order_status' => $this->orderStatus,
            'orderItems' => array_map(function ($item) {
                return [
                    'id' => isset($item['id']) ? $item['id'] : null,
                    'name' => $item['name'],
                    'comment' => $item['comment'],
                    'goods_amount' => $item['amount'],
                    'order_id' => isset($item['orderId']) ? $item['orderId'] : null,
                    'remark' => isset($item['remark']) ? $item['remark'] : '',
                    'price' => isset($item['price']) ? $item['price'] : 0,
                    'unit' => isset($item['unit']) ? $item['unit'] : '',
                    'has_storage' => isset($item['hasStorage']) ? $item['hasStorage'] : true,
                ];
            }, $this->orderItems)
        ];
    }
}
