<?php

namespace App\Http\Middleware;

use App\Exceptions\UserErrors;
use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\JWTAuth;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory $auth
     * @return void
     */
    public function __construct(JWTAuth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        try {
            $refer = $request->headers->get('referer');
            $sKey = $request->headers->get("X-" . config('app.name') . "-Key");
            if ($sKey) {
                $sessionOpenId = Cache::get($sKey);
                if (!$sessionOpenId) {
                    Log::info('====sKey not found====');
                    throw UserErrors::UnauthorizedHttpException('Unauthorized: access key not found', 401)->toException();
                }
                list($openId, $token) = explode(',', $sessionOpenId);
                $request->headers->set('Authorization', 'Bearer ' . $token);
                $request->headers->set('openId', $openId);

                if ($this->auth->parser()->setRequest($request)->hasToken()) {
                    $user = $this->auth->parseToken()->authenticate();
                    $request->user = $user;
                }
            }
        } catch (\Exception $e) {
            if ($e instanceof TokenExpiredException) {
                throw new UnauthorizedHttpException('', 'Unauthorized: use token has expired', $e, 401);
            }
            //doing nothing
        }
        return $next($request);
    }
}
