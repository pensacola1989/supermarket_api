<?php

namespace App\Services\Place;

use App\Services\Core\BaseRequest;

class PlaceSubApplyRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'place_id' => 'required|alpha_num|exists:places,external_id', // extId
            // 'user_id' => 'required|alpha_num|exists:users,external_id' // extId
        ];
    }

    public function authorize()
    {
        return true;
    }
}
