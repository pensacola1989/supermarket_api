<?php

namespace App\Services\Order;

use MyHelper;
use App\Services\Core\BaseRequest;

class OrderCreateRequest extends BaseRequest
{
    public function rules()
    {
        return [
            // 'customId' => 'required|alpha_num|exists:users,id',
            'storeId' => 'required|alpha_num|exists:places,id',
            'mobile' => 'required|alpha_num',
            'orderItems' => 'required|array',
            'orderItems.*.name' => 'required',
            'orderAmount' => 'required|alpha_num',
            // 'orderItems.*.order_id' => 'required|alpha_num|exists:orders,id',
        ];
    }

    public function authorize()
    {
        return true;
    }

    public function toEntity()
    {
        return [
            'store_id' => $this->storeId,
            'mobile' => $this->mobile,
            'order_sn' => MyHelper::newId(),
            'order_amount' => $this->orderAmount,
            'orderItems' => array_map(function ($item) {
                return [
                    'name' => $item['name'],
                    // 'order_id' => $item['orderId'],
                    'goods_amount' => $item['amount'],
                    'remark' => isset($item['remark']) ? $item['remark'] : '',
                    'price' => isset($item['price']) ? $item['price'] : 0,
                    'unit' => isset($item['unit']) ? $item['unit'] : ''
                ];
            }, $this->orderItems)
        ];
    }
}
