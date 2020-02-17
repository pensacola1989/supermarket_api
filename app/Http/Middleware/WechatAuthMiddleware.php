<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/19/17
 * Time: 9:28 AM
 */

namespace App\Http\Middleware;

use App\Exceptions\UserErrors;
use App\Services\Account\LoginContract;
use App\Services\Account\UserContract;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class WechatAuthMiddleware
{

    protected $loginRespository;

    public function __construct(LoginContract $loginContract)
    {
        $this->loginRespository = $loginContract;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param Closure|Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $refer = $request->headers->get('referer');
        $sKey = $request->headers->get("X-" . config('app.name') . "-Key");
        if ($sKey) {
            $sessionOpenId = Cache::get($sKey);
            if (!$sessionOpenId) {
                Log::info('====sKey not found====');
                return response('Unauthorized.', 401);
            }
            list($openId, $token) = explode(',', $sessionOpenId);

            $login = $this->loginRespository->getLoginByOpenId($openId);
            if ($login && $login->User->is_block === 1) {
                throw UserErrors::youHaveBeenBlockedByApp()->toException();
            }
            $request->headers->set('Authorization', 'Bearer ' . $token);
            $request->headers->set('openId', $openId);
        } else 
        if (app()->environment('local') && !str_contains($refer, 'servicewechat')) {
            $user = app()->make(UserContract::class)->requireById(17);
            $token = JWTAuth::fromUser($user);
            $request->headers->set('Authorization', 'Bearer ' . $token);
            $request->user = $user;
        }

        return $next($request);
    }
}
