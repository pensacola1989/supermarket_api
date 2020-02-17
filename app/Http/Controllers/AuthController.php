<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/17/17
 * Time: 5:51 PM
 */

namespace App\Http\Controllers;

use App\Exceptions\SystemErrors;
use App\Http\Transformers\UserTranformer;
use App\Services\Account\LoginContract;
use App\Services\Account\UserContract;
use App\Services\Exception\EntityNotFoundException;
use App\Services\WechatAuth\WechatAuth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Tymon\JWTAuth\JWTAuth;

class AuthController extends Controller
{
    protected $jwt;
    protected $userRepository;
    protected $loginRepository;
    protected $userTransfomer;

    public function __construct(JWTAuth $jwt, UserContract $userContract, LoginContract $loginRepo, UserTranformer $userTransformer)
    {
        $this->jwt = $jwt;
        $this->userRepository = $userContract;
        $this->loginRepository = $loginRepo;
        $this->userTransfomer = $userTransformer;
    }

    public function getToken(Request $request)
    {
        $user = $this->userRepository->getUserByMobile($request->mobile);
        if (!$user) {
            throw new EntityNotFoundException;
        }
        $token = $this->jwt->fromUser($user);
        return response()->json(compact('token'));
    }

    public function wechatCodeForSession(Request $request, $authCode)
    {
        $status = 0;
        $weChat = app()->make(WechatAuth::class);
        $authInfo = $weChat->getLoginInfo($authCode);
        $openId = $authInfo['openid'] ?? null;
        try {
            $login = $this->loginRepository->getLoginByOpenId($openId);
            if (!$login) {
                throw SystemErrors::UserNotReigstered()->toException();
            }
            $status = 1;
            $token = $this->jwt->fromUser($login->User);
            $sKey = generateAuthkey($openId, $token);
            $user = $this->transformData($login->User, $this->userTransfomer);
            $user['openId'] = $openId;

            return compact('sKey', 'user', 'status');
        } catch (\Exception $exception) {
            return ['status' => $status];
            // return $this->respond(['status' => $status]);
        }
    }

    public function getWechatSession(Request $request, $code)
    {
        $wechat = App::make(WechatAuth::class);
        $loginInfo = $wechat->getLoginInfo($code);
        $userInfo = $request->userInfo;
        $userInfo['openId'] = $loginInfo['openid'];
        $user = $this->syncWechatUser((object) $userInfo);
        $user->load('Avatar');
        $token = $this->jwt->fromUser($user);
        $sKey = (string) random_int(0, PHP_INT_MAX);
        $expiresAt = Carbon::now()->addMinutes(3600);
        $user = fractal($user, $this->userTransfomer);
        Cache::put($sKey, $loginInfo['openid'] . ',' . $token, $expiresAt);
        // Cache::put($sKey, $loginInfo['session_key'] . ',' . $loginInfo['openid'] . ',' . $token, $expiresAt);
        $user->openId = $userInfo['openId'];

        return compact('sKey', 'userInfo', 'user');
    }

    private function syncWechatUser($wechatUserInfo)
    {
        //        $wechatUserInfo = json_decode($wechatUserInfo);
        $login = $this->loginRepository->getLoginByOpenId($wechatUserInfo->openId);

        if ($login) {
            // if user has name etc. then don't update...later to do
            $user = $login->User;
            $user->name = $wechatUserInfo->nickName;
            $user->nick_name = $wechatUserInfo->nickName;
            $user->gender = $wechatUserInfo->gender;
            $user->save();
            $login->avatar_url = $wechatUserInfo->avatarUrl;
            $login->User()->associate($user);
            $login->save();

            return $user;
        } else {
            $user = $this->userRepository->createModel([
                'name' => $wechatUserInfo->nickName,
                'nick_name' => $wechatUserInfo->nickName,
                'gender' => $wechatUserInfo->gender,
            ]);
            $user->Logins()->create([
                'external_id' => $wechatUserInfo->openId,
                'external_system' => 1, // 1. 微信
                'user_id' => $user->id,
                'avatar_url' => $wechatUserInfo->avatarUrl,
            ]);

            return $user;
        }
    }
}
