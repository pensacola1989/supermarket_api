<?php

namespace App\Http\Requests;

use App\Services\Core\BaseRequest;

class TagRequest extends BaseRequest
{
    public function rules()
    {
        if ($this->method() === 'PUT') {
            return [];
        } else {
            return [
                'tag_name' => 'required',
                // 'order' => 'required',
                // 'created_by_place_id' => 'required'
            ];
        }
    }

    public function authorize()
    {
        return true;
    }
}
