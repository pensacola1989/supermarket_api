<?php

namespace App\Services\Place;

use App\Services\Core\BaseRequest;

class PlaceApplyRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'category_id' => 'required|alpha_num|exists:place_categories,id',
            'name' => 'required|between:1,50|unique:place_applies,name',
            'lat' => 'regex:/^[+-]?\d+\.\d+/',
            'lng' => 'regex:/^[+-]?\d+\.\d+/',
        ];
    }

    public function authorize()
    {
        return true;
    }
}
