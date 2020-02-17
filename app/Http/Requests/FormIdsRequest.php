<?php

namespace App\Http\Requests;

use App\Services\Core\BaseRequest;

class FormIdsRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'formIdOpenId' => 'required'
        ];
    }

    public function authorize()
    {
        return true;
    }
}
