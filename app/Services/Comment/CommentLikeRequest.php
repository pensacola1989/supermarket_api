<?php

namespace App\Services\Comment;

use App\Services\Core\BaseRequest;
use App\Services\Place\PlaceContract;
use App\Exceptions\UserErrors;
use App\Services\Account\Block\UserBlockContract;

class CommentLikeRequest extends BaseRequest
{
    private $_commentRepository;

    private $_placeRepository;

    private $_posterBlockUserRepository;

    public function __construct(CommentContract $commentContract, PlaceContract $placeContract, UserBlockContract $userBlockContract)
    {
        $this->_commentRepository = $commentContract;
        $this->_placeRepository = $placeContract;
        $this->_posterBlockUserRepository = $userBlockContract;
    }

    public function rules()
    {
        return [
            // 'comment_id' => 'required|exists:comments,id',
            // 'is_like' => 'required|alpha_num|min:0,max:1',
            // 'user_id' => 'required|alpha_num|exists:users,id',
        ];
    }

    public function authorize()
    {
        $commentId = app('request')->route()[2]['commentId'];
        $comment = $this->_commentRepository->requireById($commentId);
        $placeId = $comment->Post->Place->id;

        $user = app('request')->user();
        if ($this->_placeRepository->userIsBlock($user->id, $placeId)) {
            throw UserErrors::userIsBlockedForThisPlace()->toException();
        }

        if ($this->_posterBlockUserRepository->userIsBlockByPoster($comment->ToUser->id, $user->id)) {
            throw UserErrors::youAreBlockedByThisUser()->toException();
        }

        return true;
    }
}
