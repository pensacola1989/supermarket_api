<?php

namespace App\Services\Account\Block;

use App\Services\Core\BaseRequest;

class UserBlockRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'userId' => 'required|alpha_num|exists:users,id',
            'blockUserId' => 'required|alpha_num|exists:users,id',
        ];
    }

    public function authorize()
    {
        return true;
    }

    public function toEntity()
    {
        return [
            'user_id' => $this->userId,
            'block_user_id' => $this->blockUserId
        ];
    }
}
