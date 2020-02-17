<?php

namespace App\Services\Comment;

use App\Services\Core\BaseRequest;
use App\Services\Place\PlaceContract;
use App\Services\Post\PostContract;
use App\Exceptions\UserErrors;
use App\Services\Account\Block\UserBlockContract;
use Illuminate\Validation\Rule;
use App\Services\Account\UserContract;

class CommentRequest extends BaseRequest
{
    private $_postRepository;

    private $_placeRepository;

    private $_userRepository;

    private $_posterBlockUserRepository;

    public function __construct(PostContract $postContract, PlaceContract $placeContract, UserContract $userContract, UserBlockContract $userBlockContract)
    {
        $this->_postRepository = $postContract;
        $this->_placeRepository = $placeContract;
        $this->_userRepository = $userContract;
        $this->_posterBlockUserRepository = $userBlockContract;
    }

    public function rules()
    {
        return [
            'content' => 'required',
            'post_id' => 'required|alpha_num|exists:posts,id',
            'photo_id' => 'alpha_num|exists:attachments,id',
            // 'to_uid' => 'required|alpha_num|exists:users,id',

            'to_uid' => ['required', 'alpha_num', function ($attributes, $value, $fail) {
                if ($value == 0) {
                    return true;
                }
                $userExist = $this->_userRepository->getById($this->to_uid);
                if (!$userExist) {
                    $fail('user not exist');
                }
            }]
        ];
    }

    public function authorize()
    {
        $postId = $this->request->get('post_id');
        // to_uid 可以是帖子的主人，也可以是你回复某个人的uid，都是to_uid, 拉黑看人，所有评论回复操作，检查的逻辑一致
        $toUserId = $this->request->get('to_uid');
        $placeId = $this->_postRepository->requireById($postId)->Place->id;

        $user = app('request')->user();
        if ($this->_placeRepository->userIsBlock($user->id, $placeId)) {
            throw UserErrors::userIsBlockedForThisPlace()->toException();
        }

        if ($this->_posterBlockUserRepository->userIsBlockByPoster($toUserId, $user->id)) {
            throw UserErrors::youAreBlockedByThisUser()->toException();
        }

        return true;
    }
}
