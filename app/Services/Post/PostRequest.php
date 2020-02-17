<?php

namespace App\Services\Post;

use App\Services\Core\BaseRequest;
use App\Services\Place\PlaceContract;
use App\Exceptions\UserErrors;

class PostRequest extends BaseRequest
{
    private $_placeRepository;

    public function __construct(PlaceContract $placeContract)
    {
        $this->_placeRepository = $placeContract;
    }

    public function rules()
    {
        return [
            'content' => 'required_without_all:photo_ids',
            'photo_ids' => 'array|required_without_all:content',
            'photo_ids.*' => 'integer',
            'place_id' => 'required|alpha_num|exists:places,id',
            'tag_ids' => 'array|between:1,6',
            'tag_ids.*' => 'integer|min:1',
        ];
    }

    public function authorize()
    {
        $placeId = $this->request->get('place_id');
        $user = app('request')->user();

        if ($this->_placeRepository->userIsBlock($user->id, $placeId)) {
            throw UserErrors::userIsBlockedForThisPlace()->toException();
        }
        return true;
    }
}
