<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/19/17
 * Time: 9:28 AM
 */

namespace App\Http\Middleware;

use App\Services\Account\UserContract;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Exceptions\UserErrors;
use App\Services\Place\PlaceRepository;

class AdminMiddleware
{
    protected $placeRepository;

    public function __construct(PlaceRepository $placeRepository)
    {
        $this->placeRepository = $placeRepository;
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
        $placeExtId = $request->segment(5);
        $place = $this->placeRepository->requireByExternalId($placeExtId);
        $request->merge(['place' => $place]);
        if ($request->user()->cannot('manage-place', $place)) {
            throw UserErrors::NoPermission()->toException();
        }

        return $next($request);
    }
}
