<?php

namespace App\Services\Order;

use MyHelper;
use App\Services\Core\BaseRequest;

class OrderSearchRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'storeId' => 'required_without:customId|alpha_num|exists:users,id',
            'customId' => 'required_without:storeId|alpha_num|exists:users,id'
        ];
    }

    public function authorize()
    {
        $user = $this->getCurrentUser();
        $canSearch  = in_array($user->id, [$this->storeId, $this->customId]);

        return $canSearch;
    }

    public function toEntity()
    {
        return [
            'store_id' => $this->storeId,
            'custom_id' => $this->customId
        ];
    }
}
