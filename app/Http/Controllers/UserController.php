<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/14/17
 * Time: 6:35 PM
 */

namespace App\Http\Controllers;

use App\Http\Requests\FormIdsRequest;
use App\Http\Transformers\SystemNotifyTransformer;
use App\Services\Account\UserContract;
use App\Services\Post\LikeContract;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Transformers\UserTranformer;
use App\Services\SystemNotify\SystemNotifyContract;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{
    private $validateRule = [
        'name' => 'required_without_all:nick_name|between:1,20',
        'nick_name' => 'required_without_all:name|between:1,20',
        'mobile' => 'nullable|alpha_num'
    ];

    private $userRepository;

    private $likeRepository;

    private $systemNotifyRepository;

    protected $userTransformer;

    public function __construct(UserContract $userContact, LikeContract $likeContract, UserTranformer $userTranformer, SystemNotifyContract $systemNotifyContract)
    {
        $this->likeRepository = $likeContract;
        $this->userRepository = $userContact;
        $this->userTransformer = $userTranformer;
        $this->systemNotifyRepository = $systemNotifyContract;
    }

    public function search(Request $request)
    {
        return $this->userRepository->search($request);
    }

    public function create(Request $request)
    {
        $this->customerValidate($request, $this->validateRule);
        $user = $this->userRepository->createUser($request->input());

        return $user;
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, $this->validateRule);
        $user = $this->userRepository->updateUserInfo($id, $request->input());

        return $this->respond(fractal($user, $this->userTransformer));
    }

    public function all()
    {
        return $this->userRepository->getAll();
    }

    public function destroy($id)
    {
        $model = $this->userRepository->requireById($id);
        $this->userRepository->delete($model);

        return $this->OK();
    }

    public function show($id)
    {
        $model = $this->userRepository->requireByExternalId($id);

        return $this->respond(fractal($model, $this->userTransformer));
        // return $this->respond($this->usert) $model;
    }

    public function storeFormIds(FormIdsRequest $request)
    {
        $formIdOpenId = $request->formIdOpenId;
        return $this->_storeFormId($formIdOpenId);
        // $uesrId = $this->getCurrentUserId();
        // $userRangeKey = "{$uesrId}FormIdRangekeys";
        // $todayKey = $uesrId . date('Y-m-d');
        // Cache::put($todayKey, $formIdOpenId);
    }

    /**
     * exclude self
     * @param Request $request
     * @param $userId
     * @return mixed
     */
    public function likes(Request $request, $userId)
    {
        $ret = $this->likeRepository->search(['like_user_id' => $userId]);

        return $ret;
    }

    /**
     * exclude self
     * @param Request $request
     * @param $userId
     * @return mixed
     */
    public function beLikes(Request $request, $userId)
    {
        $ret = $this->likeRepository->search(['be_liked_user_id' => $userId]);

        return $ret;
    }

    public function summary($id)
    {
        $ret = $this->userRepository->getUserSummary($id);
        return $ret;
        //        $model = $this->userRepository->requireByExternalId($id);
        //        $model=
    }

    public function notifyRegister($id)
    {
        return $this->userRepository->getNotifyCount($id);
    }

    public function readNotify($id)
    {
        $this->userRepository->clearNotify($id);

        return $this->OK();
    }

    public function getSystemNotifications(Request $request)
    {
        // $user = $this->getCurrentUser();
        // $sysNotifyReadtime = $user->sys_notify_read_time;
        // $criteria['userLastReadTime'] = $sysNotifyReadtime;
        $sysNotifications = $this->systemNotifyRepository->search($request->input());

        return $this->respondPaginate($sysNotifications, new SystemNotifyTransformer);
    }

    public function getSystemNotificationsNums(Request $request)
    {
        $user = $this->getCurrentUser();
        $sysNotifyReadtime = $user->sys_notify_read_time;
        $sysNotificationsNum = $this->systemNotifyRepository->getUserUnreadSystemNotifyNum($sysNotifyReadtime);

        $userNotificationsNum = $this->userRepository->getNotifyCount($this->getCurrentUserId());

        return ['sys' => $sysNotificationsNum, 'user' => $userNotificationsNum];
    }

    public function readSystemNotify(Request $request)
    {
        $user = $this->getCurrentUser();
        $user->touchSystemReadTime();

        return $this->OK();
    }

    private function _storeFormId($formIdOpenId)
    {
        $userId = $this->getCurrentUserId();
        $userFormIdsCacheKeyPrefix = "{$userId}_formIds";
        $userRangeKey = "{$userId}FormIdRangekeys";
        $todayKey = $userFormIdsCacheKeyPrefix . '_' . date('Y_m_d');
        $todayFormIds = Cache::has($todayKey) ? (Cache::get($todayKey)) : [];
        array_push($todayFormIds, $formIdOpenId);
        // cache back
        Cache::put($todayKey, $todayFormIds);

        $userFormIdKeysInDays = Cache::has($userRangeKey) ? (Cache::get($userRangeKey)) : [];
        if (!in_array($todayKey, $userFormIdKeysInDays, true)) {
            array_push($userFormIdKeysInDays, $todayKey);
        }
        Cache::put($userRangeKey, $userFormIdKeysInDays);

        return [
            'userRangeKey' => Cache::get($userRangeKey),
            'userFormIds' => Cache::get($todayKey)
        ];
    }
}
