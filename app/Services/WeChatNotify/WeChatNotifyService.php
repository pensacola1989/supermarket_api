<?php

namespace App\Services\WeChatNotify;

use App\Jobs\WeChatNotifyJob;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use JiaweiXS\WeApp\WeApp;

class WeChatNotifyService implements WeChatNotifyContract
{

    private $_weChatConfig;


    public function __construct()
    {
        $this->_weChatConfig = config('wechat');
    }

    /**
     * cache key rule: {memberid}_{sceneKey}_{actionId}
     *
     * @param Request $request
     * @return void
     */
    public function collectCredentials(Request $request, $sceneKey, $businessId)
    {
        $user = app('request')->user();
        $headerKeys = $this->_weChatConfig['headers'];
        $headerFormIdKey = $headerKeys['formId'];
        $headerOpenIdKey = $headerKeys['openId'];
        if ($request->hasHeader($headerFormIdKey) && $request->hasHeader($headerOpenIdKey)) {

            $expiresAt = Carbon::now()->addMinutes(24 * 6 * 60);
            $formId = $request->headers->get($headerFormIdKey);
            $openId = $request->headers->get($headerOpenIdKey);
            $userId = $user->id;

            \Log::info("[sceneKey|key|value]:");
            \Log::info("scene key ${sceneKey}");
            \Log::info("{$userId}|{$sceneKey}|{$businessId}");
            \Log::info("{$formId}|{$openId}");

            Cache::put("{$userId}|{$sceneKey}|{$businessId}", "{$formId}|{$openId}", $expiresAt);
        }
    }

    /**
     * Undocumented function
     *
     * @param Int $memberId
     * @param String $sceneKey
     * @param Int $businessId
     * @param Mixed $data
     * @param array $pageArgs
     * @return void
     */
    public function deliveryMsg($openId, $sceneKey, $businessId, $data, array $pageArgs = null)
    {
        Log::info('.....wechatnotify....');
        $job = (new WeChatNotifyJob([
            'openId' => $openId,
            'sceneKey' => $sceneKey,
            'businessId' => $businessId,
            'data' => $data,
            'pageArgs' => $pageArgs,
        ]))->onQueue('weChatNotify');
        dispatch($job);
    }

    private function _buildData($data)
    {
        $ret = [];
        foreach ($data as $index => $value) {
            $index++;
            $ret[] = [
                "keyword{$index}" => ['value' => $value],
            ];
        }

        return $ret;
    }
}
