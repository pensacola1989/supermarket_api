<?php

if (!function_exists('generateAuthkey')) {
    function generateAuthkey($openId, $token = null)
    {
        try {
            $sKey = '';
            $jwt = app()->make(Tymon\JWTAuth\JWTAuth::class);
            $expiresAt = Carbon\Carbon::now('Asia/Shanghai')->addMinutes(24 * 7 * 60);
            $sKey = (string) random_int(0, PHP_INT_MAX);
            if ($token) {
                Cache::put($sKey, "{$openId},{$token}", $expiresAt);
            } else {
                $expireToken = $jwt->getToken();
                $token = $jwt->refresh($expireToken);

                Cache::put($sKey, "{$openId},{$token}", $expiresAt);
            }

            return $sKey;
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            throw App\Exceptions\SystemErrors::TokenUnableRrefreshed($e->getMessage())->toException($e);
        }
    }
}

if (!function_exists('isTextOverflow')) {
    function isTextOverflow($text)
    {
        return mb_strwidth($text, 'utf8') > 240;
    }
}

if (!function_exists('getUserAvatar')) {
    function getUserAvatar(App\Services\Account\User $user)
    {
        $fullUrlPrefix = config('app.oss.fullUrlPrefix');
        // if ($this->isAnonymous) {
        //     return config('app.default_image.url') . '/' . $user->external_id . '?' . http_build_query(config('app.default_image.params'));
        // }
        if ($user->avatar_url) {
            // http://ehe1989.oss-cn-hangzhou.aliyuncs.com/data/images/14/156613924475784611.jpg
            // return $user->avatar_url;
            return "http://{$fullUrlPrefix}/data/images/{$user->id}/{$user->avatar_url}";
        }
        if ($user->Avatar) {
            return "http://{$fullUrlPrefix}/data/images/{$user->id}/{$user->Avatar->attach_file_name}";
            // return $user->Avatar->attach_file_name;
        }
        if ($user->Logins()->count()) {
            return $user->Logins()->first()->avatar_url;
        }
    }
}

if (!function_exists('timeDiffForHuman')) {
    function timeDiffForHuman(\Illuminate\Support\Carbon $dateTime = null)
    {
        if (!$dateTime) {
            return null;
        }
        if (Carbon\Carbon::now() > Carbon\Carbon::parse($dateTime)->addDays(3)) {
            return $dateTime->format('Y-m-d H:i');
        }
        return Carbon\Carbon::parse($dateTime)->diffForHumans();
    }
}
if (!function_exists('formatDistance')) {
    function formatDistance($distance)
    {
        if (!is_numeric($distance)) {
            return;
        }
        $distance = ceil($distance);
        if ($distance / 1000 >= 1) {
            return ceil($distance / 1000) . '公里内';
        }
        return ceil($distance / 100) * 100 . '米内';
    }
}
if (!function_exists('isValidMpLink')) {
    function isValidMpLink($link)
    {
        $p = '/^https:\/\/mp.weixin.qq.com\/s\/[a-z0-9\-]/i';

        return (bool) preg_match($p, $link);
    }
}

if (!function_exists('getPlaceDefaultConfigOptions')) {
    function getPlaceDefaultConfigOptions()
    {
        return \DB::table('place_configs')->where('visible', 1)->get();
    }
}

// if (!function_exists('getPlaceConfigValues')) {
//     /**
//      * 1 -> canReply
//      * 2 -> canPost
//      * 3 -> canAnonymous
//      *
//      * @param [type] $options
//      * @param [type] $defaultConfigs
//      * @return void
//      */
//     function getPlaceConfigValues($defaultConfigs)
//     {
//         $searchById = function ($id) use ($defaultConfigs) {
//             $configIndex = $defaultConfigs->search(function ($config) use ($id) {
//                 return $config['configId'] === $id;
//             });
//             if ($configIndex === false) {
//                 return false;
//             }
//             return boolval($defaultConfigs[$configIndex]['configValue']);
//         };

//         return [
//             'canReply' => $searchById(1),
//             'canPost' => $searchById(2),
//             'canAnonymous' => $searchById(3)
//         ];
//     }
// }
if (!function_exists('getPlaceConfigDirectlyValues')) {
    function getPlaceConfigDirectlyValues($configValues, $id)
    {
        $configIndex = collect($configValues)->search(function ($config) use ($id) {
            return $config->configId === $id;
        });
        if ($configIndex === false) {
            return false;
        }
        return boolval($configValues[$configIndex]->configValue);
    }
}
