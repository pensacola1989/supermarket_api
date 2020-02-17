<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/14/17
 * Time: 6:35 PM
 */

namespace App\Http\Controllers;

use App\Exceptions\UserErrors;
use App\Http\Transformers\UserBlockTransformer;
use App\Services\Account\UserContract;
use App\Services\Post\LikeContract;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Transformers\UserTranformer;
use App\Services\Account\Block\UserBlockContract;
use App\Services\Account\Block\UserBlockRequest;

class UserBlockController extends Controller
{

    private $userBlockRepository;

    private $userRepository;

    protected $userBlockTransformer;

    public function __construct(UserBlockContract $userBlockContract, UserContract $userContract, UserBlockTransformer $userBlockTransformer)
    {
        $this->userBlockRepository = $userBlockContract;
        $this->userRepository = $userContract;
        $this->userBlockTransformer = $userBlockTransformer;
    }

    public function search(Request $request)
    {
        $request->merge([
            'userId' => $this->getCurrentUserId()
        ]);
        $blockedUsers = $this->userBlockRepository->search($request->input());
        return $this->respondPaginate($blockedUsers, $this->userBlockTransformer);
    }

    public function create($blockUserId)
    {
        $blockFromUserId = $this->getCurrentUserId();
        $blockUser = $this->userRepository->requireByExternalId($blockUserId);
        if ($blockFromUserId === $blockUser->id) {
            throw UserErrors::youCannotBlockYouSelf()->toException();
        }
        $isBlockedBefore = $this->userBlockRepository->getModel()->where([
            ['user_id', '=', $blockFromUserId],
            ['block_user_id', '=', $blockUser->id]
        ])->first();
        if ($isBlockedBefore) {
            throw UserErrors::CannotRepeatBlockAction("you have blocked this user before!")->toException();
        }
        $this->userBlockRepository->createModel([
            'user_id' => $blockFromUserId,
            'block_user_id' => $blockUser->id
        ]);

        return $this->Created();
    }

    public function update(Request $request, $id)
    { }

    public function all()
    { }

    public function destroy($id)
    { }

    public function removeMyBlockById($blockUserId)
    {
        $myUserId = $this->getCurrentUserId();
        $block = $this->userBlockRepository->getOneBlockUser($myUserId, $blockUserId);

        $this->userBlockRepository->delete($block);

        return $this->OK();
    }

    public function show($id)
    { }
}
