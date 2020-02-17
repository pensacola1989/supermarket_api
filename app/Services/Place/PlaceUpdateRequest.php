<?php

namespace App\Services\Place;

use App\Services\Core\BaseRequest;

class PlaceUpdateRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'categoryId' => 'required|alpha_num|exists:place_categories,id',
            'name' => 'required|between:1,50',
            'lat' => 'regex:/^[+-]?\d+\.\d+/',
            'lng' => 'regex:/^[+-]?\d+\.\d+/',
            'isPrivate' => 'required',
            'configs' => 'required',
            'avatar_id' => 'required'
        ];
    }

    public function authorize()
    {
        return true;
    }

    public function toEntity()
    {
        return [
            'desc' => $this->desc,
            'category_id' => $this->categoryId,
            'name' => $this->name,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'is_private' => $this->isPrivate,
            'configs' => $this->configs,
            'avatar_id' => $this->avatar_id
        ];
    }
}
