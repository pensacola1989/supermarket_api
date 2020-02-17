<?php

namespace App\Services\Place;

use App\Services\Core\BaseRequest;
use App\Exceptions\UserErrors;
use App\Services\Account\Block\UserBlockContract;
use Illuminate\Support\Facades\Route;
use App\Services\Post\PostContract;

class LikeRequest extends BaseRequest
{
    private $_placeRespository;

    private $_postRepository;

    private $_posterBlockUserRepository;

    public function __construct(PlaceContract $placeContract, PostContract $postContract, UserBlockContract $userBlockContract)
    {
        $this->_placeRespository = $placeContract;
        $this->_postRepository = $postContract;
        $this->_posterBlockUserRepository = $userBlockContract;
    }

    public function rules()
    {
        return [];
    }

    public function authorize()
    {
        $postId = app('request')->route()[2]['postId'];
        $post = $this->_postRepository->requireByExternalId($postId);
        $placeId = $post->Place->id;
        $user = app('request')->user();
        if ($this->_placeRespository->userIsBlock($user->id, $placeId)) {
            throw UserErrors::userIsBlockedForThisPlace()->toException();
        }

        if ($this->_posterBlockUserRepository->userIsBlockByPoster($post->User->id, $user->id)) {
            throw UserErrors::youAreBlockedByThisUser()->toException();
        }

        return true;
    }
}
